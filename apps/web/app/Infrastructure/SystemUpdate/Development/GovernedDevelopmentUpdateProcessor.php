<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Development;

use JsonException;
use PharData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

// Author by Lab | zefry
final class GovernedDevelopmentUpdateProcessor
{
    private const MAX_OUTER_BYTES = 268435456;
    private const MAX_INNER_BYTES = 134217728;
    private const MAX_JSON_BYTES = 1048576;

    public function __construct(private readonly GovernedDevelopmentUpdateRequest $requests)
    {
    }

    /**
     * Discover a trusted exact staging publication without mutating the active runtime.
     *
     * @param null|callable(string,string):array<string,mixed> $jsonFetcher
     * @return array<string,mixed>
     */
    public function discover(?callable $jsonFetcher = null): array
    {
        $this->assertProcessorConfiguration();
        $jsonFetcher ??= fn (string $url, string $token): array => $this->githubJson($url, $token);

        $githubToken = (string) config('oneqay.development_updater.github_token', '');
        if (strlen($githubToken) < 20 || strlen($githubToken) > 4096) {
            throw new DevelopmentUpdaterViolation('github_token_invalid');
        }

        $currentSource = strtolower(trim((string) config('oneqay.development_updater.running_source_commit', '')));
        if (preg_match('/\A[0-9a-f]{40}\z/', $currentSource) !== 1) {
            throw new DevelopmentUpdaterViolation('running_release_identity_invalid');
        }

        $run = $this->latestTrustedPublicationRun($jsonFetcher, $githubToken);
        $candidateSource = $this->pattern(
            $run['head_sha'] ?? null,
            '/\A[0-9a-f]{40}\z/',
            'publication_source_invalid',
        );

        if (! hash_equals($currentSource, $candidateSource)) {
            $this->assertForwardOnly($jsonFetcher, $githubToken, $currentSource, $candidateSource);
        }

        $runId = $run['id'] ?? null;
        if (! is_int($runId) || $runId <= 0) {
            throw new DevelopmentUpdaterViolation('publication_run_id_invalid');
        }

        $artifact = $this->trustedArtifactForRun($jsonFetcher, $githubToken, $runId, $candidateSource);
        $artifactId = $artifact['id'] ?? null;
        if (! is_int($artifactId) || $artifactId <= 0) {
            throw new DevelopmentUpdaterViolation('actions_artifact_id_invalid');
        }
        $outerDigest = $this->pattern(
            str_replace('sha256:', '', (string) ($artifact['digest'] ?? '')),
            '/\A[0-9a-f]{64}\z/',
            'outer_digest_invalid',
        );

        $candidate = $this->requests->storeCandidate([
            'release_id' => 'durable-staging-'.substr($candidateSource, 0, 12),
            'source_commit' => $candidateSource,
            'current_source_commit' => $currentSource,
            'github_run_id' => $runId,
            'github_artifact_id' => $artifactId,
            'github_outer_sha256' => $outerDigest,
        ], time());

        return [
            'state' => $candidate['candidate_state'],
            'release_id' => $candidate['release_id'],
            'source_commit' => $candidate['source_commit'],
            'candidate_fingerprint' => $candidate['candidate_fingerprint'],
            'expires_at_unix' => $candidate['expires_at_unix'],
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
    }

    /**
     * @param null|callable(string,string):array<string,mixed> $jsonFetcher
     * @param null|callable(string,string,string):void $downloader
     * @param null|callable(string,string):array<string,mixed> $attestationFetcher
     * @return array<string,mixed>
     */
    public function process(
        ?callable $jsonFetcher = null,
        ?callable $downloader = null,
        ?callable $attestationFetcher = null,
    ): array {
        $now = time();
        $request = $this->requests->requireCurrentPending($now);
        $this->assertProcessorConfiguration();

        $privateRoot = $this->requests->privateRoot();
        $lockPath = $privateRoot.'/update.lock';
        $lock = fopen($lockPath, 'c+');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new DevelopmentUpdaterViolation('update_lock_held');
        }
        @chmod($lockPath, 0600);

        $work = $privateRoot.'/work/'.$request['request_id'];
        $this->removeTree($work);
        if (! mkdir($work, 0700, true) && ! is_dir($work)) {
            flock($lock, LOCK_UN);
            fclose($lock);
            throw new DevelopmentUpdaterViolation('work_directory_unavailable');
        }

        $previousRelease = null;
        $previousEnv = null;
        $pointerMutated = false;
        $envMutated = false;
        $buildBackup = null;
        $buildMutated = false;

        try {
            $jsonFetcher ??= fn (string $url, string $token): array => $this->githubJson($url, $token);
            $downloader ??= function (string $url, string $token, string $destination): void {
                $this->githubDownload($url, $token, $destination);
            };
            $attestationFetcher ??= fn (string $url, string $token): array => $this->httpsJson($url, $token);

            $githubToken = (string) config('oneqay.development_updater.github_token', '');
            if (strlen($githubToken) < 20 || strlen($githubToken) > 4096) {
                throw new DevelopmentUpdaterViolation('github_token_invalid');
            }

            $currentSource = (string) $request['current_source_commit'];
            $currentArtifact = (string) $request['current_artifact_sha256'];

            $candidateSource = (string) $request['candidate_source_commit'];
            $runId = (int) $request['github_run_id'];
            $artifactId = (int) $request['github_artifact_id'];
            $outerDigest = (string) $request['github_outer_sha256'];
            $releaseId = (string) $request['candidate_release_id'];

            $run = $this->trustedPublicationRunById($jsonFetcher, $githubToken, $runId, $candidateSource);
            $this->assertForwardOnly($jsonFetcher, $githubToken, $currentSource, $candidateSource);

            $artifactMeta = $this->trustedArtifactForRun(
                $jsonFetcher,
                $githubToken,
                $runId,
                $candidateSource,
            );
            $observedArtifactId = $artifactMeta['id'] ?? null;
            $observedOuterDigest = $this->pattern(
                str_replace('sha256:', '', (string) ($artifactMeta['digest'] ?? '')),
                '/\A[0-9a-f]{64}\z/',
                'outer_digest_invalid',
            );
            if (! is_int($observedArtifactId)
                || $observedArtifactId !== $artifactId
                || ! hash_equals($outerDigest, $observedOuterDigest)) {
                throw new DevelopmentUpdaterViolation('authorized_candidate_drift');
            }

            $outerZip = $work.'/github-actions-artifact.zip';
            $downloadUrl = 'https://api.github.com/repos/labzefry/oneQay/actions/artifacts/'.$artifactId.'/zip';
            $downloader($downloadUrl, $githubToken, $outerZip);
            $this->assertRegularFile($outerZip, 1024, self::MAX_OUTER_BYTES, 'outer_artifact_invalid');
            $actualOuter = hash_file('sha256', $outerZip);
            if (! is_string($actualOuter) || ! hash_equals($outerDigest, $actualOuter)) {
                throw new DevelopmentUpdaterViolation('outer_artifact_digest_mismatch');
            }

            $bundle = $work.'/bundle';
            if (! mkdir($bundle, 0700) && ! is_dir($bundle)) {
                throw new DevelopmentUpdaterViolation('bundle_directory_unavailable');
            }
            $this->extractArchive($outerZip, $bundle, 'outer_artifact_extraction_failed');

            if ($releaseId !== 'durable-staging-'.substr($candidateSource, 0, 12)) {
                throw new DevelopmentUpdaterViolation('authorized_release_id_invalid');
            }
            $archiveName = $releaseId.'.tar.gz';
            $manifestName = $releaseId.'.manifest.json';
            $checksumName = $archiveName.'.sha256';
            $handoffName = $releaseId.'.deployment-handoff.json';

            foreach ([$archiveName, $manifestName, $checksumName, $handoffName] as $expected) {
                $this->assertRegularFile($bundle.'/'.$expected, 2, self::MAX_OUTER_BYTES, 'bundle_member_missing');
            }

            $archivePath = $bundle.'/'.$archiveName;
            $manifestPath = $bundle.'/'.$manifestName;
            $handoffPath = $bundle.'/'.$handoffName;
            $manifest = $this->readJson($manifestPath);
            $handoff = $this->readJson($handoffPath);

            $candidateArtifact = $this->validateBundle(
                $manifest,
                $handoff,
                $archivePath,
                $bundle.'/'.$checksumName,
                $candidateSource,
                $releaseId,
            );

            $releaseRoot = $this->safeConfiguredPath('release_root');
            $activePointer = $this->safeConfiguredPath('active_release_pointer');
            $runtimeEnv = $this->safeConfiguredPath('runtime_env_path');
            $documentRoot = $this->safeConfiguredPath('document_root');
            $mode = strtoupper(trim((string) config('oneqay.development_updater.document_root_mode', '')));
            if (! in_array($mode, ['ACTIVE_RELEASE_PUBLIC', 'FIXED_PUBLIC_BRIDGE'], true)) {
                throw new DevelopmentUpdaterViolation('document_root_mode_invalid');
            }

            $previousRelease = $this->currentActiveRelease($activePointer, $releaseRoot);
            if ($previousRelease === null) {
                throw new DevelopmentUpdaterViolation('previous_active_release_required');
            }

            $candidateDirectory = $releaseRoot.'/'.$releaseId;
            if (file_exists($candidateDirectory) || is_link($candidateDirectory)) {
                throw new DevelopmentUpdaterViolation('candidate_release_already_present');
            }

            // Exact short-lived operator authority must still be current before any deployment mutation.
            $this->requests->requireCurrentPending(time());

            $this->extractCandidate($archivePath, $releaseRoot, $releaseId, $candidateSource, $candidateArtifact);
            $this->bindRuntimeEnvironment($candidateDirectory, $runtimeEnv);

            $previousEnv = $this->readPrivateRuntimeEnv($runtimeEnv);
            $candidateEnv = $this->rewriteRuntimeIdentity($previousEnv, $candidateSource, $candidateArtifact);

            // Reverify again immediately before public/runtime/pointer mutation.
            $this->requests->requireCurrentPending(time());

            if ($mode === 'ACTIVE_RELEASE_PUBLIC') {
                if ($documentRoot !== $activePointer.'/apps/web/public') {
                    throw new DevelopmentUpdaterViolation('active_release_document_root_invalid');
                }
            } else {
                $buildBackup = $this->prepareFixedPublicBuild(
                    $documentRoot,
                    $candidateDirectory.'/apps/web/public/build',
                    $request['request_id'],
                );
                $buildMutated = true;
            }

            $this->atomicWriteBytes($runtimeEnv, $candidateEnv, 0600);
            $envMutated = true;
            $this->atomicPoint($activePointer, $candidateDirectory);
            $pointerMutated = true;

            $attestationUrl = $this->configuredHttpsUrl('attestation_url');
            $attestationToken = (string) config('oneqay.development_updater.attestation_token', '');
            if (strlen($attestationToken) < 16 || strlen($attestationToken) > 4096) {
                throw new DevelopmentUpdaterViolation('attestation_token_invalid');
            }

            $first = $attestationFetcher($attestationUrl, $attestationToken);
            $this->assertAttestation(
                $first,
                $candidateSource,
                $candidateArtifact,
                (string) config('oneqay.development_updater.environment_id', ''),
            );

            $this->atomicWriteBytes($runtimeEnv, $previousEnv, 0600);
            $this->atomicPoint($activePointer, $previousRelease);
            $previousIdentity = $this->releaseIdentity($previousRelease);
            $previousAttestation = $attestationFetcher($attestationUrl, $attestationToken);
            $this->assertAttestation(
                $previousAttestation,
                $previousIdentity['source_commit'],
                $previousIdentity['artifact_sha256'],
                (string) config('oneqay.development_updater.environment_id', ''),
            );

            // Rollback rehearsal does not extend authority; exact authority must still be current.
            $this->requests->requireCurrentPending(time());
            $this->atomicWriteBytes($runtimeEnv, $candidateEnv, 0600);
            $this->atomicPoint($activePointer, $candidateDirectory);
            $second = $attestationFetcher($attestationUrl, $attestationToken);
            $this->assertAttestation(
                $second,
                $candidateSource,
                $candidateArtifact,
                (string) config('oneqay.development_updater.environment_id', ''),
            );

            $evidence = $this->deploymentEvidence(
                $request,
                $releaseId,
                $candidateSource,
                $candidateArtifact,
                $second,
            );
            $evidencePath = $privateRoot.'/evidence/'.$releaseId.'.json';
            $this->atomicWriteJson($evidencePath, $evidence);

            $result = $this->safeResult(
                'SUCCEEDED',
                $releaseId,
                $candidateSource,
                $candidateArtifact,
                time(),
                'governed_update_healthy',
                basename($evidencePath),
            );
            $this->requests->complete($result);

            if (is_string($buildBackup) && is_dir($buildBackup)) {
                $this->removeTree($buildBackup);
            }

            return $result;
        } catch (Throwable $failure) {
            try {
                if ($pointerMutated && is_string($previousRelease) && is_dir($previousRelease)) {
                    $activePointer = $this->safeConfiguredPath('active_release_pointer');
                    $this->atomicPoint($activePointer, $previousRelease);
                }
                if ($envMutated && is_string($previousEnv)) {
                    $runtimeEnv = $this->safeConfiguredPath('runtime_env_path');
                    $this->atomicWriteBytes($runtimeEnv, $previousEnv, 0600);
                }
                if ($buildMutated && is_string($buildBackup) && is_dir($buildBackup)) {
                    $this->restoreFixedPublicBuild(
                        $this->safeConfiguredPath('document_root'),
                        $buildBackup,
                    );
                }
            } catch (Throwable) {
            }

            $safeCode = $failure instanceof DevelopmentUpdaterViolation
                ? $failure->safeCode()
                : 'development_update_failed';

            $result = $this->safeResult(
                'FAILED',
                null,
                null,
                null,
                time(),
                $safeCode,
            );

            try {
                $this->requests->complete($result);
            } catch (Throwable) {
            }

            if ($failure instanceof DevelopmentUpdaterViolation) {
                throw $failure;
            }

            throw new DevelopmentUpdaterViolation('development_update_failed');
        } finally {
            $this->removeTree($work);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function assertProcessorConfiguration(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $appEnv = strtolower(trim((string) config('app.env', '')));
        $productionDataAllowed = filter_var(env('ONEQAY_PRODUCTION_DATA_ALLOWED', false), FILTER_VALIDATE_BOOL);
        if (! (bool) config('oneqay.development_updater.enabled', false)
            || $runtime !== 'durable-staging'
            || $appEnv === 'production'
            || $productionDataAllowed !== false) {
            throw new DevelopmentUpdaterViolation('development_updater_disabled');
        }

        $environment = (string) config('oneqay.development_updater.environment_id', '');
        if (preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/', $environment) !== 1) {
            throw new DevelopmentUpdaterViolation('environment_id_invalid');
        }

        foreach (['release_root', 'active_release_pointer', 'runtime_env_path', 'document_root'] as $key) {
            $this->safeConfiguredPath($key);
        }
        $this->configuredHttpsUrl('attestation_url');
    }

    private function latestTrustedPublicationRun(callable $fetch, string $token): array
    {
        $url = 'https://api.github.com/repos/labzefry/oneQay/actions/workflows/'
            .'durable-staging-release-publication.yml/runs?branch=main&status=success&per_page=10';
        $payload = $fetch($url, $token);
        $runs = $payload['workflow_runs'] ?? null;
        if (! is_array($runs)) {
            throw new DevelopmentUpdaterViolation('workflow_runs_invalid');
        }

        foreach ($runs as $run) {
            if (! is_array($run)) {
                continue;
            }
            if (($run['conclusion'] ?? null) !== 'success'
                || ($run['head_branch'] ?? null) !== 'main'
                || ! in_array($run['event'] ?? null, ['push', 'workflow_dispatch'], true)
                || ! is_int($run['id'] ?? null)
                || ($run['id'] ?? 0) <= 0) {
                continue;
            }

            $this->pattern($run['head_sha'] ?? null, '/\A[0-9a-f]{40}\z/', 'publication_source_invalid');

            return $run;
        }

        throw new DevelopmentUpdaterViolation('trusted_publication_not_found');
    }

    private function trustedPublicationRunById(
        callable $fetch,
        string $token,
        int $runId,
        string $source,
    ): array {
        $run = $fetch(
            'https://api.github.com/repos/labzefry/oneQay/actions/runs/'.$runId,
            $token,
        );

        if (($run['id'] ?? null) !== $runId
            || ($run['conclusion'] ?? null) !== 'success'
            || ($run['head_branch'] ?? null) !== 'main'
            || ($run['head_sha'] ?? null) !== $source
            || ! in_array($run['event'] ?? null, ['push', 'workflow_dispatch'], true)) {
            throw new DevelopmentUpdaterViolation('authorized_publication_run_invalid');
        }

        return $run;
    }

    private function trustedArtifactForRun(
        callable $fetch,
        string $token,
        int $runId,
        string $source,
    ): array {
        $payload = $fetch(
            'https://api.github.com/repos/labzefry/oneQay/actions/runs/'.$runId.'/artifacts?per_page=100',
            $token,
        );
        $artifacts = $payload['artifacts'] ?? null;
        if (! is_array($artifacts)) {
            throw new DevelopmentUpdaterViolation('actions_artifacts_invalid');
        }

        $expectedName = 'oneqay-durable-staging-'.substr($source, 0, 12).'-operator-bundle';
        foreach ($artifacts as $artifact) {
            if (! is_array($artifact)) {
                continue;
            }
            if (($artifact['name'] ?? null) !== $expectedName
                || ($artifact['expired'] ?? true) !== false) {
                continue;
            }

            $this->pattern(
                str_replace('sha256:', '', (string) ($artifact['digest'] ?? '')),
                '/\A[0-9a-f]{64}\z/',
                'outer_digest_invalid',
            );

            return $artifact;
        }

        throw new DevelopmentUpdaterViolation('trusted_actions_artifact_not_found');
    }

    private function assertForwardOnly(
        callable $fetch,
        string $token,
        string $current,
        string $candidate,
    ): void {
        $payload = $fetch(
            'https://api.github.com/repos/labzefry/oneQay/compare/'.$current.'...'.$candidate,
            $token,
        );

        $status = $payload['status'] ?? null;
        $base = $payload['base_commit']['sha'] ?? null;
        $head = $payload['merge_base_commit']['sha'] ?? null;
        if (! in_array($status, ['ahead', 'identical'], true)
            || $base !== $current
            || $head !== $current) {
            throw new DevelopmentUpdaterViolation('candidate_not_forward_from_running_source');
        }
    }

    private function validateBundle(
        array $manifest,
        array $handoff,
        string $archivePath,
        string $checksumPath,
        string $source,
        string $releaseId,
    ): string {
        if (($manifest['manifest_version'] ?? null) !== 1
            || ($manifest['schema_id'] ?? null) !== 'oneqay.durable-staging-release-manifest.v1'
            || ($manifest['product']['name'] ?? null) !== 'oneQay'
            || ($manifest['product']['repository'] ?? null) !== 'labzefry/oneQay'
            || ($manifest['release']['id'] ?? null) !== $releaseId
            || ($manifest['release']['channel'] ?? null) !== 'STAGING'
            || ($manifest['release']['environment'] ?? null) !== 'DURABLE_STAGING'
            || ($manifest['release']['production'] ?? null) !== false
            || ($manifest['release']['production_data_allowed'] ?? null) !== false
            || ($manifest['source']['commit_sha'] ?? null) !== $source
            || ($manifest['runtime']['required_runtime_class'] ?? null) !== 'durable-staging'
            || ($manifest['runtime']['runtime_build_tools_required'] ?? null) !== false
            || ($manifest['migration']['expected_count'] ?? null) !== 27
            || ($manifest['migration']['execution_state'] ?? null) !== 'NOT_EXECUTED_BY_ARTIFACT_BUILD'
            || ($manifest['migration']['execution_authorized'] ?? null) !== false
            || ($manifest['operational_boundary']['production_activation'] ?? null) !== 'NOT_AUTHORIZED'
            || ($manifest['operational_boundary']['migration27_execution'] ?? null) !== 'NOT_PERFORMED'
            || ($manifest['attribution'] ?? null) !== 'Lab | zefry') {
            throw new DevelopmentUpdaterViolation('governed_manifest_invalid');
        }

        $artifactHash = $this->pattern(
            $manifest['artifact']['sha256'] ?? null,
            '/\A[0-9a-f]{64}\z/',
            'inner_artifact_hash_invalid',
        );
        $filename = $manifest['artifact']['filename'] ?? null;
        $size = $manifest['artifact']['size_bytes'] ?? null;
        if ($filename !== $releaseId.'.tar.gz'
            || ! is_int($size)
            || $size < 1024
            || $size > self::MAX_INNER_BYTES) {
            throw new DevelopmentUpdaterViolation('inner_artifact_identity_invalid');
        }

        $this->assertRegularFile($archivePath, 1024, self::MAX_INNER_BYTES, 'inner_artifact_invalid');
        if (filesize($archivePath) !== $size
            || ! hash_equals($artifactHash, (string) hash_file('sha256', $archivePath))) {
            throw new DevelopmentUpdaterViolation('inner_artifact_integrity_failed');
        }

        $sidecar = trim((string) file_get_contents($checksumPath));
        if (! hash_equals($artifactHash.'  '.$filename, $sidecar)) {
            throw new DevelopmentUpdaterViolation('inner_artifact_sidecar_invalid');
        }

        if (($handoff['schema_version'] ?? null) !== 1
            || ($handoff['handoff_state'] ?? null) !== 'VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED'
            || ($handoff['product']['name'] ?? null) !== 'oneQay'
            || ($handoff['product']['repository'] ?? null) !== 'labzefry/oneQay'
            || ($handoff['artifact']['release_id'] ?? null) !== $releaseId
            || ($handoff['artifact']['source_commit'] ?? null) !== $source
            || ($handoff['artifact']['artifact_sha256'] ?? null) !== $artifactHash
            || ($handoff['runtime']['required_runtime_class'] ?? null) !== 'durable-staging'
            || ($handoff['runtime']['production'] ?? null) !== false
            || ($handoff['migration']['execution_state'] ?? null) !== 'NOT_PERFORMED'
            || ($handoff['migration']['execution_authorized'] ?? null) !== false
            || ($handoff['deployment']['authority_state'] ?? null) !== 'NOT_GRANTED'
            || ($handoff['operational_boundary']['production_activation'] ?? null) !== 'NOT_AUTHORIZED') {
            throw new DevelopmentUpdaterViolation('deployment_handoff_invalid');
        }

        $manifestSha = hash_file(
            'sha256',
            dirname($archivePath).'/'.$releaseId.'.manifest.json',
        );
        if (! is_string($manifestSha)
            || ($handoff['artifact']['manifest_sha256'] ?? null) !== $manifestSha) {
            throw new DevelopmentUpdaterViolation('deployment_handoff_manifest_mismatch');
        }

        return $artifactHash;
    }

    private function extractCandidate(
        string $archivePath,
        string $releaseRoot,
        string $releaseId,
        string $source,
        string $artifactHash,
    ): void {
        if (! is_dir($releaseRoot) || is_link($releaseRoot) || ! is_writable($releaseRoot)) {
            throw new DevelopmentUpdaterViolation('release_root_unavailable');
        }

        $stage = $releaseRoot.'/.development-update-'.bin2hex(random_bytes(8));
        if (! mkdir($stage, 0700)) {
            throw new DevelopmentUpdaterViolation('candidate_stage_unavailable');
        }

        try {
            $this->extractArchive($archivePath, $stage, 'candidate_extraction_failed');
            $candidate = $stage.'/'.$releaseId;
            if (! is_dir($candidate) || is_link($candidate)) {
                throw new DevelopmentUpdaterViolation('candidate_release_root_missing');
            }

            foreach ([
                'RELEASE.json',
                'apps/web/vendor/autoload.php',
                'apps/web/bootstrap/app.php',
                'apps/web/public/index.php',
                'apps/web/public/.htaccess',
                'apps/web/public/build/manifest.json',
            ] as $required) {
                if (! is_file($candidate.'/'.$required) || is_link($candidate.'/'.$required)) {
                    throw new DevelopmentUpdaterViolation('candidate_structure_invalid');
                }
            }

            if (file_exists($candidate.'/apps/web/.env') || is_link($candidate.'/apps/web/.env')) {
                throw new DevelopmentUpdaterViolation('candidate_embedded_env_forbidden');
            }

            $metadata = $this->readJson($candidate.'/RELEASE.json');
            if (($metadata['product'] ?? null) !== 'oneQay'
                || ($metadata['release_id'] ?? null) !== $releaseId
                || ($metadata['source_commit'] ?? null) !== $source
                || ($metadata['environment'] ?? null) !== 'DURABLE_STAGING'
                || ($metadata['required_runtime_class'] ?? null) !== 'durable-staging'
                || ($metadata['production'] ?? null) !== false
                || ($metadata['migration_count'] ?? null) !== 27
                || ($metadata['migration_execution_state'] ?? null) !== 'NOT_EXECUTED_BY_ARTIFACT_BUILD'
                || ($metadata['migration_execution_authorized'] ?? null) !== false) {
                throw new DevelopmentUpdaterViolation('candidate_release_metadata_invalid');
            }

            $migrations = glob($candidate.'/apps/web/database/migrations/*.php');
            if (! is_array($migrations) || count($migrations) !== 27) {
                throw new DevelopmentUpdaterViolation('candidate_migration_set_invalid');
            }

            $this->validateExtractedTree($candidate);

            $destination = $releaseRoot.'/'.$releaseId;
            if (! rename($candidate, $destination)) {
                throw new DevelopmentUpdaterViolation('candidate_commit_failed');
            }
            @chmod($destination, 0500);

            if (preg_match('/\A[0-9a-f]{64}\z/', $artifactHash) !== 1) {
                throw new DevelopmentUpdaterViolation('candidate_artifact_binding_invalid');
            }
        } finally {
            $this->removeTree($stage);
        }
    }

    private function bindRuntimeEnvironment(string $candidateDirectory, string $runtimeEnvPath): void
    {
        $this->assertRegularFile($runtimeEnvPath, 2, self::MAX_JSON_BYTES, 'runtime_env_unavailable');
        $permissions = @fileperms($runtimeEnvPath);
        if (! is_int($permissions) || (($permissions & 0o077) !== 0)) {
            throw new DevelopmentUpdaterViolation('runtime_env_permissions_invalid');
        }

        $link = $candidateDirectory.'/apps/web/.env';
        if (file_exists($link) || is_link($link)) {
            throw new DevelopmentUpdaterViolation('candidate_runtime_env_path_occupied');
        }

        if (! function_exists('symlink') || ! symlink($runtimeEnvPath, $link)) {
            throw new DevelopmentUpdaterViolation('candidate_runtime_env_link_failed');
        }
        if (! is_link($link) || realpath($link) !== realpath($runtimeEnvPath)) {
            throw new DevelopmentUpdaterViolation('candidate_runtime_env_link_invalid');
        }
    }

    private function readPrivateRuntimeEnv(string $path): string
    {
        $this->assertRegularFile($path, 2, self::MAX_JSON_BYTES, 'runtime_env_unavailable');
        $permissions = @fileperms($path);
        if (! is_int($permissions) || (($permissions & 0o077) !== 0)) {
            throw new DevelopmentUpdaterViolation('runtime_env_permissions_invalid');
        }

        $raw = file_get_contents($path);
        if (! is_string($raw)) {
            throw new DevelopmentUpdaterViolation('runtime_env_read_failed');
        }

        return $raw;
    }

    private function rewriteRuntimeIdentity(string $environment, string $source, string $artifact): string
    {
        $replacements = [
            'ONEQAY_RUNNING_SOURCE_COMMIT' => $source,
            'ONEQAY_RUNNING_ARTIFACT_SHA256' => $artifact,
        ];

        foreach ($replacements as $key => $value) {
            $pattern = '/^'.preg_quote($key, '/').'=[^\r\n]*$/m';
            if (preg_match_all($pattern, $environment) !== 1) {
                throw new DevelopmentUpdaterViolation('runtime_identity_binding_invalid');
            }
            $updated = preg_replace($pattern, $key.'='.$value, $environment, 1);
            if (! is_string($updated)) {
                throw new DevelopmentUpdaterViolation('runtime_identity_rewrite_failed');
            }
            $environment = $updated;
        }

        return $environment;
    }

    private function currentActiveRelease(string $activePointer, string $releaseRoot): ?string
    {
        if (! is_link($activePointer)) {
            return null;
        }
        $resolved = realpath($activePointer);
        $root = realpath($releaseRoot);
        if (! is_string($resolved) || ! is_string($root) || ! str_starts_with($resolved.'/', rtrim($root, '/').'/')) {
            throw new DevelopmentUpdaterViolation('active_release_pointer_invalid');
        }

        return $resolved;
    }

    private function releaseIdentity(string $releaseDirectory): array
    {
        $metadata = $this->readJson($releaseDirectory.'/RELEASE.json');
        $source = $this->pattern($metadata['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'release_source_invalid');
        $release = $this->pattern($metadata['release_id'] ?? null, '/\Adurable-staging-[0-9a-f]{12}\z/', 'release_id_invalid');

        $envLink = $releaseDirectory.'/apps/web/.env';
        if (! is_link($envLink)) {
            throw new DevelopmentUpdaterViolation('release_runtime_env_link_missing');
        }
        $env = (string) file_get_contents($envLink);
        if (preg_match('/^ONEQAY_RUNNING_ARTIFACT_SHA256=([0-9a-f]{64})$/m', $env, $match) !== 1) {
            throw new DevelopmentUpdaterViolation('release_artifact_env_binding_missing');
        }

        return [
            'source_commit' => $source,
            'release_id' => $release,
            'artifact_sha256' => $match[1],
        ];
    }

    private function atomicPoint(string $pointer, string $target): void
    {
        if (! is_dir($target) || is_link($target)) {
            throw new DevelopmentUpdaterViolation('pointer_target_invalid');
        }

        $directory = dirname($pointer);
        if (! is_dir($directory) || ! is_writable($directory) || ! function_exists('symlink')) {
            throw new DevelopmentUpdaterViolation('pointer_directory_unavailable');
        }

        $temporary = $directory.'/.oneqay-pointer-'.bin2hex(random_bytes(8));
        if (! symlink($target, $temporary)) {
            throw new DevelopmentUpdaterViolation('pointer_temp_create_failed');
        }

        try {
            if (! rename($temporary, $pointer)) {
                throw new DevelopmentUpdaterViolation('pointer_atomic_switch_failed');
            }
        } finally {
            if (is_link($temporary)) {
                @unlink($temporary);
            }
        }

        if (! is_link($pointer) || realpath($pointer) !== realpath($target)) {
            throw new DevelopmentUpdaterViolation('pointer_readback_failed');
        }
    }

    private function prepareFixedPublicBuild(
        string $documentRoot,
        string $candidateBuild,
        string $requestId,
    ): string {
        if (! is_dir($documentRoot)
            || is_link($documentRoot)
            || ! is_writable($documentRoot)
            || ! is_file($documentRoot.'/index.php')
            || ! is_file($documentRoot.'/.htaccess')
            || ! is_dir($documentRoot.'/build')
            || ! is_dir($candidateBuild)) {
            throw new DevelopmentUpdaterViolation('fixed_public_bridge_invalid');
        }

        $current = $documentRoot.'/build';
        $temporary = $documentRoot.'/.oneqay-build-new-'.substr(hash('sha256', $requestId), 0, 12);
        $backup = $documentRoot.'/.oneqay-build-old-'.substr(hash('sha256', $requestId), 0, 12);
        if (file_exists($temporary) || file_exists($backup)) {
            throw new DevelopmentUpdaterViolation('fixed_public_bridge_staging_collision');
        }

        $this->copyTree($current, $temporary);
        $this->copyTree($candidateBuild, $temporary);

        if (! rename($current, $backup)) {
            $this->removeTree($temporary);
            throw new DevelopmentUpdaterViolation('fixed_public_bridge_backup_failed');
        }

        if (! rename($temporary, $current)) {
            @rename($backup, $current);
            $this->removeTree($temporary);
            throw new DevelopmentUpdaterViolation('fixed_public_bridge_swap_failed');
        }

        return $backup;
    }

    private function restoreFixedPublicBuild(string $documentRoot, string $backup): void
    {
        $current = $documentRoot.'/build';
        if (! is_dir($backup)) {
            return;
        }

        $failed = $documentRoot.'/.oneqay-build-failed-'.bin2hex(random_bytes(6));
        if (is_dir($current)) {
            if (! rename($current, $failed)) {
                throw new DevelopmentUpdaterViolation('fixed_public_bridge_recovery_failed');
            }
        }

        if (! rename($backup, $current)) {
            if (is_dir($failed)) {
                @rename($failed, $current);
            }
            throw new DevelopmentUpdaterViolation('fixed_public_bridge_recovery_failed');
        }

        $this->removeTree($failed);
    }

    private function assertAttestation(
        array $attestation,
        string $source,
        string $artifact,
        string $environment,
    ): void {
        $required = [
            'schema_version','environment_id','runtime_class','runtime_model','environment_isolation',
            'serving_application_runtime','synthetic_fixture_runtime','production_traffic_served',
            'durable_persistence_enabled','durable_session_control_enabled','durable_authorization_enabled',
            'durable_transaction_boundary_enabled','durable_pos_persistence_enabled',
            'exact_running_source_commit','exact_running_artifact_sha256',
            'authenticated_configuration_mutation_channel','read_before_write_read_after_supported',
            'non_mutating_health_attestation_supported','verified_flag_rollback_supported',
            'activation_authority_binding','feature_activation_state','secrets_embedded',
        ];
        $actual = array_keys($attestation);
        sort($actual, SORT_STRING);
        sort($required, SORT_STRING);
        if ($actual !== $required
            || ($attestation['schema_version'] ?? null) !== 1
            || ($attestation['environment_id'] ?? null) !== $environment
            || ($attestation['runtime_class'] ?? null) !== 'durable-staging'
            || ($attestation['runtime_model'] ?? null) !== 'NON_SYNTHETIC_DURABLE_RUNTIME'
            || ($attestation['environment_isolation'] ?? null) !== 'ISOLATED_NON_PRODUCTION'
            || ($attestation['serving_application_runtime'] ?? null) !== true
            || ($attestation['synthetic_fixture_runtime'] ?? null) !== false
            || ($attestation['production_traffic_served'] ?? null) !== false
            || ($attestation['exact_running_source_commit'] ?? null) !== $source
            || ($attestation['exact_running_artifact_sha256'] ?? null) !== $artifact
            || ($attestation['activation_authority_binding'] ?? null) !== 'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY'
            || ($attestation['feature_activation_state'] ?? null) !== 'INACTIVE'
            || ($attestation['secrets_embedded'] ?? null) !== false) {
            throw new DevelopmentUpdaterViolation('runtime_attestation_invalid');
        }

        foreach ([
            'durable_persistence_enabled','durable_session_control_enabled','durable_authorization_enabled',
            'durable_transaction_boundary_enabled','durable_pos_persistence_enabled',
            'authenticated_configuration_mutation_channel','read_before_write_read_after_supported',
            'non_mutating_health_attestation_supported','verified_flag_rollback_supported',
        ] as $field) {
            if (($attestation[$field] ?? null) !== true) {
                throw new DevelopmentUpdaterViolation('runtime_attestation_capability_missing');
            }
        }
    }

    private function deploymentEvidence(
        array $request,
        string $releaseId,
        string $source,
        string $artifact,
        array $readback,
    ): array {
        $fingerprint = hash('sha256', json_encode([
            'mode' => 'GOVERNED_DEVELOPMENT_UPDATER',
            'request_id' => $request['request_id'],
            'release_id' => $releaseId,
            'source_commit' => $source,
            'artifact_sha256' => $artifact,
            'environment_id' => config('oneqay.development_updater.environment_id'),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $authorityId = 'durable-staging-deployment-authority-'.substr(
            hash('sha256', (string) ($request['signature'] ?? '').'|'.$source.'|'.$artifact),
            0,
            24,
        );
        $authoritySha = hash('sha256', $authorityId.'|'.$fingerprint);
        $requestSha = hash('sha256', json_encode($request, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return [
            'schema_version' => 1,
            'product' => 'oneQay',
            'evidence_state' => 'DEPLOYED_VERIFIED_NOT_SELECTED',
            'environment_id' => (string) config('oneqay.development_updater.environment_id'),
            'runtime_class' => 'durable-staging',
            'release_id' => $releaseId,
            'source_commit' => $source,
            'artifact_sha256' => $artifact,
            'deployment_plan_fingerprint' => $fingerprint,
            'deployment_authority_id' => $authorityId,
            'deployment_authority_sha256' => $authoritySha,
            'deployment_request_id' => $request['request_id'],
            'deployment_request_sha256' => $requestSha,
            'verification' => [
                'preflight_passed' => true,
                'previous_active_release_preserved' => true,
                'immutable_release_extracted' => true,
                'public_document_root_verified' => true,
                'external_runtime_configuration_bound' => true,
                'provenance_readback_verified' => true,
                'configuration_read_before_write_verified' => true,
                'configuration_read_after_write_verified' => true,
                'non_mutating_health_attestation_verified' => true,
                'rollback_path_verified' => true,
            ],
            'runtime_readback' => [
                'environment_id' => $readback['environment_id'],
                'runtime_class' => 'durable-staging',
                'exact_running_source_commit' => $source,
                'exact_running_artifact_sha256' => $artifact,
                'durable_staging_runtime_enabled' => true,
                'production_data_allowed' => false,
            ],
            'operational_boundary' => [
                'migration27_execution' => 'NOT_PERFORMED',
                'permission_provisioning' => 'NONE',
                'feature_activation' => 'INACTIVE',
                'technical_preview_activation' => 'NOT_AUTHORIZED',
                'production_activation' => 'NOT_AUTHORIZED',
                'updater_activation' => 'INACTIVE',
                'target_selection' => 'NOT_PERFORMED',
                'selected_target' => null,
                'producer_dispatch' => 'NOT_PERFORMED',
            ],
            'secrets_embedded' => false,
            'attribution' => 'Lab | zefry',
        ];
    }

    private function safeResult(
        string $state,
        ?string $releaseId,
        ?string $source,
        ?string $artifact,
        int $completedAt,
        string $safeCode,
        ?string $evidenceFile = null,
    ): array {
        return [
            'schema_version' => 1,
            'state' => $state,
            'release_id' => $releaseId,
            'source_commit' => $source,
            'artifact_sha256' => $artifact,
            'completed_at_unix' => $completedAt,
            'safe_code' => $safeCode,
            'evidence_file' => $evidenceFile,
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
    }

    private function githubJson(string $url, string $token): array
    {
        if (! str_starts_with($url, 'https://api.github.com/repos/labzefry/oneQay/')) {
            throw new DevelopmentUpdaterViolation('github_url_not_allowlisted');
        }

        $body = $this->httpGet($url, [
            'Accept: application/vnd.github+json',
            'Authorization: Bearer '.$token,
            'X-GitHub-Api-Version: 2022-11-28',
            'User-Agent: oneQay-governed-development-updater',
        ], self::MAX_JSON_BYTES);

        try {
            $value = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new DevelopmentUpdaterViolation('github_json_invalid');
        }
        if (! is_array($value) || array_is_list($value)) {
            throw new DevelopmentUpdaterViolation('github_json_shape_invalid');
        }

        return $value;
    }

    private function githubDownload(string $url, string $token, string $destination): void
    {
        if (! preg_match(
            '#\Ahttps://api\.github\.com/repos/labzefry/oneQay/actions/artifacts/[1-9][0-9]*/zip\z#',
            $url,
        )) {
            throw new DevelopmentUpdaterViolation('github_download_url_not_allowlisted');
        }

        $this->httpDownload($url, [
            'Accept: application/vnd.github+json',
            'Authorization: Bearer '.$token,
            'X-GitHub-Api-Version: 2022-11-28',
            'User-Agent: oneQay-governed-development-updater',
        ], $destination, self::MAX_OUTER_BYTES);
    }

    private function httpsJson(string $url, string $token): array
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || ! is_string($parts['host'] ?? null)
            || $parts['host'] === ''
            || array_key_exists('user', $parts)
            || array_key_exists('pass', $parts)
            || array_key_exists('fragment', $parts)) {
            throw new DevelopmentUpdaterViolation('attestation_url_invalid');
        }

        $body = $this->httpGet($url, [
            'Accept: application/json',
            'Authorization: Bearer '.$token,
        ], 32768);

        try {
            $value = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new DevelopmentUpdaterViolation('attestation_json_invalid');
        }
        if (! is_array($value) || array_is_list($value)) {
            throw new DevelopmentUpdaterViolation('attestation_shape_invalid');
        }

        return $value;
    }

    private function httpGet(string $url, array $headers, int $maxBytes): string
    {
        $temporary = tempnam(sys_get_temp_dir(), '.oneqay-http-');
        if ($temporary === false) {
            throw new DevelopmentUpdaterViolation('http_temp_failed');
        }
        try {
            $this->httpDownload($url, $headers, $temporary, $maxBytes);
            $body = file_get_contents($temporary);
            if (! is_string($body) || strlen($body) < 2 || strlen($body) > $maxBytes) {
                throw new DevelopmentUpdaterViolation('http_response_invalid');
            }

            return $body;
        } finally {
            @unlink($temporary);
        }
    }

    private function httpDownload(string $url, array $headers, string $destination, int $maxBytes): void
    {
        $directory = dirname($destination);
        if (! is_dir($directory) || ! is_writable($directory)) {
            throw new DevelopmentUpdaterViolation('download_destination_invalid');
        }

        if (extension_loaded('curl') && function_exists('curl_init')) {
            $handle = fopen($destination, 'wb');
            if ($handle === false) {
                throw new DevelopmentUpdaterViolation('download_open_failed');
            }
            $curl = curl_init($url);
            if ($curl === false) {
                fclose($handle);
                throw new DevelopmentUpdaterViolation('download_client_failed');
            }

            curl_setopt_array($curl, [
                CURLOPT_FILE => $handle,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            ]);
            $ok = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);
            fclose($handle);

            if ($ok !== true || $status < 200 || $status >= 300) {
                @unlink($destination);
                throw new DevelopmentUpdaterViolation('download_failed');
            }
        } else {
            if (! filter_var((string) ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
                throw new DevelopmentUpdaterViolation('https_client_unavailable');
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers)."\r\n",
                    'timeout' => 120,
                    'follow_location' => 1,
                    'max_redirects' => 3,
                    'ignore_errors' => false,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ]);
            $input = @fopen($url, 'rb', false, $context);
            $output = @fopen($destination, 'wb');
            if ($input === false || $output === false) {
                if (is_resource($input)) {
                    fclose($input);
                }
                if (is_resource($output)) {
                    fclose($output);
                }
                @unlink($destination);
                throw new DevelopmentUpdaterViolation('download_failed');
            }

            $bytes = stream_copy_to_stream($input, $output, $maxBytes + 1);
            fclose($input);
            fclose($output);
            if (! is_int($bytes) || $bytes <= 0 || $bytes > $maxBytes) {
                @unlink($destination);
                throw new DevelopmentUpdaterViolation('download_size_invalid');
            }
        }

        $size = filesize($destination);
        if (! is_int($size) || $size <= 0 || $size > $maxBytes) {
            @unlink($destination);
            throw new DevelopmentUpdaterViolation('download_size_invalid');
        }
        @chmod($destination, 0600);
    }

    private function extractArchive(string $archivePath, string $destination, string $safeCode): void
    {
        try {
            $archive = new PharData($archivePath);
            $archive->extractTo($destination, null, false);
        } catch (Throwable) {
            throw new DevelopmentUpdaterViolation($safeCode);
        }
    }

    private function validateExtractedTree(string $root): void
    {
        $files = 0;
        $bytes = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            $relative = substr($path, strlen($root) + 1);
            if (! is_string($relative) || $relative === '' || str_contains($relative, "\0")) {
                throw new DevelopmentUpdaterViolation('candidate_tree_invalid');
            }

            foreach (explode(DIRECTORY_SEPARATOR, $relative) as $segment) {
                if ($segment === '' || $segment === '.' || $segment === '..') {
                    throw new DevelopmentUpdaterViolation('candidate_tree_invalid');
                }
                if (in_array(strtolower($segment), ['.git', '.svn'], true)) {
                    throw new DevelopmentUpdaterViolation('candidate_repository_metadata_forbidden');
                }
            }

            if (is_link($path)) {
                throw new DevelopmentUpdaterViolation('candidate_link_forbidden');
            }
            if ($entry->isDir()) {
                continue;
            }
            if (! $entry->isFile()) {
                throw new DevelopmentUpdaterViolation('candidate_special_file_forbidden');
            }

            $basename = strtolower($entry->getBasename());
            $extension = strtolower($entry->getExtension());
            if ($basename === '.env'
                || str_starts_with($basename, '.env.')
                || in_array($basename, ['id_rsa', 'id_ed25519'], true)
                || in_array($extension, ['pem', 'key', 'p12', 'pfx'], true)) {
                throw new DevelopmentUpdaterViolation('candidate_secret_file_forbidden');
            }

            ++$files;
            $bytes += max(0, $entry->getSize());
            if ($files > 100000 || $bytes > 536870912) {
                throw new DevelopmentUpdaterViolation('candidate_limits_exceeded');
            }
        }
    }

    private function copyTree(string $source, string $destination): void
    {
        if (! is_dir($source) || is_link($source)) {
            throw new DevelopmentUpdaterViolation('copy_source_invalid');
        }
        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            throw new DevelopmentUpdaterViolation('copy_destination_failed');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            $relative = substr($path, strlen($source) + 1);
            $target = $destination.'/'.$relative;

            if (is_link($path)) {
                throw new DevelopmentUpdaterViolation('copy_link_forbidden');
            }
            if ($entry->isDir()) {
                if (! is_dir($target) && ! mkdir($target, 0755, true) && ! is_dir($target)) {
                    throw new DevelopmentUpdaterViolation('copy_directory_failed');
                }
                continue;
            }
            if (! $entry->isFile()) {
                throw new DevelopmentUpdaterViolation('copy_special_file_forbidden');
            }
            $parent = dirname($target);
            if (! is_dir($parent) && ! mkdir($parent, 0755, true) && ! is_dir($parent)) {
                throw new DevelopmentUpdaterViolation('copy_directory_failed');
            }
            if (! copy($path, $target)) {
                throw new DevelopmentUpdaterViolation('copy_file_failed');
            }
        }
    }

    private function removeTree(string $path): void
    {
        if ($path === '' || $path === '/' || (! file_exists($path) && ! is_link($path))) {
            return;
        }
        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $entry) {
            $target = $entry->getPathname();
            if (is_link($target) || $entry->isFile()) {
                @unlink($target);
            } elseif ($entry->isDir()) {
                @rmdir($target);
            }
        }
        @rmdir($path);
    }

    private function safeConfiguredPath(string $key): string
    {
        $path = trim((string) config('oneqay.development_updater.'.$key, ''));
        if ($path === ''
            || $path === '/'
            || strlen($path) > 4096
            || ! str_starts_with($path, '/')
            || str_contains($path, "\0")
            || str_contains($path, '\\')
            || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path) === 1
            || preg_match('#//+#', $path) === 1) {
            throw new DevelopmentUpdaterViolation($key.'_invalid');
        }

        return rtrim($path, '/');
    }

    private function configuredHttpsUrl(string $key): string
    {
        $url = trim((string) config('oneqay.development_updater.'.$key, ''));
        $parts = parse_url($url);
        if (! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || ! is_string($parts['host'] ?? null)
            || $parts['host'] === ''
            || array_key_exists('user', $parts)
            || array_key_exists('pass', $parts)
            || array_key_exists('fragment', $parts)) {
            throw new DevelopmentUpdaterViolation($key.'_invalid');
        }

        return $url;
    }

    private function readJson(string $path): array
    {
        $this->assertRegularFile($path, 2, self::MAX_JSON_BYTES, 'json_input_invalid');
        try {
            $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new DevelopmentUpdaterViolation('json_input_invalid');
        }

        if (! is_array($value) || array_is_list($value)) {
            throw new DevelopmentUpdaterViolation('json_input_invalid');
        }

        return $value;
    }

    private function assertRegularFile(
        string $path,
        int $minimum,
        int $maximum,
        string $safeCode,
    ): void {
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            throw new DevelopmentUpdaterViolation($safeCode);
        }
        $size = filesize($path);
        if (! is_int($size) || $size < $minimum || $size > $maximum) {
            throw new DevelopmentUpdaterViolation($safeCode);
        }
    }

    private function pattern(mixed $value, string $pattern, string $safeCode): string
    {
        if (! is_string($value) || preg_match($pattern, $value) !== 1) {
            throw new DevelopmentUpdaterViolation($safeCode);
        }

        return $value;
    }

    private function atomicWriteBytes(string $path, string $bytes, int $mode): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) || ! is_writable($directory) || is_link($path)) {
            throw new DevelopmentUpdaterViolation('atomic_write_target_invalid');
        }

        $temporary = tempnam($directory, '.oneqay-env-');
        if ($temporary === false) {
            throw new DevelopmentUpdaterViolation('atomic_write_temp_failed');
        }
        try {
            if (file_put_contents($temporary, $bytes, LOCK_EX) !== strlen($bytes)) {
                throw new DevelopmentUpdaterViolation('atomic_write_failed');
            }
            @chmod($temporary, $mode);
            if (! rename($temporary, $path)) {
                throw new DevelopmentUpdaterViolation('atomic_write_commit_failed');
            }
            @chmod($path, $mode);
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function atomicWriteJson(string $path, array $payload): void
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
        $this->atomicWriteBytes($path, $json, 0600);
    }
}
