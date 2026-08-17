<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class DurablePolicyMutation
{
    private function __construct(
        private PolicyMutationId $mutationId,
        private PolicyMutationOperation $operation,
        private PolicyAssignmentScope $scope,
        private RoleIdentifier $role,
        private ?PermissionIdentifier $permission,
        private ?PlatformIdentityId $targetIdentity,
    ) {}

    public static function roleCreate(PolicyMutationId $id, VerifiedOrganizationalContext $actor, RoleIdentifier $role): self
    {
        return new self($id, PolicyMutationOperation::fromString(PolicyMutationOperation::ROLE_CREATE), PolicyAssignmentScope::fromVerifiedContext($actor, 'tenant'), $role, null, null);
    }

    public static function permissionGrant(PolicyMutationId $id, VerifiedOrganizationalContext $actor, RoleIdentifier $role, PermissionIdentifier $permission): self
    {
        return new self($id, PolicyMutationOperation::fromString(PolicyMutationOperation::PERMISSION_GRANT), PolicyAssignmentScope::fromVerifiedContext($actor, 'tenant'), $role, $permission, null);
    }

    public static function permissionRevoke(PolicyMutationId $id, VerifiedOrganizationalContext $actor, RoleIdentifier $role, PermissionIdentifier $permission): self
    {
        return new self($id, PolicyMutationOperation::fromString(PolicyMutationOperation::PERMISSION_REVOKE), PolicyAssignmentScope::fromVerifiedContext($actor, 'tenant'), $role, $permission, null);
    }

    public static function roleAssignment(PolicyMutationId $id, VerifiedOrganizationalContext $actor, string $operation, PlatformIdentityId $targetIdentity, RoleIdentifier $role): self
    {
        $canonicalOperation = PolicyMutationOperation::fromString($operation);
        if (! $canonicalOperation->isAssignmentMutation()) {
            throw new InvalidArgumentException('Role assignment operation is invalid.');
        }

        return new self($id, $canonicalOperation, PolicyAssignmentScope::fromVerifiedContext($actor, $canonicalOperation->scopeType()), $role, null, $targetIdentity);
    }

    public function mutationId(): PolicyMutationId { return $this->mutationId; }
    public function operation(): PolicyMutationOperation { return $this->operation; }
    public function scope(): PolicyAssignmentScope { return $this->scope; }
    public function role(): RoleIdentifier { return $this->role; }
    public function permission(): ?PermissionIdentifier { return $this->permission; }
    public function targetIdentity(): ?PlatformIdentityId { return $this->targetIdentity; }

    public function fingerprint(VerifiedOrganizationalContext $actor): string
    {
        $payload = [
            'tenant_id' => $actor->tenantId()->value(),
            'actor_identity_id' => $actor->identityId()->value(),
            'operation' => $this->operation->value(),
            'scope_type' => $this->scope->type(),
            'organization_id' => $this->scope->organizationId()?->value(),
            'outlet_id' => $this->scope->outletId()?->value(),
            'device_id' => $this->scope->deviceId()?->value(),
            'target_identity_id' => $this->targetIdentity?->value(),
            'role_id' => $this->role->value(),
            'permission_id' => $this->permission?->value(),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return hash('sha256', $json);
    }
}
