<?php

declare(strict_types=1);

final class DurableStagingDeploymentHandoffException extends RuntimeException
{
}

function handoffFail(string $code): never
{
    throw new DurableStagingDeploymentHandoffException($code);
}

/** @return array<string, mixed> */
function handoffLoadJson(string $path): array
{
    if (! is_file($path) || is_link($path)) {
        handoffFail('input_json_unavailable');
    }

    $raw = file_get_contents($path);
    if (! is_string($raw) || $raw === '') {
        handoffFail('input_json_unreadable');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        handoffFail('input_json_malformed');
    }

    if (! is_array($decoded)) {
        handoffFail('input_json_malformed');
    }

    return $decoded;
}

/**
 * @param list<string> $command
 * @return array{stdout:string,stderr:string}
 */
function handoffRun(array $command): array
{
    $pipes = [];
    $process = proc_open(
        $command,
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    if (! is_resource($process)) {
        handoffFail('archive_inspection_unavailable');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0 || ! is_string($stdout) || ! is_string($stderr)) {
        handoffFail('archive_inspection_failed');
    }

    return ['stdout' => $stdout, 'stderr' => $stderr];
}

function handoffAssertLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        handoffFail($code);
    }
}

function handoffAssertPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        handoffFail($code);
    }

    return $value;
}

function handoffAssertSafeArchivePath(string $path, string $releaseId): void
{
    if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\')) {
        handoffFail('archive_path_invalid');
    }

    if (str_starts_with($path, '/') || preg_match('/\A[A-Za-z]:\//', $path) === 1) {
        handoffFail('archive_absolute_path_forbidden');
    }

    $segments = explode('/', rtrim($path, '/'));
    foreach ($segments as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..') {
            handoffFail('archive_path_traversal_forbidden');
        }

        if (in_array(strtolower($segment), ['.git', '.svn', 'node_modules', 'tests'], true)) {
            handoffFail('archive_forbidden_path');
        }
    }

    if (($segments[0] ?? null) !== $releaseId) {
        handoffFail('archive_release_root_mismatch');
    }

    $basename = strtolower((string) end($segments));
    $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));

    if (
        $basename === '.env'
        || str_starts_with($basename, '.env.')
        || in_array($basename, ['id_rsa', 'id_ed25519'], true)
        || in_array($extension, ['pem', 'key', 'p12', 'pfx'], true)
    ) {
        handoffFail('archive_secret_shape_forbidden');
    }
}

/**
 * @param list<string> $archivePaths
 */
function handoffAssertArchiveShape(array $archivePaths, string $releaseId): void
{
    $required = [
        $releaseId.'/RELEASE.json',
        $releaseId.'/apps/web/vendor/autoload.php',
        $releaseId.'/apps/web/bootstrap/app.php',
        $releaseId.'/apps/web/public/index.php',
        $releaseId.'/apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
        $releaseId.'/release-contract/DURABLE_STAGING_RELEASE_ARTIFACT_CONTRACT.json',
        $releaseId.'/release-contract/durable-staging-manifest-v1.schema.json',
    ];

    foreach ($required as $requiredPath) {
        if (! in_array($requiredPath, $archivePaths, true)) {
            handoffFail('archive_required_path_missing');
        }
    }

    $migrationCount = 0;
    foreach ($archivePaths as $path) {
        if (
            preg_match(
                '/\A'.preg_quote($releaseId, '/').'\/apps\/web\/database\/migrations\/[^\/]+\.php\z/',
                $path,
            ) === 1
        ) {
            ++$migrationCount;
        }
    }

    if ($migrationCount !== 27) {
        handoffFail('archive_migration_count_mismatch');
    }
}

/** @return array<string, mixed> */
function handoffPrepare(
    string $manifestPath,
    string $artifactPath,
    string $expectedSourceCommit,
): array {
    if (preg_match('/\A[0-9a-f]{40}\z/', $expectedSourceCommit) !== 1) {
        handoffFail('expected_source_commit_invalid');
    }

    if (! is_file($artifactPath) || is_link($artifactPath)) {
        handoffFail('artifact_unavailable');
    }

    $manifest = handoffLoadJson($manifestPath);

    handoffAssertLiteral($manifest['manifest_version'] ?? null, 1, 'manifest_version_unsupported');
    handoffAssertLiteral(
        $manifest['schema_id'] ?? null,
        'oneqay.durable-staging-release-manifest.v1',
        'manifest_schema_unsupported',
    );
    handoffAssertLiteral($manifest['product']['name'] ?? null, 'oneQay', 'manifest_product_mismatch');
    handoffAssertLiteral(
        $manifest['product']['repository'] ?? null,
        'labzefry/oneQay',
        'manifest_repository_mismatch',
    );

    $releaseId = handoffAssertPattern(
        $manifest['release']['id'] ?? null,
        '/\Adurable-staging-[0-9a-f]{12}\z/',
        'manifest_release_id_invalid',
    );
    handoffAssertLiteral($releaseId, 'durable-staging-'.substr($expectedSourceCommit, 0, 12), 'release_source_binding_mismatch');
    handoffAssertLiteral($manifest['release']['channel'] ?? null, 'STAGING', 'manifest_channel_mismatch');
    handoffAssertLiteral($manifest['release']['environment'] ?? null, 'DURABLE_STAGING', 'manifest_environment_mismatch');
    handoffAssertLiteral($manifest['release']['production'] ?? null, false, 'production_artifact_forbidden');
    handoffAssertLiteral($manifest['release']['production_data_allowed'] ?? null, false, 'production_data_artifact_forbidden');
    handoffAssertLiteral($manifest['release']['synthetic_fixture_runtime'] ?? null, false, 'synthetic_fixture_artifact_forbidden');

    handoffAssertLiteral($manifest['source']['commit_sha'] ?? null, $expectedSourceCommit, 'manifest_source_mismatch');
    handoffAssertLiteral(
        $manifest['runtime']['required_runtime_class'] ?? null,
        'durable-staging',
        'runtime_class_mismatch',
    );
    handoffAssertLiteral($manifest['migration']['source_included'] ?? null, true, 'migration_source_missing');
    handoffAssertLiteral($manifest['migration']['expected_count'] ?? null, 27, 'migration_count_mismatch');
    handoffAssertLiteral(
        $manifest['migration']['latest_migration'] ?? null,
        '0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
        'migration_tail_mismatch',
    );
    handoffAssertLiteral(
        $manifest['migration']['execution_state'] ?? null,
        'NOT_EXECUTED_BY_ARTIFACT_BUILD',
        'migration_execution_state_invalid',
    );
    handoffAssertLiteral($manifest['migration']['execution_authorized'] ?? null, false, 'migration_authority_forbidden');
    handoffAssertLiteral(
        $manifest['attestation_binding']['environment_values_embedded'] ?? null,
        false,
        'embedded_environment_values_forbidden',
    );
    handoffAssertLiteral(
        $manifest['operational_boundary']['environment_deployment'] ?? null,
        'NOT_PERFORMED',
        'artifact_deployment_boundary_invalid',
    );
    handoffAssertLiteral(
        $manifest['operational_boundary']['migration27_execution'] ?? null,
        'NOT_PERFORMED',
        'artifact_migration_boundary_invalid',
    );
    handoffAssertLiteral(
        $manifest['operational_boundary']['selected_target'] ?? 'non-null',
        null,
        'artifact_selected_target_forbidden',
    );
    handoffAssertLiteral(
        $manifest['operational_boundary']['producer_dispatch'] ?? null,
        'NOT_PERFORMED',
        'artifact_producer_dispatch_boundary_invalid',
    );

    $artifactFilename = basename($artifactPath);
    handoffAssertLiteral(
        $manifest['artifact']['filename'] ?? null,
        $artifactFilename,
        'artifact_filename_mismatch',
    );

    $artifactSize = filesize($artifactPath);
    $artifactSha256 = hash_file('sha256', $artifactPath);
    $manifestSha256 = hash_file('sha256', $manifestPath);
    if (! is_int($artifactSize) || $artifactSize < 1 || ! is_string($artifactSha256) || ! is_string($manifestSha256)) {
        handoffFail('artifact_digest_unavailable');
    }

    handoffAssertLiteral($manifest['artifact']['size_bytes'] ?? null, $artifactSize, 'artifact_size_mismatch');
    handoffAssertLiteral($manifest['artifact']['sha256'] ?? null, $artifactSha256, 'artifact_digest_mismatch');

    $list = handoffRun(['tar', '-tzf', $artifactPath]);
    $archivePaths = array_values(array_filter(
        array_map(static fn (string $line): string => rtrim($line, '/'), preg_split('/\R/', trim($list['stdout'])) ?: []),
        static fn (string $line): bool => $line !== '',
    ));

    if ($archivePaths === []) {
        handoffFail('archive_empty');
    }

    foreach ($archivePaths as $path) {
        handoffAssertSafeArchivePath($path, $releaseId);
    }

    $verbose = handoffRun(['tar', '-tvzf', $artifactPath]);
    foreach (preg_split('/\R/', trim($verbose['stdout'])) ?: [] as $line) {
        if ($line === '') {
            continue;
        }

        $type = $line[0] ?? '';
        if ($type === 'l' || $type === 'h') {
            handoffFail('archive_link_forbidden');
        }
    }

    handoffAssertArchiveShape($archivePaths, $releaseId);

    $releaseJson = handoffRun(['tar', '-xOzf', $artifactPath, $releaseId.'/RELEASE.json']);
    try {
        $releaseMetadata = json_decode($releaseJson['stdout'], true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        handoffFail('release_metadata_malformed');
    }

    if (! is_array($releaseMetadata)) {
        handoffFail('release_metadata_malformed');
    }

    handoffAssertLiteral($releaseMetadata['product'] ?? null, 'oneQay', 'release_metadata_product_mismatch');
    handoffAssertLiteral($releaseMetadata['release_id'] ?? null, $releaseId, 'release_metadata_id_mismatch');
    handoffAssertLiteral($releaseMetadata['source_commit'] ?? null, $expectedSourceCommit, 'release_metadata_source_mismatch');
    handoffAssertLiteral($releaseMetadata['environment'] ?? null, 'DURABLE_STAGING', 'release_metadata_environment_mismatch');
    handoffAssertLiteral($releaseMetadata['required_runtime_class'] ?? null, 'durable-staging', 'release_metadata_runtime_mismatch');
    handoffAssertLiteral($releaseMetadata['migration_count'] ?? null, 27, 'release_metadata_migration_count_mismatch');
    handoffAssertLiteral($releaseMetadata['migration_execution_authorized'] ?? null, false, 'release_metadata_migration_authority_forbidden');
    handoffAssertLiteral($releaseMetadata['environment_deployment'] ?? null, 'NOT_PERFORMED', 'release_metadata_deployment_boundary_invalid');

    return [
        'schema_version' => 1,
        'handoff_state' => 'VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED',
        'product' => [
            'name' => 'oneQay',
            'repository' => 'labzefry/oneQay',
        ],
        'artifact' => [
            'release_id' => $releaseId,
            'source_commit' => $expectedSourceCommit,
            'artifact_filename' => $artifactFilename,
            'artifact_sha256' => $artifactSha256,
            'artifact_size_bytes' => $artifactSize,
            'manifest_sha256' => $manifestSha256,
        ],
        'runtime' => [
            'release_channel' => 'STAGING',
            'release_environment' => 'DURABLE_STAGING',
            'required_runtime_class' => 'durable-staging',
            'production' => false,
            'production_data_allowed' => false,
            'synthetic_fixture_runtime' => false,
            'environment_values_embedded' => false,
        ],
        'migration' => [
            'source_included' => true,
            'expected_count' => 27,
            'latest_migration' => '0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
            'execution_state' => 'NOT_PERFORMED',
            'execution_authorized' => false,
        ],
        'deployment' => [
            'execution_state' => 'NOT_PERFORMED',
            'archive_extraction_state' => 'NOT_PERFORMED',
            'runtime_configuration_mutation_state' => 'NOT_PERFORMED',
            'active_release_pointer_mutation_state' => 'NOT_PERFORMED',
            'authority_state' => 'NOT_GRANTED',
        ],
        'required_external_bindings' => [
            'ONEQAY_RUNTIME_CLASS',
            'ONEQAY_RUNNING_SOURCE_COMMIT',
            'ONEQAY_RUNNING_ARTIFACT_SHA256',
            'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
            'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
            'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
        ],
        'operational_boundary' => [
            'environment_creation' => 'NOT_PERFORMED',
            'environment_deployment' => 'NOT_PERFORMED',
            'migration27_execution' => 'NOT_PERFORMED',
            'permission_provisioning' => 'NONE',
            'feature_activation' => 'INACTIVE',
            'technical_preview_activation' => 'NOT_AUTHORIZED',
            'production_activation' => 'NOT_AUTHORIZED',
            'updater_activation' => 'INACTIVE',
            'target_selection' => 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET',
            'selected_target' => null,
            'producer_dispatch' => 'NOT_PERFORMED',
        ],
        'attribution' => 'Lab | zefry',
    ];
}

try {
    $manifestPath = $argv[1] ?? null;
    $artifactPath = $argv[2] ?? null;
    $expectedSourceCommit = $argv[3] ?? null;
    $outputPath = $argv[4] ?? null;

    if (
        ! is_string($manifestPath)
        || ! is_string($artifactPath)
        || ! is_string($expectedSourceCommit)
        || ! is_string($outputPath)
        || $outputPath === ''
    ) {
        fwrite(
            STDERR,
            "Usage: php tools/prepare-durable-staging-deployment-handoff.php <manifest.json> <artifact.tar.gz> <expected-source-sha> <output.json>\n",
        );
        exit(2);
    }

    if (is_link($outputPath)) {
        handoffFail('output_symlink_forbidden');
    }

    $handoff = handoffPrepare($manifestPath, $artifactPath, $expectedSourceCommit);
    $directory = dirname($outputPath);
    if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
        handoffFail('output_directory_unavailable');
    }

    $json = json_encode($handoff, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    $bytes = file_put_contents($outputPath, $json, LOCK_EX);
    if (! is_int($bytes) || $bytes !== strlen($json)) {
        @unlink($outputPath);
        handoffFail('handoff_write_failed');
    }

    @chmod($outputPath, 0600);

    fwrite(STDOUT, "Durable staging deployment handoff prepared without deployment mutation.\n");
    fwrite(STDOUT, "Author by Lab | zefry\n");
} catch (DurableStagingDeploymentHandoffException $exception) {
    fwrite(STDERR, "Durable staging deployment handoff failed: {$exception->getMessage()}\n");
    exit(1);
} catch (JsonException) {
    fwrite(STDERR, "Durable staging deployment handoff failed: handoff_encoding_failed\n");
    exit(1);
}

// Author by Lab | zefry
