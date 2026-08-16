<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Activation;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use JsonException;

// Author by Lab | zefry
final class SystemUpdateAtomicJsonFile
{
    /** @return array<string, mixed>|null */
    public function read(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if (! is_string($raw) || $raw === '') {
            throw new SystemUpdateControlPlaneViolation('deployment_state_unreadable');
        }

        try {
            $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new SystemUpdateControlPlaneViolation('deployment_state_malformed');
        }

        if (! is_array($decoded)) {
            throw new SystemUpdateControlPlaneViolation('deployment_state_malformed');
        }

        return $decoded;
    }

    /** @param array<string, mixed> $payload */
    public function write(string $path, array $payload): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new SystemUpdateControlPlaneViolation('deployment_state_directory_unavailable');
        }

        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n";
            $suffix = bin2hex(random_bytes(8));
        } catch (JsonException) {
            throw new SystemUpdateControlPlaneViolation('deployment_state_encoding_failed');
        }

        $temporary = $directory.'/.oneqay-tmp-'.$suffix;
        $bytes = file_put_contents($temporary, $json, LOCK_EX);

        if (! is_int($bytes) || $bytes !== strlen($json)) {
            @unlink($temporary);
            throw new SystemUpdateControlPlaneViolation('deployment_state_write_failed');
        }

        @chmod($temporary, 0600);

        if (! rename($temporary, $path)) {
            @unlink($temporary);
            throw new SystemUpdateControlPlaneViolation('deployment_state_commit_failed');
        }
    }
}
