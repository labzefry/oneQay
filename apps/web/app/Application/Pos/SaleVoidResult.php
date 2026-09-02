<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use App\Domain\Pos\TenderCategory;

// Author by Lab | zefry
final readonly class SaleVoidResult
{
    public function __construct(
        private string $voidId,
        private string $saleId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private Money $reversedAmount,
        private TenderCategory $tenderCategory,
        private string $evidenceMode,
        private string $correlationId,
        private int $voidedAtUnix,
    ) {}

    public function voidId(): string { return $this->voidId; }
    public function saleId(): string { return $this->saleId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function reversedAmount(): Money { return $this->reversedAmount; }
    public function tenderCategory(): TenderCategory { return $this->tenderCategory; }
    public function evidenceMode(): string { return $this->evidenceMode; }
    public function correlationId(): string { return $this->correlationId; }
    public function voidedAtUnix(): int { return $this->voidedAtUnix; }
}
