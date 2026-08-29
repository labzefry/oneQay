<?php

declare(strict_types=1);

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
