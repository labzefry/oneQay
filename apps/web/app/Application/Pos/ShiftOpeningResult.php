<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class ShiftOpeningResult
{
    public function __construct(
        private string $shiftId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private string $deviceId,
        private string $correlationId,
        private int $openedAtUnix,
        private bool $active,
    ) {}

    public function shiftId(): string { return $this->shiftId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function correlationId(): string { return $this->correlationId; }
    public function openedAtUnix(): int { return $this->openedAtUnix; }
    public function active(): bool { return $this->active; }
}
