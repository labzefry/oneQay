<?php
// Author by Lab | zefry. Synthetic database only; no network, real tenant or SMTP.
declare(strict_types=1);
require __DIR__.'/../../../apps/web/tests/business-experience.php';
require __DIR__.'/../cpanel/repair-legacy-business-domain.php';
$platform=$app->make(App\Application\Platform\PlatformStore::class);$mail=$app->make(App\Application\Notification\MailStore::class);
config(['platform.business_domain_url'=>'https://example.test','platform.public_url'=>'https://oneqay.example.test','platform.business_domain_reserved'=>['legacy-shop']]);
$legacy=array_replace($b,['id'=>str_repeat('c',32),'tenant_id'=>'synthetic-legacy-tenant','name'=>'Legacy Shop','email'=>'legacy@example.test','status'=>'active']);$db->table('oneqay_platform_businesses')->insert($legacy);
$profile=['business_id'=>$legacy['id'],'tenant_id'=>$legacy['tenant_id'],'business_type'=>'laundry','owner_totp_required'=>false,'version'=>5];$db->table('oneqay_business_profiles')->insert($profile);
$binding=['configuration_sha256'=>str_repeat('a',64),'operator_sha256'=>str_repeat('b',64),'source'=>str_repeat('d',40)];
$deny=function(callable $f,string $code)use($assert){try{$f();$assert(false,'expected denial '.$code);}catch(RuntimeException $e){$assert($e->getMessage()===$code,'safe refusal '.$code);}};
$plan=ldPlan($db,$domains,$binding);$assert($plan['slug']==='legacy-shop2'&&$plan['profile']['business_type']==='laundry','canonical name generates free nonreserved slug while preserving type');
$assert($plan['tenant_id']===$legacy['tenant_id']&&!str_contains(json_encode($plan),'legacy@example.test'),'plan binds exact tenant without raw email');
$beforeBusiness=$db->table('oneqay_platform_businesses')->get()->toJson();$beforeCredentials=$db->table('oneqay_identity_password_credentials')->get()->toJson();$messages=$db->table('oneqay_mail_messages')->count();$audits=$db->table('oneqay_platform_audit')->count();
$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>throw new RuntimeException('BACKUP_FAILED')),'BACKUP_FAILED');
$assert(!$db->table('oneqay_business_domains')->where('business_id',$legacy['id'])->exists()&&$db->table('oneqay_business_profiles')->where('business_id',$legacy['id'])->value('version')==5,'failed backup leaves domain and version untouched');
$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>throw new RuntimeException('AUTHORITY_EXPIRED')),'AUTHORITY_EXPIRED');
$assert(!$db->table('oneqay_business_domains')->where('business_id',$legacy['id'])->exists(),'authority expiring while waiting for lock cannot mutate');
$db->table('oneqay_business_profiles')->where('business_id',$legacy['id'])->increment('version');$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>null),'STALE_LEGACY_PLAN');$db->table('oneqay_business_profiles')->where('business_id',$legacy['id'])->update(['version'=>5]);
$db->table('oneqay_business_domains')->insert(['slug'=>'retired-legacy','business_id'=>$legacy['id'],'active'=>false,'created_at'=>1,'retired_at'=>2]);$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>null),'DOMAIN_HISTORY_ALREADY_EXISTS');$db->table('oneqay_business_domains')->where('slug','retired-legacy')->delete();
$db->table('oneqay_platform_businesses')->where('id',$legacy['id'])->update(['status'=>'pending']);$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>null),'LEGACY_SCOPE_INVALID');$db->table('oneqay_platform_businesses')->where('id',$legacy['id'])->update(['status'=>'active']);
$db->table('oneqay_business_domains')->insert(['slug'=>$plan['slug'],'business_id'=>$b['id'],'active'=>false,'created_at'=>1,'retired_at'=>2]);$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>null),'DOMAIN_CANDIDATE_TAKEN');$db->table('oneqay_business_domains')->where('slug',$plan['slug'])->delete();
// Failure after domain insertion and profile update rolls the entire transaction back.
$db->table('oneqay_platform_businesses')->where('id',$legacy['id'])->update(['email'=>'invalid']);$invalid=ldPlan($db,$domains,$binding);
try{ldApply($db,$domains,$platform,$mail,$invalid,fn()=>null);$assert(false,'invalid mail accepted');}catch(App\Application\Platform\PlatformViolation){$assert(!$db->table('oneqay_business_domains')->where('business_id',$legacy['id'])->exists()&&$db->table('oneqay_business_profiles')->where('business_id',$legacy['id'])->value('version')==5,'mail enqueue failure rolls back domain and profile');}
$db->table('oneqay_platform_businesses')->where('id',$legacy['id'])->update(['email'=>$legacy['email']]);
$preimage=null;$backup=function(array $v)use(&$preimage){$preimage=$v;};$r=ldApply($db,$domains,$platform,$mail,$plan,$backup);
$assert($preimage['profile']['version']==5&&$preimage['domain_history']===[],'scoped preimage captured before mutation');
$assert($r['url']==='https://legacy-shop2.example.test'&&$domains->hostBusiness('legacy-shop2.example.test')['id']===$legacy['id'],'actual domain resolves exact business');
$after=(array)$db->table('oneqay_business_profiles')->where('business_id',$legacy['id'])->first();$expected=$plan['profile'];$expected['version']=6;$assert(ldHash($after)===ldHash($expected),'only profile version changes; type MFA tenant preserved');
$assert(ldApply($db,$domains,$platform,$mail,$plan,fn()=>throw new RuntimeException('REPLAY_BACKUP_CALLED'))===$r,'replay after lost local result returns durable receipt');
$assert($db->table('oneqay_mail_messages')->count()===$messages+1&&$db->table('oneqay_platform_audit')->count()===$audits+1&&$db->table('oneqay_business_domains')->where('business_id',$legacy['id'])->count()===1,'replay produces one domain one email one audit');
$assert($db->table('oneqay_platform_businesses')->get()->toJson()===$beforeBusiness&&$db->table('oneqay_identity_password_credentials')->get()->toJson()===$beforeCredentials,'canonical accounts and credentials unchanged');
$db->table('oneqay_business_domains')->where('slug',$plan['slug'])->update(['active'=>false,'retired_at'=>time()]);$deny(fn()=>ldApply($db,$domains,$platform,$mail,$plan,fn()=>null),'APPLIED_DOMAIN_CHANGED');
$assert(!$db->table('oneqay_business_domains')->where('slug',$plan['slug'])->value('active'),'replay never resurrects retired domain');
echo 'LEGACY_DOMAIN_REPAIR_'.$engine.'_PASS assertions='.$n.PHP_EOL;
