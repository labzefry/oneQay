<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface ShiftClosingCashRepository
{
    public function record(
        PosExecutionContext $context,
        ShiftClosingCashCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): ShiftClosingCashResult;
}
