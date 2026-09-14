<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosShiftStartWorkspaceSnapshot
{
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private ?string $activeShiftId,
        private ?int $openedAtUnix,
        private ?string $openingCashEvidenceId,
        private ?Money $openingCash,
        private ?string $openingCashEvidenceMode,
        private ?int $openingCashRecordedAtUnix,
    ) {
        if (($this->activeShiftId === null) !== ($this->openedAtUnix === null)) {
            throw new InvalidArgumentException('Active shift identity and opened timestamp must have matching presence.');
        }

        $evidenceParts = [
            $this->openingCashEvidenceId,
            $this->openingCash,
            $this->openingCashEvidenceMode,
            $this->openingCashRecordedAtUnix,
        ];
        $presentEvidenceParts = count(array_filter($evidenceParts, static fn (mixed $value): bool => $value !== null));

        if ($presentEvidenceParts !== 0 && $presentEvidenceParts !== count($evidenceParts)) {
            throw new InvalidArgumentException('Opening cash evidence must be complete when present.');
        }

        if ($presentEvidenceParts > 0 && $this->activeShiftId === null) {
            throw new InvalidArgumentException('Opening cash evidence requires an active shift.');
        }
    }

    public function tenantId(): string
    {
        return $this->tenantId;
    }

    public function organizationId(): string
    {
        return $this->organizationId;
    }

    public function outletId(): string
    {
        return $this->outletId;
    }

    public function deviceId(): string
    {
        return $this->deviceId;
    }

    public function activeShiftId(): ?string
    {
        return $this->activeShiftId;
    }

    public function openedAtUnix(): ?int
    {
        return $this->openedAtUnix;
    }

    public function openingCashEvidenceId(): ?string
    {
        return $this->openingCashEvidenceId;
    }

    public function openingCash(): ?Money
    {
        return $this->openingCash;
    }

    public function openingCashEvidenceMode(): ?string
    {
        return $this->openingCashEvidenceMode;
    }

    public function openingCashRecordedAtUnix(): ?int
    {
        return $this->openingCashRecordedAtUnix;
    }

    public function readyForCashier(): bool
    {
        return $this->activeShiftId !== null && $this->openingCashEvidenceId !== null;
    }
}
