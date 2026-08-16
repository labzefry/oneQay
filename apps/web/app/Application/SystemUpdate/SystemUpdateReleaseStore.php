<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateReleaseStore
{
    /**
     * Creates an isolated private staging directory for a future trusted extractor.
     *
     * The returned path is infrastructure-private and must never be surfaced in HTTP responses,
     * audit records, or user-visible diagnostics.
     */
    public function prepareStagingWorkspace(string $operationId): string;

    public function commitStagedRelease(SystemUpdatePreparedRelease $release): void;

    public function assertReleaseReady(SystemUpdateReleaseIdentity $release): void;

    public function releaseExists(string $releaseId): bool;
}
