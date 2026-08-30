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
}
