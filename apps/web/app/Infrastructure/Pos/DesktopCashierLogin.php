<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Infrastructure\Pos;
use App\Application\Identity\{VerifyFirstPartyIdentityCredential,FirstPartyIdentityEligibilityVerifier,VerifyFirstPartyCredentialEpoch,FirstPartySessionAuthorityService,PrivilegedTotpMfaService,PrivilegedTotpFactorEpochRepository,PrivilegedTotpMfaState};
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Tenancy\TenantContextStore;
use App\Application\Authorization\{DurableScopedAuthorizationPolicy,PosPermission};
use App\Domain\Tenancy\TenantId;
use App\Domain\Identity\PlatformIdentityId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
final readonly class DesktopCashierLogin {
 public function __construct(private Connection $db,private Container $app,private Encrypter $cipher,private LaravelDesktopPosService $desktop){}
 public function login(array $input,string $correlation):array {
  abort_unless(!array_diff(array_keys($input),['login','password','totp'])&&isset($input['login'],$input['password'])&&is_string($input['login'])&&is_string($input['password'])&&strlen($input['login'])<=190&&strlen($input['password'])<=72,401);
  $login=strtolower(trim($input['login']));$row=$this->db->table('oneqay_desktop_cashiers')->where('login',$login)->where('enabled',true)->first();
  if(!$row){password_verify($input['password'],'$2y$12$4Umg0rCJwM8kmcbZnXrJrOTDeUFRZeDA.wgdkuVhz/4djZoOCufQ2');abort(401);}
  try{
   $tenant=TenantId::fromString($row->tenant_id);$identity=PlatformIdentityId::fromString($row->identity_id);
   abort_unless($this->app->make(VerifyFirstPartyIdentityCredential::class)->verify($tenant,$identity,$input['password'])&&$this->app->make(FirstPartyIdentityEligibilityVerifier::class)->isEligible($tenant,$identity),401);
   $mfa=$this->app->make(PrivilegedTotpMfaService::class);$factor=null;$state=$mfa->requiredState($tenant,$identity);
   if($state!==null){abort_unless($state->is(PrivilegedTotpMfaState::CONFIRMED)&&is_string($input['totp']??null),401);$mfa->challenge($tenant,$identity,$input['totp']);$factor=$this->app->make(PrivilegedTotpFactorEpochRepository::class)->currentEpoch($tenant,$identity);}
   $verified=new ServerVerifiedTenantContext($tenant);$this->app->make(TenantContextStore::class)->setVerified($verified);$scope=$this->app->make(EnterOrganizationalContext::class)->enter(new ServerVerifiedPlatformIdentity($identity),$verified,$row->organization_id,$row->outlet_id,$row->device_id);$this->app->make(DurableScopedAuthorizationPolicy::class)->require($scope,PosPermission::completeSale());$epoch=$this->app->make(VerifyFirstPartyCredentialEpoch::class)->capture($tenant,$identity);
  }catch(\Throwable){abort(401,'Authentication failed.');}
  return $this->db->transaction(function()use($tenant,$identity,$row,$epoch,$factor,$correlation){
   $fresh=$this->db->table('oneqay_desktop_cashiers')->where('login',$row->login)->lockForUpdate()->first();abort_unless($fresh&&$fresh->enabled&&(int)$fresh->version===(int)$row->version,401);
   $issued=$this->app->make(FirstPartySessionAuthorityService::class)->issue($tenant,$identity,$row->organization_id,$row->outlet_id,$row->device_id,$epoch,$factor,$correlation);$a=['tenant'=>$tenant->value(),'identity'=>$identity->value(),'organization'=>$row->organization_id,'outlet'=>$row->outlet_id,'device'=>$row->device_id,'session'=>$issued->authorityId(),'credential_epoch'=>$epoch,'factor_epoch'=>$factor];$token=bin2hex(random_bytes(32));
   $this->db->table('oneqay_desktop_credentials')->insert(['token_hash'=>hash('sha256',$token),'authority'=>$this->cipher->encryptString(json_encode($a,JSON_THROW_ON_ERROR)),'expires_at'=>time()+43200,'revoked_at'=>null]);return ['token'=>$token,'snapshot'=>$this->desktop->snapshot(true)];
  });
 }
}
