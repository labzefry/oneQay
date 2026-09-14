<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosCashVarianceReconciliationWorkspaceRepository
{
    public function read(
        PosExecutionContext $context,
        ?string $selectedClosingCashEvidenceId,
    ): PosCashVarianceReconciliationWorkspaceData;

    public function resolveVariance(
        PosExecutionContext $context,
        string $closingCashEvidenceId,
    ): CashVarianceResult;
}
