<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Activation;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;
use App\Application\SystemUpdate\SystemUpdateReleaseStore;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

// Author by Lab | zefry
final class FilesystemSystemUpdateReleaseStore implements SystemUpdateReleaseStore
{
    public function __construct(
        private readonly string $privateRoot,
        private readonly int $maximumFiles = 100000,
        private readonly int $maximumBytes = 536870912,
    ) {
        if ($maximumFiles < 1 || $maximumBytes < 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_staging_limits');
        }

        $this->ensureDirectory($this->privateRoot);
        $this->ensureDirectory($this->stagingRoot());
        $this->ensureDirectory($this->releasesRoot());
    }

    public function prepareStagingWorkspace(string $operationId): string
    {
        $this->assertOperationId($operationId);

        $workspace = $this->stagingRoot().'/'.$operationId;
        if (file_exists($workspace) || is_link($workspace)) {
            throw new SystemUpdateControlPlaneViolation('staging_workspace_exists');
        }

        $this->ensureDirectory($workspace);

        return $workspace;
    }

    public function commitStagedRelease(SystemUpdatePreparedRelease $release): void
    {
        $operationId = $release->operationId();
        $releaseId = $release->identity()->releaseId();
        $this->assertOperationId($operationId);
        $this->assertReleaseId($releaseId);

        $workspace = $this->stagingRoot().'/'.$operationId;
        $candidate = $workspace.'/'.$releaseId;
        $destination = $this->releasesRoot().'/'.$releaseId;

        if (! is_dir($candidate) || is_link($candidate)) {
            throw new SystemUpdateControlPlaneViolation('staged_release_missing');
        }

        if (file_exists($destination) || is_link($destination)) {
            throw new SystemUpdateControlPlaneViolation('release_already_staged');
        }

        $this->validateTree($candidate);
        $this->validateReleaseMetadata($candidate, $release);

        if (! rename($candidate, $destination)) {
            throw new SystemUpdateControlPlaneViolation('release_promotion_failed');
        }

        @chmod($destination, 0500);
        @rmdir($workspace);

        $this->assertReleaseReady($release->identity());
    }

    public function assertReleaseReady(SystemUpdateReleaseIdentity $release): void
    {
        $this->assertReleaseId($release->releaseId());
        $path = $this->releasesRoot().'/'.$release->releaseId();

        if (! is_dir($path) || is_link($path)) {
            throw new SystemUpdateControlPlaneViolation('release_not_ready');
        }

        foreach ([
            'RELEASE.json',
            'apps/web/vendor/autoload.php',
            'apps/web/bootstrap/app.php',
            'apps/web/public/index.php',
            'public-surface/index.php',
        ] as $required) {
            if (! is_file($path.'/'.$required) || is_link($path.'/'.$required)) {
                throw new SystemUpdateControlPlaneViolation('release_structure_invalid');
            }
        }

        $this->validateReleaseIdentityMetadata($path, $release);
    }

    public function releaseExists(string $releaseId): bool
    {
        $this->assertReleaseId($releaseId);
        $path = $this->releasesRoot().'/'.$releaseId;

        return is_dir($path) && ! is_link($path);
    }

    private function validateTree(string $candidate): void
    {
        $files = 0;
        $bytes = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($candidate, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $path = $entry->getPathname();
            $relative = substr($path, strlen($candidate) + 1);

            if (! is_string($relative) || $relative === '' || str_contains($relative, "\0")) {
                throw new SystemUpdateControlPlaneViolation('staging_path_invalid');
            }

            foreach (explode(DIRECTORY_SEPARATOR, $relative) as $segment) {
                if ($segment === '' || $segment === '.' || $segment === '..') {
                    throw new SystemUpdateControlPlaneViolation('staging_path_invalid');
                }

                if (in_array(strtolower($segment), ['.git', '.svn'], true)) {
                    throw new SystemUpdateControlPlaneViolation('staging_repository_metadata_forbidden');
                }
            }

            if (is_link($path)) {
                throw new SystemUpdateControlPlaneViolation('staging_link_forbidden');
            }

            if ($entry->isDir()) {
                continue;
            }

            if (! $entry->isFile()) {
                throw new SystemUpdateControlPlaneViolation('staging_special_file_forbidden');
            }

            $stat = lstat($path);
            if (! is_array($stat) || (int) ($stat['nlink'] ?? 1) > 1) {
                throw new SystemUpdateControlPlaneViolation('staging_hardlink_forbidden');
            }

            $basename = strtolower($entry->getBasename());
            $extension = strtolower($entry->getExtension());

            if (
                $basename === '.env'
                || str_starts_with($basename, '.env.')
                || in_array($basename, ['id_rsa', 'id_ed25519'], true)
                || in_array($extension, ['pem', 'key', 'p12', 'pfx'], true)
            ) {
                throw new SystemUpdateControlPlaneViolation('staging_secret_file_forbidden');
            }

            ++$files;
            $bytes += max(0, $entry->getSize());

            if ($files > $this->maximumFiles || $bytes > $this->maximumBytes) {
                throw new SystemUpdateControlPlaneViolation('staging_limits_exceeded');
            }
        }
    }

    private function validateReleaseMetadata(string $candidate, SystemUpdatePreparedRelease $release): void
    {
        $this->validateReleaseIdentityMetadata($candidate, $release->identity());

        $raw = file_get_contents($candidate.'/RELEASE.json');
        $metadata = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($metadata)) {
            throw new SystemUpdateControlPlaneViolation('release_metadata_invalid');
        }

        if (($metadata['migration_classification'] ?? null) !== SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION) {
            throw new SystemUpdateControlPlaneViolation('schema_change_not_supported');
        }
    }

    private function validateReleaseIdentityMetadata(string $candidate, SystemUpdateReleaseIdentity $release): void
    {
        $raw = @file_get_contents($candidate.'/RELEASE.json');
        $metadata = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($metadata)) {
            throw new SystemUpdateControlPlaneViolation('release_metadata_invalid');
        }

        if (($metadata['release_id'] ?? null) !== $release->releaseId()) {
            throw new SystemUpdateControlPlaneViolation('release_metadata_identity_mismatch');
        }

        if (($metadata['source_commit'] ?? null) !== $release->sourceCommit()) {
            throw new SystemUpdateControlPlaneViolation('release_metadata_source_mismatch');
        }

        if (($metadata['product'] ?? null) !== 'oneQay') {
            throw new SystemUpdateControlPlaneViolation('release_metadata_product_mismatch');
        }
    }

    private function ensureDirectory(string $path): void
    {
        if (is_link($path)) {
            throw new SystemUpdateControlPlaneViolation('private_release_root_symlink_forbidden');
        }

        if (! is_dir($path) && ! mkdir($path, 0700, true) && ! is_dir($path)) {
            throw new SystemUpdateControlPlaneViolation('private_release_root_unavailable');
        }

        if (is_link($path)) {
            throw new SystemUpdateControlPlaneViolation('private_release_root_symlink_forbidden');
        }
    }

    private function stagingRoot(): string
    {
        return rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/staging';
    }

    private function releasesRoot(): string
    {
        return rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/releases';
    }

    private function assertOperationId(string $operationId): void
    {
        if (preg_match('/\Aop-[0-9a-f]{16}\z/', $operationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_operation_id');
        }
    }

    private function assertReleaseId(string $releaseId): void
    {
        if (preg_match('/\Am75-preview-[0-9a-f]{12}\z/', $releaseId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_release_identity');
        }
    }
}
