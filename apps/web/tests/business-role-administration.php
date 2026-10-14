<?php
// Author by Lab | zefry. Real disposable SQLite policy database.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
use App\Application\Authorization\{AdministrationPermission, InitialTenantAdministratorProvisioningRepository, DurablePolicyAdministrationService, DurableScopedAuthorizationPolicy, PermissionIdentifier, PolicyAdministrationClock};
use App\Application\Business\{BusinessAccess, BusinessRoleAdministration};
use App\Application\Organization\{OrganizationalContextStore, VerifiedOrganizationalContext};
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Device\DeviceId;
use App\Infrastructure\Authorization\{LaravelDurablePolicyAdministrationRepository, LaravelDurableRolePermissionRepository};
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
foreach (['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('b',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','APP_DEBUG'=>'false'] as $k=>$v) {putenv($k.'='.$v);$_ENV[$k]=$v;$_SERVER[$k]=$v;}
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['config']->set('database.default','roles_test');$app['config']->set('database.connections.roles_test',['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true]);
$connection=$app->make('db')->connection('roles_test');
$migrations=glob(__DIR__.'/../database/migrations/*.php');sort($migrations);foreach(array_slice($migrations,0,6) as $file)(require $file)->up();
$n=0;$assert=static function(bool $ok,string $message)use(&$n):void{++$n;if(!$ok)throw new RuntimeException($message);};
$reject=static function(callable $f,string $message)use($assert):void{try{$f();}catch(App\Application\Business\BusinessViolation|App\Application\Authorization\DurableAuthorizationViolation|App\Application\Authorization\DurablePolicyAdministrationViolation $e){$assert(true,$message);return;}$assert(false,$message);};
foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-target-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-no-authority-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-target-beta'],
]);
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
$connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-alpha', 'organization_id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'outlet-beta', 'organization_id' => 'organization-beta'],
]);
$connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-alpha', 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'device-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta'],
]);
foreach (['synthetic-admin-alpha', 'synthetic-device-admin-alpha', 'synthetic-target-alpha', 'synthetic-no-authority-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha',
    ]);
    $connection->table('oneqay_outlet_access_grants')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha',
    ]);
    $connection->table('oneqay_device_access_grants')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha', 'device_id' => 'device-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta',
]);
$connection->table('oneqay_outlet_access_grants')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta',
]);
$connection->table('oneqay_device_access_grants')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta', 'device_id' => 'device-beta',
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-control-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE, 'permission_id' => AdministrationPermission::MANAGE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-device-control-role', 'permission_id' => AdministrationPermission::MANAGE],
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-admin-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);
$connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-device-admin-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'role_id' => 'synthetic-device-control-role',
]);


$context=static fn(string $identity='synthetic-admin-alpha',string $outlet='outlet-alpha'):VerifiedOrganizationalContext=>new VerifiedOrganizationalContext(PlatformIdentityId::fromString($identity),TenantId::fromString('tenant-alpha'),OrganizationId::fromString('organization-alpha'),OutletId::fromString($outlet),DeviceId::fromString('device-alpha'));
$store=new class($context()) implements OrganizationalContextStore{public function __construct(public ?VerifiedOrganizationalContext $scope){}public function current():?VerifiedOrganizationalContext{return $this->scope;}public function setVerified(VerifiedOrganizationalContext $context):void{$this->scope=$context;}public function clear():void{$this->scope=null;}};
$policy=new DurableScopedAuthorizationPolicy(new LaravelDurableRolePermissionRepository($connection,true,'ci'));
$tx=new LaravelPersistenceTransaction($connection,true,'ci');
$clock=new class implements PolicyAdministrationClock{public function nowUnix():int{return 1791820800;}};
$service=new DurablePolicyAdministrationService($policy,new LaravelDurablePolicyAdministrationRepository($connection,true,'ci'),$tx,$clock);
$access=new BusinessAccess($store,$policy);$roles=new BusinessRoleAdministration($access,$service,$tx);
foreach(BusinessRoleAdministration::templates() as $template)$assert(!in_array(AdministrationPermission::MANAGE,$template['permissions'],true),'Presets must never grant protected administration');
$request=['operation_id'=>'business-role-fixture-1','identity_id'=>'synthetic-target-alpha','role'=>'oneqay-finance-team','action'=>'assign'];
$store->scope=$context('synthetic-no-authority-alpha');$reject(fn()=>$roles->apply($request),'Ordinary user cannot grant access');
$store->scope=$context('synthetic-device-admin-alpha');$reject(fn()=>$roles->apply($request),'Device administrator cannot define tenant roles');
$store->scope=$context();
$roles->apply($request);
$target=$context('synthetic-target-alpha');
$assert($policy->allows($target,PermissionIdentifier::fromString('business.finance.view')),'Finance module becomes usable');
$assert($policy->allows($target,PermissionIdentifier::fromString('business.finance.settle')),'Finance posting permission installed');
$assert(!$policy->allows($target,PermissionIdentifier::fromString('business.people.view')),'Unrelated people module remains unauthorized');
$assert(!$policy->allows($target,AdministrationPermission::manage()),'Finance role cannot self-elevate');
$assert(!$policy->allows($context('synthetic-target-alpha','another-outlet'),PermissionIdentifier::fromString('business.finance.view')),'Role does not spread to another outlet');
$count=$connection->table('oneqay_policy_mutations')->count();
$roles->apply($request);$assert($connection->table('oneqay_policy_mutations')->count()===$count,'Exact replay makes no duplicate journal');
$reject(fn()=>$roles->apply(array_merge($request,['identity_id'=>'synthetic-no-authority-alpha'])),'Idempotency key cannot be reused for another identity');
$reject(fn()=>$roles->apply(array_merge($request,['operation_id'=>'business-role-cross-tenant','identity_id'=>'synthetic-target-beta','role'=>'oneqay-people-team'])),'Cross-tenant target rejected');
$assert(!$connection->table('oneqay_roles')->where('id','oneqay-people-team')->exists(),'Invalid target rolls back preset creation and grants');
$roles->apply(array_merge($request,['operation_id'=>'business-role-revoke-1','action'=>'revoke']));
$assert(!$policy->allows($target,PermissionIdentifier::fromString('business.finance.view')),'Revocation immediately removes access');
$roles->apply(array_merge($request,['operation_id'=>'business-role-manager','role'=>'oneqay-business-manager']));
$store->scope=$target;
$modules=array_column($access->modules(),'key');
$assert(count($modules)===8&&!in_array('business_access',$modules,true)&&!in_array('business_settings',$modules,true)&&in_array('business_products',$modules,true)&&in_array('business_reports',$modules,true),'Manager sees eight delivered operational modules, no administrator or settings page');
$assert($policy->allows($target,PermissionIdentifier::fromString('pos.inventory.replenish')),'Manager can receive into existing POS inventory');
echo 'Business role administration: '.$n." assertions passed on SQLite.\n";
