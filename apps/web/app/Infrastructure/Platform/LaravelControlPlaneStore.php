<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\{ControlPlaneStore,ControlPlaneManagement,BusinessActivation,PlatformStore,PlatformViolation};
use App\Application\Notification\MailStore;
use App\Infrastructure\Identity\LaravelPlatformIdentitySecurity;
use Illuminate\Database\Connection;
// Author by Lab | zefry
final readonly class LaravelControlPlaneStore implements ControlPlaneStore {
 public function __construct(private Connection $db,private PlatformStore $platform,private ControlPlaneTelemetry $telemetry,private ControlPlaneCampaigns $marketing,private LaravelPlatformIdentitySecurity $security,private ControlPlanePilot $pilot,private MailStore $mail){}
 public function overview():array{return ['business_counts'=>$this->db->table('oneqay_platform_businesses')->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status')->all(),'noc'=>$this->telemetry->dashboard(),'configuration'=>$this->settings(),'campaigns'=>$this->marketing->listing(),'active_sessions'=>$this->db->table('oneqay_identity_first_party_sessions')->whereNull('revoked_at_unix')->where('expires_at_unix','>',time())->count(),'sampled_at'=>time()];}
 public function settings():array{$r=$this->db->table('oneqay_platform_control_settings')->where('id',1)->firstOrFail();return ['version'=>(int)$r->version,'configuration'=>array_replace(['registration_enabled'=>true,'features'=>ControlPlaneManagement::features([]),'worker_stale_seconds'=>180,'database_warn_ms'=>250,'error_rate_warn_percent'=>2],json_decode($r->configuration,true,32,JSON_THROW_ON_ERROR))];}
 public function detail(array $b):array{
  unset($b['token_hash'],$b['token_expires_at']);$t=$b['tenant_id'];$c=$this->db->table('oneqay_platform_business_controls')->where('business_id',$b['id'])->first();
  $identities=$this->db->table('oneqay_identities')->where('tenant_id',$t)->orderBy('id')->limit(250)->get(['id','first_party_authentication_enabled'])->map(function($i)use($t):array{
   $factor=$this->db->table('oneqay_identity_totp_factors')->where('tenant_id',$t)->where('identity_id',$i->id)->first(['confirmed_at_unix']);$sessions=$this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$t)->where('identity_id',$i->id)->whereNull('revoked_at_unix')->where('expires_at_unix','>',time());
   $roles=[];foreach(['tenant','organization','outlet','device']as$scope)foreach($this->db->table('oneqay_'.$scope.'_role_assignments')->where('tenant_id',$t)->where('identity_id',$i->id)->get(['role_id'])as$r)$roles[]=['role'=>$r->role_id,'scope'=>$scope];
   return ['id'=>$i->id,'authentication_enabled'=>(bool)$i->first_party_authentication_enabled,'mfa'=>$factor?($factor->confirmed_at_unix===null?'reenrollment_required':'enrolled'):'not_enrolled','sessions'=>$sessions->count(),'last_seen'=>$sessions->max('last_seen_at_unix'),'roles'=>$roles];
  })->all();
  return ['business'=>$b,'controls'=>['plan'=>$c?->plan??'standard','notes'=>$c?->notes??'','features'=>ControlPlaneManagement::features($c?json_decode($c->features,true,16,JSON_THROW_ON_ERROR):[])],'identities'=>$identities,'identity_limit'=>250,'marketing_subscribed'=>$this->marketing->subscribed($b['id']),'pilot'=>$this->pilot->inspect($b),'audit'=>$this->db->table('oneqay_platform_audit')->where('business_id',$b['id'])->orderByDesc('id')->limit(50)->get()->map(fn($r)=>(array)$r)->all()];
 }
 public function preview(string $id):array{return $this->marketing->preview($id);}
 public function execute(string $action,array $in,?array $b,string $actor):array{
  if(str_starts_with($action,'campaign.'))return $this->marketing->command($in);
  if($action==='pilot.verify')return $this->pilot->verify($b,$in,$actor);
  if($action==='security.sessions_revoke')return ['sessions_revoked'=>$this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$b['tenant_id'])->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>time()])];
  if($action==='security.totp_reset'){
   $result=$this->security->reset($b,BusinessActivation::text($in['identity_id']??null,96));$text=$b['locale']==='id'?'Pemilik platform telah mereset authenticator seorang pengguna bisnis Anda. Sesi dan kode pemulihan lama dicabut. Pengguna harus masuk dengan kata sandi dan mendaftarkan authenticator baru. Hubungi pemilik platform jika tidak diharapkan.':'The platform owner reset an authenticator for your business. Old sessions and recovery codes were revoked. Sign in with the existing password and enroll a new authenticator. Contact the platform owner if unexpected.';
   $result['notification_id']=$this->mail->enqueue($b['tenant_id'],'security-reset-'.bin2hex(random_bytes(16)),$b['email'],'oneQay · Authenticator reset',$text,time());return $result;
  }
  if($action==='settings.save'){
   $s=$in['configuration']??null;if(!is_array($s)||array_diff(array_keys($s),['registration_enabled','features','worker_stale_seconds','database_warn_ms','error_rate_warn_percent'])||!is_bool($s['registration_enabled']??null))throw new PlatformViolation('INVALID_CONFIGURATION');$s['features']=ControlPlaneManagement::features($s['features']??null);
   foreach(['worker_stale_seconds'=>[120,3600],'database_warn_ms'=>[10,10000],'error_rate_warn_percent'=>[1,50]]as$k=>[$min,$max])if(!is_int($s[$k]??null)||$s[$k]<$min||$s[$k]>$max)throw new PlatformViolation('INVALID_CONFIGURATION');
   if($this->db->table('oneqay_platform_control_settings')->where('id',1)->where('version',$in['version'])->update(['version'=>$in['version']+1,'configuration'=>json_encode($s,JSON_THROW_ON_ERROR)])!==1)throw new PlatformViolation('STALE_VERSION',409);return $this->settings();
  }
  if($action==='business.configure'){
   $locale=$in['locale']??null;if(!in_array($locale,['id','en'],true))throw new PlatformViolation('INVALID_LOCALE');$features=ControlPlaneManagement::features($in['features']??null);$plan=BusinessActivation::text($in['plan']??null,32);if(!preg_match('/\A[a-z0-9][a-z0-9_-]{0,31}\z/D',$plan))throw new PlatformViolation('INVALID_PLAN');$notes=$in['notes']??'';if(!is_string($notes)||strlen($notes)>1000)throw new PlatformViolation('INVALID_NOTES');
   $this->db->table('oneqay_platform_business_controls')->updateOrInsert(['business_id'=>$b['id']],['features'=>json_encode($features,JSON_THROW_ON_ERROR),'plan'=>$plan,'notes'=>$notes]);$updated=$this->platform->update($b['id'],(int)$b['version'],['name'=>BusinessActivation::text($in['name']??null,160),'locale'=>$locale,'updated_at'=>time()]);return ['id'=>$b['id'],'version'=>(int)$updated['version']];
  }
  if($action==='error.update'){
   if(!in_array($in['state']??null,['open','acknowledged','resolved'],true))throw new PlatformViolation('INVALID_ERROR_STATE');$note=BusinessActivation::text($in['resolution_note']??null,500);
   if($this->db->table('oneqay_platform_errors')->where('id',BusinessActivation::text($in['id']??null,64))->where('version',$in['version'])->update(['state'=>$in['state'],'resolution_note'=>$note,'version'=>$in['version']+1])!==1)throw new PlatformViolation('STALE_VERSION',409);return ['id'=>$in['id'],'state'=>$in['state']];
  }throw new PlatformViolation('UNKNOWN_ACTION');
 }
}
