<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Authorization\{AdministrationPermission, DurablePolicyAdministrationService, DurablePolicyMutation, PermissionIdentifier, PolicyMutationId, PolicyMutationOperation, RoleIdentifier};
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry. Presets use the existing protected policy administration boundary.
final readonly class BusinessRoleAdministration
{
    public function __construct(private BusinessAccess $access, private DurablePolicyAdministrationService $policies, private PersistenceTransaction $transaction) {}

    public static function templates(): array
    {
        $permissions = array_values(array_unique([...array_values(BusinessAccess::ACTIONS), ...array_column(BusinessAccess::MODULES, 1), 'pos.inventory.replenish']));
        $permissions = array_values(array_filter($permissions, static fn (string $p): bool => $p !== AdministrationPermission::MANAGE));
        $select = static fn (array $prefixes): array => array_values(array_filter($permissions, static function (string $p) use ($prefixes): bool {
            foreach ($prefixes as $prefix) if (str_starts_with($p, $prefix)) return true;
            return false;
        }));
        return [
            'oneqay-cashier' => ['title' => 'Cashier', 'permissions' => ['pos.sale.complete','pos.shift.open','pos.shift.opening-cash.record','pos.shift.closing-cash.record','pos.shift.cash-variance-explanation.record','pos.shift.close']],
            'oneqay-shift-supervisor' => ['title' => 'Shift supervisor', 'permissions' => ['pos.shift.closing-cash.record','pos.shift.cash-variance-explanation.record','pos.shift.cash-variance-review-decision.record','pos.shift.close','pos.reporting.sales-summary.view']],
            'oneqay-business-manager' => ['title' => 'Business manager', 'permissions' => $permissions],
            'oneqay-purchasing-team' => ['title' => 'Purchasing team', 'permissions' => $select(['business.suppliers.', 'business.purchases.', 'pos.inventory.replenish'])],
            'oneqay-finance-team' => ['title' => 'Finance team', 'permissions' => $select(['business.finance.'])],
            'oneqay-customer-team' => ['title' => 'Customer team', 'permissions' => $select(['business.customers.'])],
            'oneqay-people-team' => ['title' => 'People team', 'permissions' => $select(['business.people.'])],
            'oneqay-business-viewer' => ['title' => 'Business viewer', 'permissions' => array_values(array_filter($permissions, static fn (string $p): bool => str_ends_with($p, '.view')))],
        ];
    }

    public function apply(array $input): array
    {
        $scope = $this->access->require(AdministrationPermission::MANAGE);
        BusinessInput::fields($input, ['operation_id', 'identity_id', 'role', 'action']);
        $operation = BusinessInput::text($input['operation_id'], 128);
        if (strlen($operation) < 8) throw new BusinessViolation('INVALID_IDENTIFIER');
        $target = PlatformIdentityId::fromString(BusinessInput::text($input['identity_id'], 96));
        $key = BusinessInput::text($input['role'], 64);
        $template = self::templates()[$key] ?? throw new BusinessViolation('UNKNOWN_ROLE');
        $action = BusinessInput::text($input['action'], 16);
        if (!in_array($action, ['assign', 'revoke'], true)) throw new BusinessViolation('UNKNOWN_ACTION');
        $role = RoleIdentifier::fromString($key);
        $id = static fn (string $part): PolicyMutationId => PolicyMutationId::fromString(hash('sha256', $operation.'|'.$part));
        $failure = null;
        try {
        return $this->transaction->run(function () use ($scope, $target, $template, $action, $role, $id, &$failure): array {
            try {
            if ($action === 'assign') {
                $this->policies->apply($scope, DurablePolicyMutation::roleCreate($id('role'), $scope, $role));
                foreach ($template['permissions'] as $permission) $this->policies->apply($scope, DurablePolicyMutation::permissionGrant($id('permission:'.$permission), $scope, $role, PermissionIdentifier::fromString($permission)));
            }
            $outcome = $this->policies->apply($scope, DurablePolicyMutation::roleAssignment($id('assignment'), $scope, $action === 'assign' ? PolicyMutationOperation::ROLE_ASSIGN_OUTLET : PolicyMutationOperation::ROLE_REVOKE_OUTLET, $target, $role));
            return ['outcome' => $outcome, 'role' => $role->value(), 'identity_id' => $target->value(), 'outlet_id' => $scope->outletId()?->value()];
            } catch (\Throwable $error) { $failure = $error; throw $error; }
        });
        } catch (\App\Application\Persistence\DurablePersistenceViolation $error) {
            // Preserve the domain rejection after the outer transaction has rolled back.
            throw $failure ?? $error;
        }
    }
}
