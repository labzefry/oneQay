<?php
// Author by Lab | zefry. Self-service owner-only authenticator policy, with credential verification and revocation.
declare(strict_types=1);
namespace App\Infrastructure\Identity;
use App\Application\Business\BusinessAccess;
use App\Application\Platform\PlatformViolation;
use App\Application\Identity\{VerifyFirstPartyIdentityCredential,PrivilegedTotpMfaService,PrivilegedTotpMfaState};
use App\Delivery\Http\Identity\FirstPartySessionKeys as K;
use Illuminate\Http\Request;
final class LaravelBusinessOwnerSecurity {
 public function owner():array {
  $c=app(BusinessAccess::class)->require('authorization.policy.manage');$db=app('db')->connection();$b=$db->table('oneqay_platform_businesses')->where('tenant_id',$c->tenantId()->value())->where('identity_id',$c->identityId()->value())->where('status','active')->first();
  if(!$b||$b->tenant_id===config('platform.owner_tenant'))throw new PlatformViolation('BUSINESS_OWNER_REQUIRED',403);return [$c,(array)$b];
 }
 public function status():array {[$c,$b]=$this->owner();$p=app('db')->table('oneqay_business_profiles')->where('business_id',$b['id'])->first();return ['required'=>(bool)($p->owner_totp_required??true),'version'=>(int)($p->version??0)];}
 public function command(Request $r):array {
  [$c,$b]=$this->owner();$in=$r->except('_token');if(array_diff(array_keys($in),['action','password','code','version'])||!is_string($in['password']??null)||strlen($in['password'])>72||!in_array($in['action']??null,['enable','confirm','disable','reauth'],true))throw new PlatformViolation('INVALID_FIELDS');
  if(!app(VerifyFirstPartyIdentityCredential::class)->verify($c->tenantId(),$c->identityId(),$in['password']))throw new PlatformViolation('REAUTHENTICATION_FAILED',401);
  $db=app('db')->connection();return $db->transaction(function()use($r,$in,$c,$b,$db){
   $db->table('oneqay_platform_businesses')->where('id',$b['id'])->lockForUpdate()->firstOrFail();$p=$db->table('oneqay_business_profiles')->where('business_id',$b['id'])->lockForUpdate()->first();
   if(!is_int($in['version']??null)||$in['version']!==(int)($p->version??0))throw new PlatformViolation('STALE_VERSION',409);
   $mfa=app(PrivilegedTotpMfaService::class);$repo=app(\App\Application\Identity\PrivilegedTotpMfaRepository::class);$state=$repo->factorState($c->tenantId(),$c->identityId());
   if($in['action']==='reauth'){
    if(!BusinessOwnerSecurityPolicy::disabled($db,$b['tenant_id'],$b['identity_id']))throw new PlatformViolation('AUTHENTICATOR_STEP_UP_REQUIRED',403);
    $context=['identity_id'=>$c->identityId()->value(),'tenant_id'=>$c->tenantId()->value(),'organization_id'=>$c->organizationId()->value(),'outlet_id'=>$c->outletId()?->value(),'device_id'=>$c->deviceId()?->value()];$r->session()->put([K::STEP_UP_SCOPE=>'policy_administration',K::STEP_UP_CONTEXT=>$context,K::STEP_UP_VERIFIED_AT=>time()]);return ['status'=>'verified'];
   }
   if($p===null){$type=$db->table('oneqay_site_registrations')->where('business_id',$b['id'])->value('business_type')??'retail';$db->table('oneqay_business_profiles')->insert(['business_id'=>$b['id'],'tenant_id'=>$b['tenant_id'],'business_type'=>$type,'owner_totp_required'=>true,'version'=>1]);}
   if($in['action']==='enable'){
    if($state->is(PrivilegedTotpMfaState::CONFIRMED))throw new PlatformViolation('AUTHENTICATOR_ALREADY_ENABLED',409);
    $db->table('oneqay_business_profiles')->where('business_id',$b['id'])->update(['owner_totp_required'=>true]);$issued=$mfa->startEnrollment($c->tenantId(),$c->identityId());$db->table('oneqay_business_profiles')->where('business_id',$b['id'])->increment('version');return ['status'=>'enrollment_pending','secret'=>$issued->secret(),'uri'=>$issued->provisioningUri(),'version'=>($p?(int)$p->version:1)+1];
   }
   $code=$in['code']??'';if(!is_string($code))throw new PlatformViolation('INVALID_FIELDS');
   if($in['action']==='confirm')$mfa->confirmEnrollment($c->tenantId(),$c->identityId(),$code);
   else {
    if($state->is(PrivilegedTotpMfaState::CONFIRMED))$mfa->challenge($c->tenantId(),$c->identityId(),$code);
    $q=$db->table('oneqay_identity_totp_factors')->where('tenant_id',$b['tenant_id'])->where('identity_id',$b['identity_id']);$factor=(clone $q)->lockForUpdate()->first();
    if($factor){$secret=(new OtphpPrivilegedTotpEngine())->generateSecret();$q->update(['secret_ciphertext'=>app('encrypter')->encryptString(json_encode(['v'=>1,'tenant_id'=>$b['tenant_id'],'identity_id'=>$b['identity_id'],'secret'=>$secret],JSON_THROW_ON_ERROR)),'created_at_unix'=>time(),'confirmed_at_unix'=>null,'last_accepted_time_step'=>null,'factor_epoch'=>(int)$factor->factor_epoch+1]);}
    $db->table('oneqay_business_profiles')->where('business_id',$b['id'])->update(['owner_totp_required'=>false]);
   }
   $db->table('oneqay_business_profiles')->where('business_id',$b['id'])->increment('version');
   foreach(['oneqay_identity_totp_recovery_codes','oneqay_identity_recovery_codes','oneqay_identity_first_party_sessions']as$table)$db->table($table)->where('tenant_id',$b['tenant_id'])->where('identity_id',$b['identity_id'])->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>time()]);
   app(\App\Application\Platform\PlatformStore::class)->audit($b['identity_id'],'business.totp_'.$in['action'],$b['id'],'Owner verified password and authenticator policy change',time());app(\App\Application\Notification\MailStore::class)->enqueue($b['tenant_id'],'owner-mfa-'.bin2hex(random_bytes(16)),$b['email'],'Perubahan keamanan akun oneQay / Account security changed','Pengaturan authenticator akun Anda telah diubah. Sesi sebelumnya dicabut. Masuk kembali menggunakan pengaturan keamanan terbaru. Jika bukan Anda, hubungi dukungan oneQay. / Your authenticator setting changed; previous sessions were revoked.',time());
   $r->session()->invalidate();$r->session()->regenerateToken();return ['status'=>'saved','redirect'=>'/business-login'];
  });
 }
}
