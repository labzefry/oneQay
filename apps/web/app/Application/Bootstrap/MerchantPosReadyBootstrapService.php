<?php

declare(strict_types=1);

namespace App\Application\Bootstrap;

use App\Application\Access\DurableOrganizationalAccessService;
use App\Application\Authorization\DurablePolicyAdministrationService;
use App\Application\Authorization\DurablePolicyMutation;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PolicyMutationId;
use App\Application\Authorization\PolicyMutationOperation;
use App\Application\Authorization\PosPermission;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\PersistenceTransaction;

// Author by Lab | zefry
final readonly class MerchantPosReadyBootstrapService
{
    public const ROLE = 'merchant-initial-pos-operator';

    /** @var array<string, string> */
    private const PERMISSIONS = [
        'merchant_pos_perm_catalog_prepare' => PosPermission::PREPARE_CATALOG,
        'merchant_pos_perm_inventory_baseline' => PosPermission::INVENTORY_BASELINE,
        'merchant_pos_perm_shift_open' => PosPermission::OPEN_SHIFT,
        'merchant_pos_perm_opening_cash' => PosPermission::RECORD_SHIFT_OPENING_CASH,
        'merchant_pos_perm_sale_complete' => PosPermission::COMPLETE_SALE,
    ];

    public function __construct(
        private MerchantContextBootstrapService $contextBootstrap,
        private DurableOrganizationalAccessService $access,
        private DurablePolicyAdministrationService $policy,
        private PersistenceTransaction $transaction,
    ) {}

    public function bootstrap(
        DurableContextGraph $graph,
        InitialTenantAdministratorProvisioningId $provisioningId,
        #[\SensitiveParameter] string $password,
    ): string {
        return $this->transaction->run(function () use ($graph, $provisioningId, $password): string {
            $outcome = $this->contextBootstrap->bootstrap($graph, $provisioningId, $password);
            if ($outcome !== MerchantContextBootstrapService::OUTCOME_APPLIED) {
                throw new MerchantContextBootstrapViolation(
                    MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                    'Merchant POS-ready bootstrap context stage failed.',
                );
            }

            $actor = new VerifiedOrganizationalContext(
                $graph->identityId,
                $graph->tenantId,
                $graph->organizationId,
                $graph->outletId,
                $graph->deviceId,
            );

            $this->access->recordVerifiedContext($actor);

            $role = RoleIdentifier::fromString(self::ROLE);
            $mutations = [
                DurablePolicyMutation::roleCreate(
                    PolicyMutationId::fromString('merchant_pos_role_create'),
                    $actor,
                    $role,
                ),
            ];

            foreach (self::PERMISSIONS as $mutationId => $permission) {
                $mutations[] = DurablePolicyMutation::permissionGrant(
                    PolicyMutationId::fromString($mutationId),
                    $actor,
                    $role,
                    PermissionIdentifier::fromString($permission),
                );
            }

            $mutations[] = DurablePolicyMutation::roleAssignment(
                PolicyMutationId::fromString('merchant_pos_role_assign_device'),
                $actor,
                PolicyMutationOperation::ROLE_ASSIGN_DEVICE,
                $graph->identityId,
                $role,
            );

            foreach ($mutations as $mutation) {
                if ($this->policy->apply($actor, $mutation) !== 'applied') {
                    throw new MerchantContextBootstrapViolation(
                        MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
                        'Merchant POS-ready bootstrap authorization stage failed.',
                    );
                }
            }

            return MerchantContextBootstrapService::OUTCOME_APPLIED;
        });
    }
}
