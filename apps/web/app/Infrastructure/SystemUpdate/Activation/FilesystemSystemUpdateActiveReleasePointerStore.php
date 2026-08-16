<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Activation;

use App\Application\SystemUpdate\SystemUpdateActiveReleasePointer;
use App\Application\SystemUpdate\SystemUpdateActiveReleasePointerStore;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;

// Author by Lab | zefry
final class FilesystemSystemUpdateActiveReleasePointerStore implements SystemUpdateActiveReleasePointerStore
{
    public function __construct(
        private readonly string $privateRoot,
        private readonly SystemUpdateAtomicJsonFile $json,
    ) {
    }

    public function current(): ?SystemUpdateActiveReleasePointer
    {
        $payload = $this->json->read($this->pointerPath());

        if ($payload === null) {
            return null;
        }

        if (($payload['pointer_version'] ?? null) !== 1) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_version_unsupported');
        }

        $active = $this->identityFromPayload($payload['active'] ?? null);
        $previous = isset($payload['previous_stable'])
            ? $this->identityFromPayload($payload['previous_stable'])
            : null;
        $activatedAt = $payload['activated_at_unix'] ?? null;

        if (! is_int($activatedAt)) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_malformed');
        }

        $this->assertReleaseDirectory($active->releaseId());
        if ($previous !== null) {
            $this->assertReleaseDirectory($previous->releaseId());
        }

        return new SystemUpdateActiveReleasePointer($active, $previous, $activatedAt);
    }

    public function initialize(SystemUpdateReleaseIdentity $active, int $nowUnix): SystemUpdateActiveReleasePointer
    {
        if ($this->current() !== null) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_already_initialized');
        }

        $this->assertReleaseDirectory($active->releaseId());

        $pointer = new SystemUpdateActiveReleasePointer($active, null, $nowUnix);
        $this->json->write($this->pointerPath(), $pointer->toSafeArray());

        return $pointer;
    }

    public function switchTo(
        SystemUpdateReleaseIdentity $next,
        SystemUpdateReleaseIdentity $expectedCurrent,
        int $nowUnix,
    ): SystemUpdateActiveReleasePointer {
        $current = $this->current();

        if ($current === null || ! $current->active()->equals($expectedCurrent)) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_compare_failed');
        }

        $this->assertReleaseDirectory($next->releaseId());

        $pointer = new SystemUpdateActiveReleasePointer($next, $current->active(), $nowUnix);
        $this->json->write($this->pointerPath(), $pointer->toSafeArray());

        return $pointer;
    }

    public function restorePrevious(
        SystemUpdateReleaseIdentity $previousStable,
        SystemUpdateReleaseIdentity $expectedFailedCurrent,
        int $nowUnix,
    ): SystemUpdateActiveReleasePointer {
        $current = $this->current();

        if (
            $current === null
            || ! $current->active()->equals($expectedFailedCurrent)
            || $current->previousStable() === null
            || ! $current->previousStable()->equals($previousStable)
        ) {
            throw new SystemUpdateControlPlaneViolation('rollback_pointer_compare_failed');
        }

        $this->assertReleaseDirectory($previousStable->releaseId());

        $pointer = new SystemUpdateActiveReleasePointer($previousStable, null, $nowUnix);
        $this->json->write($this->pointerPath(), $pointer->toSafeArray());

        return $pointer;
    }

    private function identityFromPayload(mixed $payload): SystemUpdateReleaseIdentity
    {
        if (! is_array($payload)) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_malformed');
        }

        $releaseId = $payload['release_id'] ?? null;
        $sourceCommit = $payload['source_commit'] ?? null;
        $artifactSha256 = $payload['artifact_sha256'] ?? null;

        if (! is_string($releaseId) || ! is_string($sourceCommit) || ! is_string($artifactSha256)) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_malformed');
        }

        return new SystemUpdateReleaseIdentity($releaseId, $sourceCommit, $artifactSha256);
    }

    private function assertReleaseDirectory(string $releaseId): void
    {
        $path = rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/releases/'.$releaseId;
        if (! is_dir($path)) {
            throw new SystemUpdateControlPlaneViolation('active_pointer_release_missing');
        }
    }

    private function pointerPath(): string
    {
        return rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/current-release.json';
    }
}
