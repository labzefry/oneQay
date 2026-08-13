<?php

declare(strict_types=1);

namespace App\Domain\Pos;

// Author by Lab | zefry
final readonly class SaleReceipt
{
    /**
     * @param list<SaleLineResult> $lines
     */
    public function __construct(
        private string $saleId,
        private string $operationId,
        private string $tenantId,
        private string $actorId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private array $lines,
        private Money $total,
        private TenderCategory $tenderCategory,
        private string $evidenceMode,
        private Money $appliedAmount,
        private Money $changeAmount,
        private string $correlationId,
    ) {
    }

    public function saleId(): string { return $this->saleId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function actorId(): string { return $this->actorId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }

    /** @return list<SaleLineResult> */
    public function lines(): array { return $this->lines; }

    public function total(): Money { return $this->total; }
    public function tenderCategory(): TenderCategory { return $this->tenderCategory; }
    public function evidenceMode(): string { return $this->evidenceMode; }
    public function appliedAmount(): Money { return $this->appliedAmount; }
    public function changeAmount(): Money { return $this->changeAmount; }
    public function correlationId(): string { return $this->correlationId; }
}
