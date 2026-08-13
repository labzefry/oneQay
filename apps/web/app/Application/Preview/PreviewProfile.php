<?php

declare(strict_types=1);

namespace App\Application\Preview;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PreviewProfile
{
    public function __construct(
        private string $principalId,
        private string $label,
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
    ) {
        if (! str_starts_with($this->principalId, 'synthetic-principal-')) {
            throw new InvalidArgumentException('Technical Preview accepts synthetic principals only.');
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException('Technical Preview profile label is required.');
        }
    }

    public function principalId(): string { return $this->principalId; }
    public function label(): string { return trim($this->label); }
    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
}
