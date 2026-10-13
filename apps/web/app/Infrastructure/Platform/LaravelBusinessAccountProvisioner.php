<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\BusinessAccountProvisioner;
use App\Application\Bootstrap\{MerchantContextBootstrapService, MerchantPosReadyBootstrapService};
use App\Application\Authorization\{InitialTenantAdministratorProvisioningId,InitialTenantAdministratorProvisioningRepository,PolicyAdministrationClock,DurablePolicyAdministrationService,DurablePolicyMutation,PolicyMutationId,PolicyMutationOperation,RoleIdentifier,PermissionIdentifier,PosPermission};
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Persistence\{DurableContextGraph,DurableContextGraphRepository,PersistenceTransaction};
use App\Application\Access\DurableOrganizationalAccessService;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Business\BusinessRoleAdministration;
use App\Infrastructure\Bootstrap\{PreauthorizedMerchantContextBootstrapAuthority,LaravelMerchantContextBootstrapStateRepository};
use App\Infrastructure\Identity\LaravelTenantSessionRevoker;
use App\Domain\Tenancy\TenantId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Device\DeviceId;
use Illuminate\Contracts\Container\Container;
// Author by Lab | zefry. Reuses canonical identity, context, credential and policy contracts.
final readonly class LaravelBusinessAccountProvisioner implements BusinessAccountProvisioner
{
    public function __construct(private Container $app,private LaravelTenantSessionRevoker $sessions) {}
    public function provision(array $business,#[\SensitiveParameter] string $password):void
    {
        PlatformProvisioningScope::execute($business,function () use ($business,$password):void {
            $grant=array_intersect_key($business,array_flip(['tenant_id','identity_id','organization_id','outlet_id','device_id']));
            $grant['provisioning_id']='activate-'.$business['id'];
            $graph=new DurableContextGraph(TenantId::fromString($grant['tenant_id']),PlatformIdentityId::fromString($grant['identity_id']),OrganizationId::fromString($grant['organization_id']),OutletId::fromString($grant['outlet_id']),DeviceId::fromString($grant['device_id']));
            $tx=$this->app->make(PersistenceTransaction::class);
            $policies=$this->app->make(DurablePolicyAdministrationService::class);
            $service=new MerchantPosReadyBootstrapService(new MerchantContextBootstrapService(
                new PreauthorizedMerchantContextBootstrapAuthority([$grant]),
                new LaravelMerchantContextBootstrapStateRepository($this->app->make('db')->connection(),true,(string)config('oneqay.runtime_class')),
                $this->app->make(DurableContextGraphRepository::class),$this->app->make(InitialTenantAdministratorProvisioningRepository::class),
                $this->app->make(FirstControlPrincipalCredentialBootstrapService::class),$tx,$this->app->make(PolicyAdministrationClock::class)),
                $this->app->make(DurableOrganizationalAccessService::class),$policies,$tx);
            $service->bootstrap($graph,InitialTenantAdministratorProvisioningId::fromString($grant['provisioning_id']),$password);
            $actor=new VerifiedOrganizationalContext($graph->identityId,$graph->tenantId,$graph->organizationId,$graph->outletId,$graph->deviceId);
            $role=RoleIdentifier::fromString('business-owner-operational');
            $id=static fn(string $part):PolicyMutationId=>PolicyMutationId::fromString(hash('sha256',$business['id'].'|'.$part));
            $policies->apply($actor,DurablePolicyMutation::roleCreate($id('role'),$actor,$role));
            $permissions=array_unique([...BusinessRoleAdministration::templates()['oneqay-business-manager']['permissions'],...array_values((new \ReflectionClass(PosPermission::class))->getConstants()),
                'pos.shift.close','pos.inventory.replenish','pos.shift.cash-variance-review-decision.record','pos.reporting.sales-summary.view']);
            foreach($permissions as $permission)$policies->apply($actor,DurablePolicyMutation::permissionGrant($id($permission),$actor,$role,PermissionIdentifier::fromString($permission)));
            $policies->apply($actor,DurablePolicyMutation::roleAssignment($id('assign'),$actor,PolicyMutationOperation::ROLE_ASSIGN_TENANT,$graph->identityId,$role));
        });
    }
    public function verifyPassword(array $business, #[\SensitiveParameter] string $password):bool
    { return $this->app->make(\App\Application\Identity\VerifyFirstPartyIdentityCredential::class)->verify(TenantId::fromString($business['tenant_id']),PlatformIdentityId::fromString($business['identity_id']),$password); }
    public function revokeTenantSessions(string $tenant,int $now):void { $this->sessions->revoke($tenant,$now); }
}
