<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosShiftStartWorkspaceRepository
{
    public function snapshot(PosExecutionContext $context): PosShiftStartWorkspaceSnapshot;
}
