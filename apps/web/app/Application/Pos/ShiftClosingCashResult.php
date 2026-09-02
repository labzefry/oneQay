<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;

// Author by Lab | zefry
final readonly class ShiftClosingCashResult
{
    public function __construct(
        private string $evidenceId,
        private string $openingCashEvidenceId,
        private string $shiftId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private string $deviceId,
        private Money $closingCash,
        private string $evidenceMode,
        private string $correlationId,
        private int $recordedAtUnix,
    ) {}

    public function evidenceId(): string { return $this->evidenceId; }
    public function openingCashEvidenceId(): string { return $this->openingCashEvidenceId; }
    public function shiftId(): string { return $this->shiftId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function closingCash(): Money { return $this->closingCash; }
    public function evidenceMode(): string { return $this->evidenceMode; }
    public function correlationId(): string { return $this->correlationId; }
    public function recordedAtUnix(): int { return $this->recordedAtUnix; }
}
