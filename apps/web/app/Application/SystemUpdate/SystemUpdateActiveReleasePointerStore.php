<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateActiveReleasePointerStore
{
    public function current(): ?SystemUpdateActiveReleasePointer;

    public function initialize(SystemUpdateReleaseIdentity $active, int $nowUnix): SystemUpdateActiveReleasePointer;

    public function switchTo(
        SystemUpdateReleaseIdentity $next,
        SystemUpdateReleaseIdentity $expectedCurrent,
        int $nowUnix,
    ): SystemUpdateActiveReleasePointer;

    public function restorePrevious(
        SystemUpdateReleaseIdentity $previousStable,
        SystemUpdateReleaseIdentity $expectedFailedCurrent,
        int $nowUnix,
    ): SystemUpdateActiveReleasePointer;
}
