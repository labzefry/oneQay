<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PolicyAdministrationDeliveryCommand
{
    private const KEYS = ['mutation_id', 'operation', 'role', 'permission', 'target_identity'];

    private function __construct(
        private PolicyMutationId $mutationId,
        private PolicyMutationOperation $operation,
        private RoleIdentifier $role,
        private ?PermissionIdentifier $permission,
        private ?PlatformIdentityId $targetIdentity,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        foreach (array_keys($payload) as $key) {
            if (! is_string($key) || ! in_array($key, self::KEYS, true)) {
                throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
            }
        }

        $mutationId = self::requiredString($payload, 'mutation_id');
        $operationValue = self::requiredString($payload, 'operation');
        $role = self::requiredString($payload, 'role');

        $operation = PolicyMutationOperation::fromString($operationValue);
        $permission = self::optionalString($payload, 'permission');
        $targetIdentity = self::optionalString($payload, 'target_identity');

        if ($operationValue === PolicyMutationOperation::ROLE_CREATE) {
            if ($permission !== null || $targetIdentity !== null) {
                throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
            }
        } elseif ($operation->isPermissionMutation()) {
            if ($permission === null || $targetIdentity !== null) {
                throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
            }
        } elseif ($operation->isAssignmentMutation()) {
            if ($permission !== null || $targetIdentity === null) {
                throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
            }
        } else {
            throw new InvalidArgumentException('Policy administration delivery operation is invalid.');
        }

        return new self(
            PolicyMutationId::fromString($mutationId),
            $operation,
            RoleIdentifier::fromString($role),
            $permission === null ? null : PermissionIdentifier::fromString($permission),
            $targetIdentity === null ? null : PlatformIdentityId::fromString($targetIdentity),
        );
    }

    public function toMutation(VerifiedOrganizationalContext $actor): DurablePolicyMutation
    {
        $operation = $this->operation->value();

        if ($operation === PolicyMutationOperation::ROLE_CREATE) {
            return DurablePolicyMutation::roleCreate($this->mutationId, $actor, $this->role);
        }

        if ($operation === PolicyMutationOperation::PERMISSION_GRANT) {
            return DurablePolicyMutation::permissionGrant(
                $this->mutationId,
                $actor,
                $this->role,
                $this->requiredPermission(),
            );
        }

        if ($operation === PolicyMutationOperation::PERMISSION_REVOKE) {
            return DurablePolicyMutation::permissionRevoke(
                $this->mutationId,
                $actor,
                $this->role,
                $this->requiredPermission(),
            );
        }

        return DurablePolicyMutation::roleAssignment(
            $this->mutationId,
            $actor,
            $operation,
            $this->requiredTargetIdentity(),
            $this->role,
        );
    }

    /** @param array<string, mixed> $payload */
    private static function requiredString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
        }
        return trim($value);
    }

    /** @param array<string, mixed> $payload */
    private static function optionalString(array $payload, string $key): ?string
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }
        $value = $payload[$key];
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
        }
        return trim($value);
    }

    private function requiredPermission(): PermissionIdentifier
    {
        return $this->permission ?? throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
    }

    private function requiredTargetIdentity(): PlatformIdentityId
    {
        return $this->targetIdentity ?? throw new InvalidArgumentException('Policy administration delivery payload is invalid.');
    }
}
