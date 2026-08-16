<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateOperationJournal
{
    public function begin(
        SystemUpdatePreparedRelease $release,
        string $actorIdentityRef,
        SystemUpdateReleaseIdentity $previousStable,
        int $nowUnix,
    ): void;

    public function transition(
        string $operationId,
        SystemUpdateOperationState $from,
        SystemUpdateOperationState $to,
        int $nowUnix,
        ?string $safeFailureCode = null,
    ): void;

    public function recordHealth(
        string $operationId,
        SystemUpdateReleaseIdentity $release,
        SystemUpdateHealthResult $result,
        int $nowUnix,
    ): void;

    public function currentState(string $operationId): SystemUpdateOperationState;
}
