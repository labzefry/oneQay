<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosShiftHistoryPerformanceWorkspaceRepository
{
    public function read(
        PosExecutionContext $context,
        ?string $selectedShiftId,
    ): PosShiftHistoryPerformanceWorkspaceSnapshot;
}
