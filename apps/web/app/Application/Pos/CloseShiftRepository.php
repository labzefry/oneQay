<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface CloseShiftRepository
{
    public function close(
        PosExecutionContext $context,
        CloseShiftCommand $command,
        string $correlationId,
        int $closedAtUnix,
    ): CloseShiftResult;
}
