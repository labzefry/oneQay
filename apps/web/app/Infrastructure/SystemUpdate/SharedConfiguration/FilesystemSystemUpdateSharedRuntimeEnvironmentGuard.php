<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedRuntimeEnvironmentGuard;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedRuntimeEnvironmentStatus;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use SplFileObject;

// Author by Lab | zefry
final class FilesystemSystemUpdateSharedRuntimeEnvironmentGuard implements SystemUpdateSharedRuntimeEnvironmentGuard
{
    private const MAXIMUM_ENV_BYTES = 65536;

    public function __construct(private readonly string $privateRoot)
    {
        if (trim($privateRoot) === '' || str_contains($privateRoot, "\0")) {
            throw new SystemUpdateControlPlaneViolation('shared_runtime_environment_root_invalid');
        }
    }

    public function assertReady(int $nowUnix): SystemUpdateSharedRuntimeEnvironmentStatus
    {
        $status = $this->inspect($nowUnix);

        if (! $status->isReady()) {
            throw new SystemUpdateControlPlaneViolation($status->safeCode());
        }

        return $status;
    }

    private function inspect(int $nowUnix): SystemUpdateSharedRuntimeEnvironmentStatus
    {
        $privateRoot = rtrim($this->privateRoot, DIRECTORY_SEPARATOR);
        $sharedRoot = $privateRoot.'/shared';
        $runtimeRoot = $sharedRoot.'/runtime';
        $environmentFile = $runtimeRoot.'/.env';

        foreach ([$sharedRoot, $runtimeRoot, $environmentFile] as $path) {
            if (is_link($path)) {
                return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                    'shared_runtime_environment_symlink_forbidden',
                    false,
                    $nowUnix,
                );
            }
        }

        if (! is_dir($sharedRoot) || ! is_dir($runtimeRoot) || ! is_file($environmentFile)) {
            return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                'shared_runtime_environment_missing',
                false,
                $nowUnix,
            );
        }

        if (! $this->privatePermissions($sharedRoot, true)
            || ! $this->privatePermissions($runtimeRoot, true)
            || ! $this->privatePermissions($environmentFile, false)
        ) {
            return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                'shared_runtime_environment_permissions_invalid',
                false,
                $nowUnix,
            );
        }

        if (! is_readable($environmentFile)) {
            return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                'shared_runtime_environment_unreadable',
                false,
                $nowUnix,
            );
        }

        $size = filesize($environmentFile);
        if (! is_int($size) || $size < 1 || $size > self::MAXIMUM_ENV_BYTES) {
            return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                'shared_runtime_environment_size_invalid',
                false,
                $nowUnix,
            );
        }

        $appKeyPresent = $this->appKeyPresent($environmentFile);
        if (! $appKeyPresent) {
            return SystemUpdateSharedRuntimeEnvironmentStatus::blocked(
                'shared_runtime_app_key_missing',
                false,
                $nowUnix,
            );
        }

        return SystemUpdateSharedRuntimeEnvironmentStatus::ready($nowUnix);
    }

    private function privatePermissions(string $path, bool $directory): bool
    {
        $permissions = fileperms($path);
        if (! is_int($permissions)) {
            return false;
        }

        $mode = $permissions & 0777;
        if (($mode & 0077) !== 0) {
            return false;
        }

        return $directory
            ? ($mode & 0500) === 0500
            : ($mode & 0400) === 0400;
    }

    private function appKeyPresent(string $environmentFile): bool
    {
        try {
            $file = new SplFileObject($environmentFile, 'rb');
        } catch (\RuntimeException) {
            return false;
        }

        while (! $file->eof()) {
            $line = $file->fgets();
            if (! is_string($line)) {
                continue;
            }

            $candidate = trim($line);
            if ($candidate === '' || str_starts_with($candidate, '#')) {
                continue;
            }

            if (str_starts_with($candidate, 'export ')) {
                $candidate = ltrim(substr($candidate, 7));
            }

            if (preg_match('/\AAPP_KEY\s*=\s*(.*)\z/s', $candidate, $matches) !== 1) {
                continue;
            }

            $value = trim((string) ($matches[1] ?? ''));
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $present = trim($value) !== '' && ! str_contains($value, 'REPLACE_WITH_');
            unset($value, $matches, $candidate, $line);

            return $present;
        }

        return false;
    }
}
