<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface CashVarianceExplanationRepository
{
    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): CashVarianceExplanationResult;
}
