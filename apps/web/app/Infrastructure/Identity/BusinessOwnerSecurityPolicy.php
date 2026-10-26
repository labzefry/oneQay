<?php
// Author by Lab | zefry. Optional MFA applies only to the exact tenant business owner; platform owner is excluded.
declare(strict_types=1);
namespace App\Infrastructure\Identity;
use Illuminate\Database\Connection;
final class BusinessOwnerSecurityPolicy {
 public static function disabled(Connection $db,string $tenant,string $identity):bool {
  if($tenant===(string)config('platform.owner_tenant')||!$db->getSchemaBuilder()->hasTable('oneqay_business_profiles'))return false;
  return $db->table('oneqay_business_profiles as p')->join('oneqay_platform_businesses as b','b.id','=','p.business_id')->where('p.tenant_id',$tenant)->where('b.identity_id',$identity)->where('b.status','active')->where('p.owner_totp_required',false)->exists();
 }
}
