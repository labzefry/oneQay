<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class PosExecutionContext
{
    private function __construct(
        private string $actorId,
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
    ) {
    }

    public static function fromVerified(?VerifiedOrganizationalContext $context): self
    {
        if ($context === null || $context->outletId() === null || $context->deviceId() === null) {
            throw new PosAccessViolation();
        }

        return new self(
            $context->identityId()->value(),
            $context->tenantId()->value(),
            $context->organizationId()->value(),
            $context->outletId()->value(),
            $context->deviceId()->value(),
        );
    }

    public function actorId(): string { return $this->actorId; }
    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
}
