<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use App\Domain\Pos\TenderCategory;

// Author by Lab | zefry
final readonly class SaleCashRefundResult
{
    public function __construct(
        private string $refundId,
        private string $saleId,
        private string $voidId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private Money $refundedAmount,
        private TenderCategory $tenderCategory,
        private string $evidenceMode,
        private string $correlationId,
        private int $refundedAtUnix,
    ) {}

    public function refundId(): string { return $this->refundId; }
    public function saleId(): string { return $this->saleId; }
    public function voidId(): string { return $this->voidId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function refundedAmount(): Money { return $this->refundedAmount; }
    public function tenderCategory(): TenderCategory { return $this->tenderCategory; }
    public function evidenceMode(): string { return $this->evidenceMode; }
    public function correlationId(): string { return $this->correlationId; }
    public function refundedAtUnix(): int { return $this->refundedAtUnix; }
}
