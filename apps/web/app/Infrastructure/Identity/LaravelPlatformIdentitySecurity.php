<?php
declare(strict_types=1);
namespace App\Infrastructure\Identity;
use App\Application\Platform\{PlatformOwnerAccess,PlatformViolation};
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Rotate monotonically versioned factor, revoke old recovery capabilities and sessions atomically.
final readonly class LaravelPlatformIdentitySecurity {
 public function __construct(private Connection $db,private Encrypter $cipher,private PlatformOwnerAccess $owner){}
 public function reset(array $business,string $identity):array{
  $actor=$this->owner->require();$tenant=$business['tenant_id'];
  if($tenant===$actor->tenantId()->value()||!in_array($business['status'],['active','suspended'],true))throw new PlatformViolation('SECURITY_TARGET_DENIED',403);
  return $this->db->transaction(function()use($tenant,$identity):array{
   if(!$this->db->table('oneqay_identities')->where('tenant_id',$tenant)->where('id',$identity)->lockForUpdate()->first())throw new PlatformViolation('IDENTITY_NOT_FOUND',404);
   $q=$this->db->table('oneqay_identity_totp_factors')->where('tenant_id',$tenant)->where('identity_id',$identity);$factor=(clone $q)->lockForUpdate()->first();
   if($factor===null||$factor->confirmed_at_unix===null)throw new PlatformViolation('TOTP_NOT_ENROLLED',409);
   $now=time();$secret=(new OtphpPrivilegedTotpEngine())->generateSecret();
   $sealed=$this->cipher->encryptString(json_encode(['v'=>1,'tenant_id'=>$tenant,'identity_id'=>$identity,'secret'=>$secret],JSON_THROW_ON_ERROR));
   $q->update(['secret_ciphertext'=>$sealed,'created_at_unix'=>$now,'confirmed_at_unix'=>null,'last_accepted_time_step'=>null,'factor_epoch'=>(int)$factor->factor_epoch+1]);
   foreach(['oneqay_identity_totp_recovery_codes','oneqay_identity_recovery_codes']as$table)$this->db->table($table)->where('tenant_id',$tenant)->where('identity_id',$identity)->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>$now]);
   $revoked=$this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$tenant)->where('identity_id',$identity)->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>$now]);
   return ['factor_state'=>'reenrollment_required','sessions_revoked'=>$revoked,'password_preserved'=>true];
  });
 }
}
