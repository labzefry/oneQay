<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Application\Pos;

use App\Domain\Pos\SaleReceipt;

// Author by Lab | zefry
interface DurablePosSaleRepository
{
    public function complete(
        PosExecutionContext $context,
        SaleCommand $command,
        int $occurredAtUnix,
    ): SaleReceipt;

    public function voidCompletedSale(
        PosExecutionContext $context,
        SaleVoidCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): SaleVoidResult;
}

// Sprint48 JRN-005 Sprint46 compatibility preservation anchor.
