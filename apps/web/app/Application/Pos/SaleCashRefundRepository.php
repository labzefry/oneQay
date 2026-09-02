<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface SaleCashRefundRepository
{
    public function record(
        PosExecutionContext $context,
        SaleCashRefundCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): SaleCashRefundResult;
}
