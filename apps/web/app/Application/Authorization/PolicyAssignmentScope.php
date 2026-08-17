<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Device\DeviceId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PolicyAssignmentScope
{
    private function __construct(private string $type, private TenantId $tenantId, private ?OrganizationId $organizationId, private ?OutletId $outletId, private ?DeviceId $deviceId) {}

    public static function fromVerifiedContext(VerifiedOrganizationalContext $context, string $type): self
    {
        return match ($type) {
            'tenant' => new self('tenant', $context->tenantId(), null, null, null),
            'organization' => new self('organization', $context->tenantId(), $context->organizationId(), null, null),
            'outlet' => $context->outletId() !== null ? new self('outlet', $context->tenantId(), $context->organizationId(), $context->outletId(), null) : throw new InvalidArgumentException('Verified outlet context is required.'),
            'device' => $context->outletId() !== null && $context->deviceId() !== null ? new self('device', $context->tenantId(), $context->organizationId(), $context->outletId(), $context->deviceId()) : throw new InvalidArgumentException('Verified device context is required.'),
            default => throw new InvalidArgumentException('Policy assignment scope is invalid.'),
        };
    }

    public function type(): string { return $this->type; }
    public function tenantId(): TenantId { return $this->tenantId; }
    public function organizationId(): ?OrganizationId { return $this->organizationId; }
    public function outletId(): ?OutletId { return $this->outletId; }
    public function deviceId(): ?DeviceId { return $this->deviceId; }

    public function matchesActor(VerifiedOrganizationalContext $context): bool
    {
        if ($this->tenantId->value() !== $context->tenantId()->value()) { return false; }
        if ($this->organizationId !== null && $this->organizationId->value() !== $context->organizationId()->value()) { return false; }
        if ($this->outletId !== null && $this->outletId->value() !== $context->outletId()?->value()) { return false; }
        return $this->deviceId === null || $this->deviceId->value() === $context->deviceId()?->value();
    }
}
