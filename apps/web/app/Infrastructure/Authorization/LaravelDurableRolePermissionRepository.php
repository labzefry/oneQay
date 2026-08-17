<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelDurableRolePermissionRepository implements DurableRolePermissionRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {
    }

    public function allows(
        VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): bool {
        $this->assertRuntimeAllowed();

        $tenant = $context->tenantId()->value();
        $identity = $context->identityId()->value();
        $organization = $context->organizationId()->value();

        try {
            $roles = [];

            $this->appendRoles(
                $roles,
                $this->connection->table('oneqay_tenant_role_assignments')
                    ->where('tenant_id', $tenant)
                    ->where('identity_id', $identity)
                    ->pluck('role_id')
                    ->all(),
            );

            $this->appendRoles(
                $roles,
                $this->connection->table('oneqay_organization_role_assignments')
                    ->where('tenant_id', $tenant)
                    ->where('identity_id', $identity)
                    ->where('organization_id', $organization)
                    ->pluck('role_id')
                    ->all(),
            );

            if ($context->outletId() !== null) {
                $outlet = $context->outletId()->value();
                $this->appendRoles(
                    $roles,
                    $this->connection->table('oneqay_outlet_role_assignments')
                        ->where('tenant_id', $tenant)
                        ->where('identity_id', $identity)
                        ->where('organization_id', $organization)
                        ->where('outlet_id', $outlet)
                        ->pluck('role_id')
                        ->all(),
                );

                if ($context->deviceId() !== null) {
                    $this->appendRoles(
                        $roles,
                        $this->connection->table('oneqay_device_role_assignments')
                            ->where('tenant_id', $tenant)
                            ->where('identity_id', $identity)
                            ->where('organization_id', $organization)
                            ->where('outlet_id', $outlet)
                            ->where('device_id', $context->deviceId()->value())
                            ->pluck('role_id')
                            ->all(),
                    );
                }
            }

            $roles = array_values(array_unique($roles));
            if ($roles === []) {
                return false;
            }

            return $this->connection->table('oneqay_role_permissions')
                ->where('tenant_id', $tenant)
                ->whereIn('role_id', $roles)
                ->where('permission_id', $permission->value())
                ->exists();
        } catch (DurableAuthorizationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurableAuthorizationViolation(
                DurableAuthorizationViolation::STORAGE_FAILURE,
                'Durable authorization storage operation failed.',
            );
        }
    }

    /**
     * @param array<int, string> $roles
     * @param array<int, mixed> $rawRoles
     */
    private function appendRoles(array &$roles, array $rawRoles): void
    {
        foreach ($rawRoles as $rawRole) {
            if (! is_string($rawRole) || $rawRole === '') {
                $this->invalidPolicyData();
            }

            try {
                $role = RoleIdentifier::fromString($rawRole);
            } catch (InvalidArgumentException) {
                $this->invalidPolicyData();
            }

            if (! hash_equals($rawRole, $role->value())) {
                $this->invalidPolicyData();
            }

            $roles[] = $role->value();
        }
    }

    private function invalidPolicyData(): never
    {
        throw new DurableAuthorizationViolation(
            DurableAuthorizationViolation::POLICY_DATA_INVALID,
            'Durable authorization policy data is invalid.',
        );
    }

    private function assertRuntimeAllowed(): void
    {
        if (! $this->enabled) {
            throw new DurableAuthorizationViolation(
                DurableAuthorizationViolation::PERSISTENCE_DISABLED,
                'Durable authorization persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurableAuthorizationViolation(
                DurableAuthorizationViolation::RUNTIME_DENIED,
                'Durable authorization runtime is not authorized.',
            );
        }
    }
}
