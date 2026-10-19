<?php
// Author by Lab | zefry

declare(strict_types=1);
namespace App\Infrastructure\Pos;

use App\Application\Authorization\{DurableScopedAuthorizationPolicy,PosPermission};
use App\Application\Identity\{FirstPartyIdentityEligibilityVerifier,FirstPartySessionAuthorityService};
use App\Application\Organization\{EnterOrganizationalContext,OrganizationalContextStore,VerifiedOrganizationalContext};
use App\Application\Tenancy\TenantContextStore;
use App\Application\Pos\{CompleteSale,SaleCommand};
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Domain\Pos\{Cart,CartLine,Money,ProductId,TenderCategory};
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use App\Delivery\Http\Identity\FirstPartySessionKeys as Keys;
use InvalidArgumentException;

/** Native credentials never grant business administration or bypass the canonical POS use case. */
final readonly class LaravelDesktopPosService
{
 public function __construct(private Connection $db,private Encrypter $cipher,private OrganizationalContextStore $contexts,private TenantContextStore $tenants,private EnterOrganizationalContext $enter,private FirstPartySessionAuthorityService $sessions,private FirstPartyIdentityEligibilityVerifier $eligibility,private DurableScopedAuthorizationPolicy $authorization,private CompleteSale $sales){}

 public function pair(Request $request):array {
  $this->authorization->require($this->context(),PosPermission::completeSale());
  $authority=[];
  foreach(['tenant'=>Keys::TENANT,'identity'=>Keys::IDENTITY,'organization'=>Keys::ORGANIZATION,'outlet'=>Keys::OUTLET,'device'=>Keys::DEVICE,'session'=>Keys::SESSION_AUTHORITY_ID,'credential_epoch'=>Keys::CREDENTIAL_EPOCH,'factor_epoch'=>Keys::MFA_FACTOR_EPOCH]as$k=>$sessionKey)$authority[$k]=$request->session()->get($sessionKey);
  $code=strtoupper(bin2hex(random_bytes(8)));$now=time();
  $this->db->table('oneqay_desktop_pairings')->where('expires_at','<',$now)->delete();
  $this->db->table('oneqay_desktop_pairings')->insert(['code_hash'=>hash('sha256',$code),'authority'=>$this->cipher->encryptString(json_encode($authority,JSON_THROW_ON_ERROR)),'expires_at'=>$now+300,'consumed_at'=>null]);
  return ['code'=>$code,'expires_at'=>$now+300];
 }
 public function exchange(string $code):array {
  if(!preg_match('/\A[A-F0-9]{16}\z/D',$code))abort(401);
  return $this->db->transaction(function()use($code):array{
   $q=$this->db->table('oneqay_desktop_pairings')->where('code_hash',hash('sha256',$code));$row=(clone$q)->lockForUpdate()->first();
   abort_unless($row&&$row->consumed_at===null&&(int)$row->expires_at>=time(),401);
   $authority=json_decode($this->cipher->decryptString($row->authority),true,32,JSON_THROW_ON_ERROR);$this->activate($authority);
   $this->authorization->require($this->context(),PosPermission::completeSale());
   $token=bin2hex(random_bytes(32));$this->db->table('oneqay_desktop_credentials')->insert(['token_hash'=>hash('sha256',$token),'authority'=>$row->authority,'expires_at'=>time()+43200,'revoked_at'=>null]);$q->update(['consumed_at'=>time()]);
   return ['token'=>$token,'snapshot'=>$this->snapshot()];
  });
 }
 public function authenticate(string $token):void {
  abort_unless(preg_match('/\A[a-f0-9]{64}\z/D',$token),401);
  $row=$this->db->table('oneqay_desktop_credentials')->where('token_hash',hash('sha256',$token))->first();
  abort_unless($row&&$row->revoked_at===null&&(int)$row->expires_at>time(),401);
  $this->activate(json_decode($this->cipher->decryptString($row->authority),true,32,JSON_THROW_ON_ERROR));
  $this->authorization->require($this->context(),PosPermission::completeSale());
 }
 private function activate(array $a):void {
  try{
   $tenant=TenantId::fromString($a['tenant']);$identity=PlatformIdentityId::fromString($a['identity']);
   $this->sessions->assertActiveCurrent($tenant,$identity,$a['session'],$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch']);
   abort_unless($this->eligibility->isEligible($tenant,$identity),401);
   $verified=new ServerVerifiedTenantContext($tenant);$this->tenants->setVerified($verified);
   $this->enter->enter(new ServerVerifiedPlatformIdentity($identity),$verified,$a['organization'],$a['outlet'],$a['device']);
  }catch(\Throwable $e){$this->clear();abort(401,'Desktop authorization must be renewed.');}
 }
 public function clear():void {$this->contexts->clear();$this->tenants->clear();}
 public function context():VerifiedOrganizationalContext {return $this->contexts->current()??throw new InvalidArgumentException('Missing context.');}
 public function scope():array {$c=$this->context();return ['tenant_id'=>$c->tenantId()->value(),'identity_id'=>$c->identityId()->value(),'organization_id'=>$c->organizationId()->value(),'outlet_id'=>$c->outletId()?->value(),'device_id'=>$c->deviceId()?->value()];}
 public function scopeHash():string{return hash('sha256',json_encode($this->scope(),JSON_THROW_ON_ERROR));}
 private function catalog():array {
  $s=$this->scope();$rows=$this->db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$s['tenant_id'])->where('outlet_id',$s['outlet_id'])->where('active',true)->orderBy('product_id')->limit(20001)->get();
  abort_if(count($rows)>20000,422,'Catalog requires a narrower outlet scope.');$profiles=[];
  if($this->db->getSchemaBuilder()->hasTable('oneqay_business_product_profiles'))$profiles=$this->db->table('oneqay_business_product_profiles')->where('tenant_id',$s['tenant_id'])->where('outlet_id',$s['outlet_id'])->get()->keyBy('product_id')->all();
  return $rows->map(function($r)use($profiles):array{$profile=$profiles[$r->product_id]??null;return ['id'=>$r->product_id,'name'=>$r->display_name,'price'=>(int)$r->unit_price_atomic,'currency'=>$r->currency,'scale'=>(int)$r->currency_scale,'stock'=>(int)$r->available_quantity,'barcode'=>$profile?->barcode??'','category'=>$profile?->category??''];})->all();
 }
 public function snapshot(bool $enterprise=false):array {
  $this->authorization->require($this->context(),PosPermission::completeSale());$s=$this->scope();
  $shift=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$s['tenant_id'])->where('organization_id',$s['organization_id'])->where('outlet_id',$s['outlet_id'])->where('device_id',$s['device_id'])->where('active_slot',1)->first();
  abort_unless($enterprise||$shift,409,'Open the cashier shift online before connecting.');
  $leaseShift=$enterprise?'enterprise-v2':$shift->shift_id;
  $catalog=$this->catalog();abort_unless(count($catalog)>0,409,'Prepare the outlet catalog first.');
  $currencies=array_unique(array_map(fn($p)=>$p['currency'].':'.$p['scale'],$catalog));abort_unless(count($currencies)===1,409,'Outlet catalog currency must be consistent.');
  $now=time();
  $authorityCatalog=array_map(fn($p)=>array_intersect_key($p,array_flip(['id','price','currency','scale'])),$catalog);
  $revision=hash('sha256',json_encode($authorityCatalog,JSON_THROW_ON_ERROR));
  // Reuse an immutable price lease across stock refreshes. Do not duplicate the entire catalog per sale.
  $lease=$this->db->table('oneqay_desktop_leases')->where('scope_hash',$this->scopeHash())->where('shift_id',$leaseShift)->where('catalog_revision',$revision)->where('expires_at','>',$now+900)->orderByDesc('issued_at')->first();
  $id=$lease?->id??bin2hex(random_bytes(16));$issued=$lease?(int)$lease->issued_at:$now;$expires=$lease?(int)$lease->expires_at:$now+28800;
  $business=null;if($this->db->getSchemaBuilder()->hasTable('oneqay_business_settings'))$business=$this->db->table('oneqay_business_settings')->where('tenant_id',$s['tenant_id'])->where('organization_id',$s['organization_id'])->first();
  $snapshot=['lease_id'=>$id,'scope'=>$s,'shift_id'=>$shift?->shift_id??'','issued_at'=>$issued,'expires_at'=>$expires,'catalog'=>$catalog,'catalog_revision'=>$revision,'server_time'=>$now,'business_name'=>$business?->business_name??'oneQay POS','currency_symbol'=>$business?->symbol??'','can_count'=>$this->authorization->allows($this->context(),PosPermission::inventoryBaseline())];
  if(!$lease)$this->db->table('oneqay_desktop_leases')->insert(['id'=>$id,'scope_hash'=>$this->scopeHash(),'tenant_id'=>$s['tenant_id'],'shift_id'=>$leaseShift,'catalog_revision'=>$revision,'snapshot'=>json_encode($enterprise?['catalog'=>$authorityCatalog,'scope'=>$s]:['catalog'=>$authorityCatalog],JSON_THROW_ON_ERROR),'issued_at'=>$issued,'expires_at'=>$expires]);
  if($enterprise){$snapshot+=app(LaravelDesktopBusinessSettings::class)->native($this->context());$snapshot['protocol']=2;$snapshot['active_shift']=$shift?['id'=>$shift->shift_id,'identity_id'=>$shift->actor_identity_id,'opened_at'=>(int)$shift->opened_at_unix,'local_id'=>$this->db->table('oneqay_desktop_events')->where('tenant_id',$s['tenant_id'])->where('kind','shift_open')->where('scope_hash',$this->scopeHash())->where('result','like','%'.$shift->shift_id.'%')->value('operation_id')]:null;}
  return $snapshot;
 }
 public function sync(array $operations,string $correlation):array {
  if(!array_is_list($operations)||count($operations)>50)abort(422);
  $results=[];foreach($operations as$operation){
   try{$results[]=$this->accept($operation,$correlation);}
   catch(\App\Application\Authorization\DurableAuthorizationViolation){abort(403);}
   catch(\App\Application\Pos\PosTransactionViolation){$results[]=['operation_id'=>$operation['operation_id']??'','state'=>'conflict','code'=>'POS_SERVER_REJECTED','correlation_id'=>$correlation];}
   catch(InvalidArgumentException|\ValueError){$results[]=['operation_id'=>is_array($operation)?($operation['operation_id']??''):'','state'=>'rejected','code'=>'POS_INVALID_OPERATION','correlation_id'=>$correlation];}
  }return ['results'=>$results,'server_time'=>time()];
 }
 private function accept(mixed $op,string $correlation):array {
  if(!is_array($op))throw new InvalidArgumentException();$keys=array_keys($op);sort($keys);
  if($keys!==['created_at','kind','lease_id','lines','operation_id','tendered'])throw new InvalidArgumentException();
  if(!is_string($op['operation_id'])||!preg_match('/\Adesk-[a-f0-9-]{36}\z/D',$op['operation_id'])||!is_int($op['created_at'])||!is_int($op['tendered'])||!is_array($op['lines'])||!array_is_list($op['lines'])||count($op['lines'])<1||count($op['lines'])>100||!in_array($op['kind'],['sale','count'],true)||!is_string($op['lease_id'])||!preg_match('/\A[a-f0-9]{32}\z/D',$op['lease_id'])||$op['tendered']<0||$op['tendered']>9007199254740991||($op['kind']==='count'&&$op['tendered']!==0))throw new InvalidArgumentException();
  foreach($op['lines']as$l)if(!is_array($l)||count($l)!==2||!array_key_exists('product_id',$l)||!array_key_exists('quantity',$l)||!is_string($l['product_id'])||!is_int($l['quantity'])||$l['quantity']<($op['kind']==='sale'?1:0)||$l['quantity']>1000000)throw new InvalidArgumentException();
  usort($op['lines'],static fn($a,$b)=>strcmp($a['product_id'],$b['product_id']));
  $op=['created_at'=>$op['created_at'],'kind'=>$op['kind'],'lease_id'=>$op['lease_id'],'lines'=>array_map(static fn($l)=>['product_id'=>$l['product_id'],'quantity'=>$l['quantity']],$op['lines']),'operation_id'=>$op['operation_id'],'tendered'=>$op['tendered']];
  $s=$this->scope();$fingerprint=hash('sha256',json_encode($op,JSON_THROW_ON_ERROR));
  return $this->db->transaction(function()use($op,$correlation,$s,$fingerprint):array{
   // Serialize all batches for this issued lease before checking the unique operation receipt.
   $lease=$this->db->table('oneqay_desktop_leases')->where('id',$op['lease_id'])->where('scope_hash',$this->scopeHash())->lockForUpdate()->first();
   if(!$lease)throw new InvalidArgumentException();
   $existing=$this->db->table('oneqay_desktop_operations')->where('tenant_id',$s['tenant_id'])->where('operation_id',$op['operation_id'])->lockForUpdate()->first();
   if($existing){if(!hash_equals($existing->fingerprint,$fingerprint)||!hash_equals($existing->scope_hash,$this->scopeHash()))throw new InvalidArgumentException();return json_decode($existing->result,true,32,JSON_THROW_ON_ERROR);}
   $result=['operation_id'=>$op['operation_id'],'state'=>'conflict','code'=>'POS_LEASE_INVALID','correlation_id'=>$correlation];
   if($op['created_at']>=(int)$lease->issued_at&&$op['created_at']<=(int)$lease->expires_at&&$op['created_at']<=time()+60){
    $cached=json_decode($lease->snapshot,true,64,JSON_THROW_ON_ERROR);$catalog=array_column($cached['catalog'],null,'id');$seen=[];
    foreach($op['lines']as$l){if(isset($seen[$l['product_id']])||!isset($catalog[$l['product_id']]))throw new InvalidArgumentException();$seen[$l['product_id']]=true;}
    if($op['kind']==='count'){
     $this->authorization->require($this->context(),PosPermission::inventoryBaseline());
     $result['state']='review';$result['code']='POS_STOCK_COUNT_REVIEW';
    }else{
     $shift=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$s['tenant_id'])->where('organization_id',$s['organization_id'])->where('outlet_id',$s['outlet_id'])->where('device_id',$s['device_id'])->where('active_slot',1)->lockForUpdate()->first();
     $result['code']='POS_SHIFT_CHANGED';
     if($shift&&$shift->shift_id===$lease->shift_id){
      $valid=true;$result['code']='POS_CATALOG_CHANGED';$total=0;
      foreach($op['lines']as$l){$old=$catalog[$l['product_id']];$current=$this->db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$s['tenant_id'])->where('outlet_id',$s['outlet_id'])->where('product_id',$l['product_id'])->lockForUpdate()->first();if(!$current||!$current->active||(int)$current->unit_price_atomic!==$old['price']||$current->currency!==$old['currency']||(int)$current->currency_scale!==$old['scale']){$valid=false;break;}if($old['price']>intdiv(9007199254740991,$l['quantity'])||$total>9007199254740991-$old['price']*$l['quantity'])throw new InvalidArgumentException();$total+=$old['price']*$l['quantity'];if((int)$current->available_quantity<$l['quantity']){$valid=false;$result['code']='POS_STOCK_CHANGED';break;}}
      if($valid){
       if($op['tendered']<$total||$op['tendered']>9007199254740991)throw new InvalidArgumentException();
       $first=$catalog[$op['lines'][0]['product_id']];$receipt=$this->sales->complete(new SaleCommand($op['operation_id'],Cart::fromLines(array_map(fn($l)=>new CartLine(ProductId::fromString($l['product_id']),$l['quantity']),$op['lines'])),TenderCategory::CASH,Money::fromAtomicUnits($op['tendered'],$first['currency'],$first['scale']),$correlation));
       $result=['operation_id'=>$op['operation_id'],'state'=>'accepted','code'=>'POS_SYNC_ACCEPTED','sale_id'=>$receipt->saleId(),'total'=>$receipt->total()->atomicUnits(),'change'=>$receipt->changeAmount()->atomicUnits(),'correlation_id'=>$correlation];
      }
     }
    }
   }
   $this->db->table('oneqay_desktop_operations')->insert(['tenant_id'=>$s['tenant_id'],'operation_id'=>$op['operation_id'],'scope_hash'=>$this->scopeHash(),'fingerprint'=>$fingerprint,'organization_id'=>$s['organization_id'],'outlet_id'=>$s['outlet_id'],'device_id'=>$s['device_id'],'identity_id'=>$s['identity_id'],'kind'=>$op['kind'],'state'=>$result['state'],'result'=>json_encode($result,JSON_THROW_ON_ERROR),'payload'=>json_encode($op,JSON_THROW_ON_ERROR),'created_at'=>time()]);return $result;
  });
 }
 public function review():array {$this->authorization->require($this->context(),PosPermission::inventoryBaseline());return $this->db->table('oneqay_desktop_operations')->where('tenant_id',$this->scope()['tenant_id'])->where('organization_id',$this->scope()['organization_id'])->where('outlet_id',$this->scope()['outlet_id'])->whereIn('state',['review','conflict'])->orderByDesc('created_at')->limit(200)->get()->map(fn($r)=>['operation_id'=>$r->operation_id,'device_id'=>$r->device_id,'identity_id'=>$r->identity_id,'kind'=>$r->kind,'state'=>$r->state,'result'=>json_decode($r->result,true),'payload'=>json_decode($r->payload,true)])->all();}
}
