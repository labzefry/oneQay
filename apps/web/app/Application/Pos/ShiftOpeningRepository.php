<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface ShiftOpeningRepository
{
    public function open(
        PosExecutionContext $context,
        ShiftOpeningCommand $command,
        string $correlationId,
        int $openedAtUnix,
    ): ShiftOpeningResult;
}
