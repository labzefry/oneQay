<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use RuntimeException;

// Author by Lab | zefry
final class FilesystemFinalShiftCloseRuntimeBindingManifestWriter implements FinalShiftCloseRuntimeBindingManifestWriter
{
    public function __construct(private readonly string $targetPath) {}

    public function write(FinalShiftCloseRuntimeBindingManifest $manifest): void
    {
        $target = $this->qualifiedTargetPath();
        $parent = dirname($target);
        $this->assertPrivateDirectory($parent);

        clearstatcache(true, $target);
        if (is_link($target)) {
            throw new RuntimeException('Runtime binding manifest target symlink is forbidden.');
        }
        if (file_exists($target)) {
            if (! is_file($target)) {
                throw new RuntimeException('Runtime binding manifest target is not a regular file.');
            }
            $this->assertPrivateFilePermissions($target);
        }

        $lockPath = $target.'.lock';
        clearstatcache(true, $lockPath);
        if (is_link($lockPath)) {
            throw new RuntimeException('Runtime binding manifest lock symlink is forbidden.');
        }

        $lock = fopen($lockPath, 'c');
        if ($lock === false) {
            throw new RuntimeException('Runtime binding manifest lock cannot be opened.');
        }

        $temporary = null;
        try {
            if (! chmod($lockPath, 0600)) {
                throw new RuntimeException('Runtime binding manifest lock permissions cannot be hardened.');
            }
            $this->assertPrivateFilePermissions($lockPath);

            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException('Runtime binding manifest lock cannot be acquired.');
            }

            clearstatcache(true, $target);
            if (is_link($target)) {
                throw new RuntimeException('Runtime binding manifest target symlink is forbidden.');
            }

            $json = $manifest->toCanonicalJson();
            $temporary = $target.'.tmp.'.bin2hex(random_bytes(16));
            $stream = fopen($temporary, 'x');
            if ($stream === false) {
                throw new RuntimeException('Runtime binding manifest temporary file cannot be created.');
            }

            try {
                if (! chmod($temporary, 0600)) {
                    throw new RuntimeException('Runtime binding manifest temporary permissions cannot be hardened.');
                }
                $written = fwrite($stream, $json);
                if ($written !== strlen($json)) {
                    throw new RuntimeException('Runtime binding manifest temporary write is incomplete.');
                }
                if (! fflush($stream)) {
                    throw new RuntimeException('Runtime binding manifest temporary flush failed.');
                }
                if (function_exists('fsync') && ! fsync($stream)) {
                    throw new RuntimeException('Runtime binding manifest temporary fsync failed.');
                }
            } finally {
                fclose($stream);
            }

            $this->assertPrivateFilePermissions($temporary);
            if (! rename($temporary, $target)) {
                throw new RuntimeException('Runtime binding manifest atomic replacement failed.');
            }
            $temporary = null;

            if (! chmod($target, 0600)) {
                throw new RuntimeException('Runtime binding manifest final permissions cannot be hardened.');
            }

            clearstatcache(true, $target);
            if (! is_file($target) || is_link($target) || ! is_readable($target)) {
                throw new RuntimeException('Runtime binding manifest final file is invalid.');
            }
            $this->assertPrivateFilePermissions($target);

            $writtenJson = file_get_contents($target);
            if ($writtenJson === false || ! hash_equals(hash('sha256', $json), hash('sha256', $writtenJson))) {
                throw new RuntimeException('Runtime binding manifest post-write verification failed.');
            }
        } finally {
            if (is_resource($lock)) {
                @flock($lock, LOCK_UN);
                fclose($lock);
            }
            if (is_string($temporary) && file_exists($temporary) && ! is_link($temporary)) {
                @unlink($temporary);
            }
        }
    }

    private function qualifiedTargetPath(): string
    {
        $path = $this->targetPath;
        if ($path === '' || ! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Runtime binding manifest target path is invalid.');
        }

        return $path;
    }

    private function assertPrivateDirectory(string $path): void
    {
        clearstatcache(true, $path);
        if (! is_dir($path) || is_link($path) || ! is_writable($path)) {
            throw new RuntimeException('Runtime binding manifest directory is unavailable.');
        }

        $permissions = fileperms($path);
        if (! is_int($permissions) || (($permissions & 0077) !== 0)) {
            throw new RuntimeException('Runtime binding manifest directory permissions are invalid.');
        }
    }

    private function assertPrivateFilePermissions(string $path): void
    {
        clearstatcache(true, $path);
        $permissions = fileperms($path);
        if (! is_int($permissions) || (($permissions & 0077) !== 0)) {
            throw new RuntimeException('Runtime binding manifest file permissions are invalid.');
        }
    }
}
