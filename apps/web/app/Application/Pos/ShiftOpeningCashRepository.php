<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface ShiftOpeningCashRepository
{
    public function record(
        PosExecutionContext $context,
        ShiftOpeningCashCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): ShiftOpeningCashResult;
}
