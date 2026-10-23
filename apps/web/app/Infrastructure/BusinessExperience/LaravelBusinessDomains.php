<?php
// Author by Lab | zefry.
declare(strict_types=1);
namespace App\Infrastructure\BusinessExperience;
use App\Application\BusinessExperience\BusinessDomain;
use App\Application\Platform\{PlatformStore,PlatformViolation,PlatformOwnerAccess,BusinessActivation};
use App\Application\Notification\MailStore;
use Illuminate\Database\Connection;
final readonly class LaravelBusinessDomains {
 public function __construct(private Connection $db,private PlatformStore $platform,private MailStore $mail,private PlatformOwnerAccess $owner){}
 public function installed():bool{return $this->db->getSchemaBuilder()->hasTable('oneqay_business_profiles');}
 public function available(string $slug):bool{BusinessDomain::slug($slug);return !$this->db->table('oneqay_business_domains')->where('slug',$slug)->exists();}
 public function availability(string $slug):array {
  $slug=BusinessDomain::slug($slug);$suggestions=[];
  for($n=2;$n<100000&&count($suggestions)<3;$n++){$candidate=substr($slug,0,36).$n;if($this->available($candidate))$suggestions[]=$candidate;}
  return ['slug'=>$slug,'available'=>$this->available($slug),'suggestions'=>$suggestions,'root'=>BusinessDomain::root((string)config('platform.public_url'))];
 }
 public function claim(array $business,string $slug,string $type):void {
  BusinessDomain::slug($slug);BusinessDomain::type($type);
  // Registration's surrounding transaction locks the canonical account registry; unique PK is the final race guard.
  if(!$this->available($slug))throw new PlatformViolation('SUBDOMAIN_UNAVAILABLE',409);
  $this->db->table('oneqay_business_profiles')->insert(['business_id'=>$business['id'],'tenant_id'=>$business['tenant_id'],'business_type'=>$type,'owner_totp_required'=>true,'version'=>1]);
  $this->db->table('oneqay_business_domains')->insert(['slug'=>$slug,'business_id'=>$business['id'],'active'=>true,'created_at'=>time(),'retired_at'=>null]);
 }
 public function profile(string $tenant):?array {
  if(!$this->installed())return null;
  $p=$this->db->table('oneqay_business_profiles as p')->join('oneqay_platform_businesses as b','b.id','=','p.business_id')->where('p.tenant_id',$tenant)->select('p.*','b.name','b.identity_id','b.status')->first();
  if(!$p)return null;$domain=$this->db->table('oneqay_business_domains')->where('business_id',$p->business_id)->where('active',true)->value('slug');return (array)$p+['subdomain'=>$domain,'url'=>$domain?'https://'.$domain.'.'.BusinessDomain::root((string)config('platform.public_url')):null];
 }
 public function hostBusiness(string $host):?array {
  $root=BusinessDomain::root((string)config('platform.public_url'));if($host===$root)return null;
  if(!str_ends_with($host,'.'.$root))throw new PlatformViolation('BUSINESS_HOST_UNKNOWN',404);
  $slug=substr($host,0,-strlen('.'.$root));BusinessDomain::slug($slug);
  $b=$this->db->table('oneqay_business_domains as d')->join('oneqay_platform_businesses as b','b.id','=','d.business_id')->where('d.slug',$slug)->where('d.active',true)->where('b.status','active')->select('b.*')->first();
  if(!$b)throw new PlatformViolation('BUSINESS_HOST_UNKNOWN',404);return (array)$b;
 }
 public function rename(array $in):array {
  $actor=$this->owner->require();if(array_diff(array_keys($in),['business_id','slug','reason','version','operation_id']))throw new PlatformViolation('INVALID_FIELDS');
  $id=BusinessActivation::text($in['business_id']??null,32);$slug=BusinessDomain::slug($in['slug']??null);$reason=BusinessActivation::text($in['reason']??null,300);$op=BusinessActivation::text($in['operation_id']??null,128);
  if(!is_int($in['version']??null)||!preg_match('/\A[a-zA-Z0-9_-]{8,128}\z/D',$op))throw new PlatformViolation('INVALID_FIELDS');
  return $this->platform->once($actor->identityId()->value(),$op,$in,function()use($actor,$id,$slug,$reason,$in,$op){$b=$this->platform->business($id,true);$p=$this->db->table('oneqay_business_profiles')->where('business_id',$id)->lockForUpdate()->first();if(!$p||(int)$p->version!==$in['version'])throw new PlatformViolation('STALE_VERSION',409);if(!$this->available($slug))throw new PlatformViolation('SUBDOMAIN_UNAVAILABLE',409);
   $previous=$this->db->table('oneqay_business_domains')->where('business_id',$id)->where('active',true)->value('slug');$this->db->table('oneqay_business_domains')->where('business_id',$id)->where('active',true)->update(['active'=>false,'retired_at'=>time()]);$this->db->table('oneqay_business_domains')->insert(['slug'=>$slug,'business_id'=>$id,'active'=>true,'created_at'=>time(),'retired_at'=>null]);$this->db->table('oneqay_business_profiles')->where('business_id',$id)->increment('version');
   $root=BusinessDomain::root((string)config('platform.public_url'));$text="Alamat bisnis oneQay Anda diubah oleh pemilik platform.\nAlamat lama: https://$previous.$root\nAlamat baru: https://$slug.$root\nAlasan: $reason\nWaktu UTC: ".gmdate('c')."\nJika tidak mengenali perubahan ini, hubungi dukungan oneQay.\n\nYour business address has changed. Please use the new address above.";
   $this->mail->enqueue($b['tenant_id'],'domain-change-'.$op,$b['email'],'Perubahan alamat bisnis / Business address changed',$text,time());$this->platform->audit($actor->identityId()->value(),'business.domain_changed',$id,$reason,time());$this->db->table('oneqay_business_handoffs')->where('business_id',$id)->delete();(new \App\Infrastructure\Identity\LaravelTenantSessionRevoker($this->db))->revoke($b['tenant_id'],time());return ['url'=>'https://'.$slug.'.'.$root,'version'=>$in['version']+1];});
 }
}
