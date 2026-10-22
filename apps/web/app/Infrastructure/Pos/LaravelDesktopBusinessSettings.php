<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Infrastructure\Pos;
use App\Application\Business\{BusinessAccess,BusinessRoleAdministration};
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\{DurableContextGraph,DurableContextGraphRepository};
use App\Application\Identity\{InitialPasswordEnrollmentService,InitialPasswordEnrollmentId,VerifyFirstPartyIdentityCredential};
use App\Application\Access\DurableOrganizationalAccessService;
use App\Domain\Identity\PlatformIdentityId;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Encryption\Encrypter;

final readonly class LaravelDesktopBusinessSettings {
 public function __construct(private Connection $db,private BusinessAccess $access,private Container $app,private Encrypter $cipher){}
 public static function defaults():array {return ['show_logo'=>true,'logo'=>null,'footer'=>'Terima kasih atas kunjungan Anda.','auto_lock_seconds'=>300,'report_recipients'=>[],'kitchens'=>[]];}
 private function query(VerifiedOrganizationalContext $s){return $this->db->table('oneqay_desktop_preferences')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value());}
 public function read(VerifiedOrganizationalContext $s):array {$row=$this->query($s)->first();return ['version'=>$row?(int)$row->version:0,'settings'=>$row?json_decode($row->settings,true,64,JSON_THROW_ON_ERROR):self::defaults()];}
 public function page():array {$s=$this->access->require('authorization.policy.manage');return $this->read($s)+['owner_account'=>$this->ownerAccount($s),'cashiers'=>$this->db->table('oneqay_desktop_cashiers')->where('tenant_id',$s->tenantId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()->value())->orderBy('name')->get(['login','name','enabled','version','identity_id'])->map(fn($r)=>(array)$r)->all()];}
 public function save(array $input):array {
  $s=$this->access->require('authorization.policy.manage');abort_unless(count($input)===2&&!array_diff(array_keys($input),['version','settings']),422);$v=$input['settings'];
  abort_unless(is_int($input['version'])&&is_array($v)&&count($v)===6&&!array_diff(array_keys($v),array_keys(self::defaults())),422);
  abort_unless(is_bool($v['show_logo'])&&is_string($v['footer'])&&mb_strlen($v['footer'])<=240&&!preg_match('/[\x00-\x08\x0b-\x1f\x7f]/',$v['footer'])&&in_array($v['auto_lock_seconds'],[0,60,120,300,900],true),422);
  abort_unless(is_array($v['report_recipients'])&&array_is_list($v['report_recipients'])&&count($v['report_recipients'])<=10,422);
  $v['report_recipients']=array_values(array_unique(array_map(function($email){abort_unless(is_string($email)&&strlen($email)<=190&&filter_var($email,FILTER_VALIDATE_EMAIL),422);return strtolower(trim($email));},$v['report_recipients'])));
  if($v['logo']!==null){$logo=$v['logo'];abort_unless(is_array($logo)&&count($logo)===3&&isset($logo['width'],$logo['height'],$logo['bits'])&&is_int($logo['width'])&&$logo['width']>=8&&$logo['width']<=256&&$logo['width']%8===0&&is_int($logo['height'])&&$logo['height']>=1&&$logo['height']<=96&&is_string($logo['bits']),422);$bytes=base64_decode($logo['bits'],true);abort_unless($bytes!==false&&strlen($bytes)===intdiv($logo['width'],8)*$logo['height'],422);}
  abort_unless(is_array($v['kitchens'])&&array_is_list($v['kitchens'])&&count($v['kitchens'])<=12,422);$ids=[];
  foreach($v['kitchens']as$k){abort_unless(is_array($k)&&count($k)===3&&isset($k['id'],$k['name'],$k['categories'])&&is_string($k['id'])&&preg_match('/\A[a-z0-9-]{1,40}\z/D',$k['id'])&&!isset($ids[$k['id']])&&is_string($k['name'])&&mb_strlen($k['name'])>=1&&mb_strlen($k['name'])<=48&&is_array($k['categories'])&&array_is_list($k['categories'])&&count($k['categories'])<=100,422);$ids[$k['id']]=true;foreach($k['categories']as$c)abort_unless(is_string($c)&&mb_strlen($c)<=96&&!preg_match('/[\x00-\x1f\x7f]/',$c),422);}
  return $this->db->transaction(function()use($s,$input,$v){$this->db->table('oneqay_outlets')->where('tenant_id',$s->tenantId()->value())->where('id',$s->outletId()->value())->lockForUpdate()->firstOrFail();$old=$this->read($s);if($old['version']===$input['version']+1&&$old['settings']===$v)return $old;abort_unless($old['version']===$input['version'],409);$this->query($s)->updateOrInsert(['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value()],['settings'=>json_encode($v,JSON_THROW_ON_ERROR),'version'=>$old['version']+1]);return $this->read($s);});
 }
 public function cashier(array $input):array {
  $s=$this->access->require('authorization.policy.manage');if(isset($input['employee_id']))abort_unless(is_string($input['employee_id'])&&preg_match('/\A[a-f0-9]{32}\z/D',$input['employee_id']),422);abort_unless(!array_diff(array_keys($input),['login','name','password','pin','enabled','version','operation_id','employee_id','account_type'])&&isset($input['login'],$input['name'],$input['enabled'],$input['version'],$input['operation_id']),422);
  abort_unless(is_string($input['login'])&&is_string($input['name']),422);$login=strtolower(trim($input['login']));$name=trim($input['name']);$password=$input['password']??'';$pin=$input['pin']??'';
  abort_unless(filter_var($login,FILTER_VALIDATE_EMAIL)&&strlen($login)<=190&&$name!==''&&mb_strlen($name)<=96&&!preg_match('/[\x00-\x1f\x7f]/',$name)&&is_bool($input['enabled'])&&is_int($input['version'])&&is_string($input['operation_id'])&&preg_match('/\A[a-zA-Z0-9-]{8,100}\z/D',$input['operation_id'])&&is_string($password)&&is_string($pin)&&($pin===''||preg_match('/\A[0-9]{6}\z/D',$pin)),422);
  return $this->db->transaction(function()use($s,$input,$login,$name,$password,$pin){
   $registry=new \App\Infrastructure\Identity\LaravelAccountEmailRegistry($this->db);$registry->lock();$owner=$registry->owner($login);$ownerMode=($input['account_type']??'staff')==='owner';
   abort_unless(in_array($input['account_type']??'staff',['owner','staff'],true),422);
   if($ownerMode){abort_unless($owner&&$owner->tenant_id===$s->tenantId()->value()&&$owner->identity_id===$s->identityId()->value()&&$owner->organization_id===$s->organizationId()->value()&&$owner->outlet_id===$s->outletId()->value()&&$owner->device_id===$s->deviceId()->value()&&$owner->status==='active'&&$password===''&&!isset($input['employee_id']),403);}
   elseif($owner)throw new \App\Application\Platform\PlatformViolation('OWNER_EMAIL_USE_EXISTING_ACCOUNT',409);
   $this->db->table('oneqay_outlets')->where('tenant_id',$s->tenantId()->value())->where('id',$s->outletId()->value())->lockForUpdate()->firstOrFail();$old=$this->db->table('oneqay_desktop_cashiers')->where('login',$login)->lockForUpdate()->first();
   if($old){if($ownerMode)abort_unless($old->identity_id===$s->identityId()->value(),409,'POS_OWNER_EMAIL_CONFLICT');abort_unless($old->tenant_id===$s->tenantId()->value()&&$old->organization_id===$s->organizationId()->value()&&$old->outlet_id===$s->outletId()->value(),409);
    if($input['version']===0&&$ownerMode&&$old->identity_id===$s->identityId()->value()&&$old->name===$name&&(bool)$old->enabled===$input['enabled'])return ['login'=>$login,'version'=>(int)$old->version];
    if($input['version']===0){abort_unless($old->identity_id==='cashier-'.substr(hash('sha256',$s->tenantId()->value().'|'.$input['operation_id']),0,32)&&$old->name===$name&&$this->app->make(VerifyFirstPartyIdentityCredential::class)->verify($s->tenantId(),PlatformIdentityId::fromString($old->identity_id),$password),409);return ['login'=>$login,'version'=>(int)$old->version];}
    abort_unless((int)$old->version===$input['version']&&$password==='',409);$identity=PlatformIdentityId::fromString($old->identity_id);
   }elseif($ownerMode){
    abort_unless($input['version']===0&&$pin!=='',422);$identity=$s->identityId();
    $this->app->make(BusinessRoleAdministration::class)->apply(['operation_id'=>'pos-owner-role-'.$input['operation_id'],'identity_id'=>$identity->value(),'role'=>'oneqay-cashier','action'=>'assign']);
   }else{
    abort_unless($input['version']===0&&strlen($password)>=12&&strlen($password)<=72&&$pin!=='',422);$identity=PlatformIdentityId::fromString('cashier-'.substr(hash('sha256',$s->tenantId()->value().'|'.$input['operation_id']),0,32));
    $graph=new DurableContextGraph($s->tenantId(),$identity,$s->organizationId(),$s->outletId(),$s->deviceId());$this->app->make(DurableContextGraphRepository::class)->persist($graph);
    $scope=new VerifiedOrganizationalContext($identity,$s->tenantId(),$s->organizationId(),$s->outletId(),$s->deviceId());$this->app->make(DurableOrganizationalAccessService::class)->recordVerifiedContext($scope);
    $enroll=$this->app->make(InitialPasswordEnrollmentService::class);$id=InitialPasswordEnrollmentId::fromString('pos-enroll-'.substr(hash('sha256',$input['operation_id']),0,32));$grant=$enroll->issue($s,$identity,$id);$enroll->redeem($s->tenantId(),$identity,$id,$grant->enrollmentToken(),$password);
    $this->app->make(BusinessRoleAdministration::class)->apply(['operation_id'=>'pos-role-'.$input['operation_id'],'identity_id'=>$identity->value(),'role'=>'oneqay-cashier','action'=>'assign']);
   }
   $verifier=$old?->pin_verifier;if($pin!==''){$salt=bin2hex(random_bytes(16));$verifier=$this->cipher->encryptString(json_encode(['salt'=>$salt,'hash'=>hash_pbkdf2('sha256',$pin,$salt,210000,64),'iterations'=>210000],JSON_THROW_ON_ERROR));}
   $version=($old?(int)$old->version:0)+1;$this->db->table('oneqay_desktop_cashiers')->updateOrInsert(['login'=>$login],['name'=>$name,'tenant_id'=>$s->tenantId()->value(),'identity_id'=>$identity->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value(),'device_id'=>$s->deviceId()->value(),'enabled'=>$input['enabled'],'pin_verifier'=>$verifier,'version'=>$version,'updated_at'=>time()]);
   if(!$ownerMode&&$this->db->getSchemaBuilder()->hasTable('oneqay_business_staff_accounts'))(new \App\Infrastructure\Business\LaravelWorkforceAccounts($this->db,$this->app))->linkCashier($s,$login,$identity->value(),$name,$input['enabled'],$input['employee_id']??null);
   if($old)$this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$s->tenantId()->value())->where('identity_id',$identity->value())->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>time()]);return ['login'=>$login,'version'=>$version];
  });
 }
 private function ownerAccount(VerifiedOrganizationalContext $s):?array {$b=$this->db->table('oneqay_platform_businesses')->where('tenant_id',$s->tenantId()->value())->where('identity_id',$s->identityId()->value())->where('status','active')->first();return $b?['login'=>$b->email,'name'=>$b->name,'identity_id'=>$b->identity_id]:null;}
 public function cashierForScope(VerifiedOrganizationalContext $s):?object {return $this->db->table('oneqay_desktop_cashiers')->where('tenant_id',$s->tenantId()->value())->where('identity_id',$s->identityId()->value())->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()?->value())->where('device_id',$s->deviceId()?->value())->where('enabled',true)->first();}
 public function native(VerifiedOrganizationalContext $s):array {$cashier=$this->cashierForScope($s);abort_unless($cashier,403);$settings=$this->read($s)['settings'];unset($settings['report_recipients']);return ['cashier_name'=>$cashier->name,'login'=>$cashier->login,'settings'=>$settings,'pin'=>$cashier->pin_verifier?json_decode($this->cipher->decryptString($cashier->pin_verifier),true,16,JSON_THROW_ON_ERROR):null];}
}
