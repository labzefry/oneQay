<?php
// Author by Lab | zefry. Every query is bound to verified tenant/org/outlet; each vertical owns its tables.
declare(strict_types=1);
namespace App\Infrastructure\BusinessExperience;
use App\Application\BusinessExperience\{BusinessDomain,ServiceWorkflow as W};
use App\Application\Business\BusinessAccess;
use App\Application\Platform\{PlatformStore,PlatformViolation};
use Illuminate\Database\Connection;
final readonly class LaravelServicePos {
 public function __construct(private Connection $db,private BusinessAccess $access,private PlatformStore $operations,private LaravelBusinessDomains $domains){}
 private function localDate(string $tenant):string{return (new \DateTimeImmutable('now',new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');}
 public function type():string{$c=$this->access->context();$p=$this->domains->profile($c->tenantId()->value());$type=$p['business_type']??'retail';if(!in_array($type,W::MODULES,true))throw new PlatformViolation('BUSINESS_WORKFLOW_MISMATCH',403);return $type;}
 private function table(string $name):string{return 'oneqay_'.$this->type().'_'.$name;}
 private function query(string $name):\Illuminate\Database\Query\Builder{$c=$this->access->context();return $this->db->table($this->table($name))->where('tenant_id',$c->tenantId()->value())->where('organization_id',$c->organizationId()->value())->where('outlet_id',$c->outletId()->value());}
 public function read():array {
  $c=$this->access->require('pos.sale.complete');$type=$this->type();$money=app(\App\Infrastructure\Business\LaravelMerchandisingRepository::class)->settings($c);$orders=$this->query('orders')->orderByDesc('created_at')->limit(100)->get()->map(fn($r)=>(array)$r)->all();$ids=array_column($orders,'id');
  $invoices=$this->db->table($this->table('invoices'))->where('tenant_id',$c->tenantId()->value())->whereIn('order_id',$ids)->get()->map(fn($r)=>(array)$r)->all();$shift=$this->query('shifts')->where('device_id',$c->deviceId()->value())->where('active_slot',1)->first();
  return ['currency'=>$money['currency'],'scale'=>(int)$money['scale'],'symbol'=>$money['symbol'],'type'=>$type,'profile'=>$this->domains->profile($c->tenantId()->value()),'resources'=>$this->query('resources')->orderBy('name')->limit(500)->get()->map(fn($r)=>(array)$r)->all(),'orders'=>$orders,'invoices'=>$invoices,'shift'=>$shift?(array)$shift:null,'can_configure'=>$this->access->allows('authorization.policy.manage'),'server_time'=>time(),'scope'=>['tenant_id'=>$c->tenantId()->value(),'organization_id'=>$c->organizationId()->value(),'outlet_id'=>$c->outletId()->value()]];
 }
 public function command(array $in):array {
  $c=$this->access->require('pos.sale.complete');$type=$this->type();$action=$in['action']??'';$op=$in['operation_id']??'';
  if(!is_string($op)||!preg_match('/\A[a-zA-Z0-9_-]{8,128}\z/D',$op)||!is_string($action)||strlen(json_encode($in,JSON_THROW_ON_ERROR))>8192)throw new PlatformViolation('INVALID_OPERATION');
  $fields=match($action){'resource.save'=>['id','version','name','unit','rate_atomic','currency','scale','enabled'],'order.create'=>['resource_id','customer_name','contact','start_date','end_date','quantity_milli'],'order.transition'=>['id','version','status'],'invoice.create'=>['id','version','period'],'payment.record'=>['invoice_id','amount_atomic'],'shift.open'=>['opening_atomic'],'shift.close'=>['closing_atomic'],default=>throw new PlatformViolation('INVALID_ACTION')};
  if(array_diff(array_keys($in),[...$fields,'action','operation_id']))throw new PlatformViolation('INVALID_FIELDS');
  if($action==='resource.save')$this->access->require('authorization.policy.manage');
  return $this->operations->once($c->tenantId()->value().'/'.$c->identityId()->value(),$op,['type'=>$type,'organization'=>$c->organizationId()->value(),'outlet'=>$c->outletId()->value(),'device'=>$c->deviceId()->value(),'input'=>$in],function()use($in,$op,$action,$type,$c){
   // Device lock serializes register operations; unit row lock also serializes reservations across registers.
   $this->db->table('oneqay_devices')->where('tenant_id',$c->tenantId()->value())->where('id',$c->deviceId()->value())->lockForUpdate()->firstOrFail();
   $scope=['tenant_id'=>$c->tenantId()->value(),'organization_id'=>$c->organizationId()->value(),'outlet_id'=>$c->outletId()->value()];$id=bin2hex(random_bytes(16));
   if($action==='resource.save'){
    $name=W::text($in['name']??null,100);$rate=W::amount($in['rate_atomic']??null);$unit=$type==='laundry'?($in['unit']??''):($type==='boarding'?'month':'night');if(!in_array($unit,['kg','piece','month','night'],true)||($type==='laundry'&&!in_array($unit,['kg','piece'],true))||!is_bool($in['enabled']??null)||!is_string($in['currency']??null)||!preg_match('/\A[A-Z]{3}\z/D',$in['currency'])||!is_int($in['scale']??null)||$in['scale']<0||$in['scale']>4||!is_int($in['version']??null))throw new PlatformViolation('INVALID_FIELDS');
    $money=app(\App\Infrastructure\Business\LaravelMerchandisingRepository::class)->settings($c);if($in['currency']!==$money['currency']||$in['scale']!==(int)$money['scale'])throw new PlatformViolation('BUSINESS_CURRENCY_MISMATCH');
    $change=['name'=>$name,'unit'=>$unit,'rate_atomic'=>$rate,'currency'=>$in['currency'],'scale'=>$in['scale'],'enabled'=>$in['enabled'],'version'=>$in['version']+1];
    if($in['version']===0){$this->db->table($this->table('resources'))->insert(['id'=>$id]+$scope+$change);}else{$id=W::text($in['id']??null,32);if($this->query('resources')->where('id',$id)->where('version',$in['version'])->update($change)!==1)throw new PlatformViolation('STALE_VERSION',409);}return ['id'=>$id,'version'=>$change['version']];
   }
   $shift=$this->query('shifts')->where('device_id',$c->deviceId()->value())->where('active_slot',1)->lockForUpdate()->first();
   if($action==='shift.open'){if($shift)throw new PlatformViolation('SHIFT_ALREADY_OPEN',409);$this->db->table($this->table('shifts'))->insert(['id'=>$id]+$scope+['identity_id'=>$c->identityId()->value(),'device_id'=>$c->deviceId()->value(),'active_slot'=>1,'opening_atomic'=>W::amount($in['opening_atomic']??null,true),'closing_atomic'=>null,'opened_at'=>time(),'closed_at'=>null]);return ['id'=>$id];}
   if(in_array($action,['payment.record','shift.close'],true)&&(!$shift||$shift->identity_id!==$c->identityId()->value()))throw new PlatformViolation('OWN_SHIFT_REQUIRED',409);
   if($action==='shift.close'){$counted=W::amount($in['closing_atomic']??null,true);$received=(int)$this->db->table($this->table('payments'))->where('tenant_id',$scope['tenant_id'])->where('shift_id',$shift->id)->sum('amount_atomic');$this->query('shifts')->where('id',$shift->id)->update(['active_slot'=>null,'closing_atomic'=>$counted,'closed_at'=>time()]);return ['id'=>$shift->id,'opening_atomic'=>(int)$shift->opening_atomic,'received_atomic'=>$received,'expected_atomic'=>(int)$shift->opening_atomic+$received,'counted_atomic'=>$counted,'variance_atomic'=>$counted-(int)$shift->opening_atomic-$received,'cashier'=>$c->identityId()->value(),'closed_at'=>time()];}
   if($action==='order.create'){
    $resource=$this->query('resources')->where('id',W::text($in['resource_id']??null,32))->lockForUpdate()->first();if(!$resource||!$resource->enabled)throw new PlatformViolation('RESOURCE_UNAVAILABLE',409);
    $start=W::date($in['start_date']??null);$end=W::date($in['end_date']??null);$quantity=W::amount($in['quantity_milli']??1000);if($type!=='laundry'&&$quantity!==1000)throw new PlatformViolation('INVALID_QUANTITY');if($type==='laundry'&&$resource->unit==='piece'&&$quantity%1000!==0)throw new PlatformViolation('PIECE_QUANTITY_MUST_BE_WHOLE');
    $total=W::total($type,(int)$resource->rate_atomic,$quantity,$start,$end);W::amount($total);
    if($type!=='laundry'&&$this->query('orders')->where('resource_id',$resource->id)->whereIn('status',['reserved','checked_in'])->where('start_date','<',$end)->where('end_date','>',$start)->exists())throw new PlatformViolation('ROOM_ALREADY_RESERVED',409);
    $this->db->table($this->table('orders'))->insert(['id'=>$id]+$scope+['resource_id'=>$resource->id,'customer_name'=>W::text($in['customer_name']??null,100),'contact'=>W::text($in['contact']??null,32),'status'=>$type==='laundry'?'received':'reserved','start_date'=>$start,'end_date'=>$end,'quantity_milli'=>$quantity,'rate_atomic'=>$resource->rate_atomic,'total_atomic'=>$total,'currency'=>$resource->currency,'scale'=>$resource->scale,'version'=>1,'created_by'=>$c->identityId()->value(),'created_at'=>time()]);
    $this->db->table($this->table('invoices'))->insert(['id'=>bin2hex(random_bytes(16)),'tenant_id'=>$scope['tenant_id'],'order_id'=>$id,'period'=>$start,'total_atomic'=>$total,'paid_atomic'=>0]);return ['id'=>$id,'total_atomic'=>$total];
   }
   if($action==='payment.record'){
    $invoice=$this->db->table($this->table('invoices'))->where('tenant_id',$scope['tenant_id'])->where('id',W::text($in['invoice_id']??null,32))->lockForUpdate()->first();if(!$invoice||!$this->query('orders')->where('id',$invoice->order_id)->whereNotIn('status',['cancelled','collected','checked_out'])->exists())throw new PlatformViolation('INVOICE_UNAVAILABLE',409);$amount=W::amount($in['amount_atomic']??null);if($amount>(int)$invoice->total_atomic-(int)$invoice->paid_atomic)throw new PlatformViolation('PAYMENT_EXCEEDS_BALANCE',409);
    $this->db->table($this->table('invoices'))->where('id',$invoice->id)->where('tenant_id',$scope['tenant_id'])->increment('paid_atomic',$amount);$this->db->table($this->table('payments'))->insert(['id'=>$id,'tenant_id'=>$scope['tenant_id'],'invoice_id'=>$invoice->id,'shift_id'=>$shift->id,'amount_atomic'=>$amount,'occurred_at'=>time(),'actor'=>$c->identityId()->value()]);return ['id'=>$id,'amount_atomic'=>$amount,'invoice_id'=>$invoice->id];
   }
   $order=$this->query('orders')->where('id',W::text($in['id']??null,32))->lockForUpdate()->first();if(!$order||!is_int($in['version']??null)||(int)$order->version!==$in['version'])throw new PlatformViolation('STALE_VERSION',409);
   if($action==='invoice.create'){
    if($type!=='boarding'||!in_array($order->status,['reserved','checked_in'],true))throw new PlatformViolation('WORKFLOW_ACTION_DENIED',409);$period=W::date($in['period']??null);if($period<$order->start_date||$period>=$order->end_date)throw new PlatformViolation('INVALID_BILLING_PERIOD');
    $last=$this->db->table($this->table('invoices'))->where('tenant_id',$scope['tenant_id'])->where('order_id',$order->id)->max('period');if($period!==W::nextPeriod($last,$order->start_date))throw new PlatformViolation('NEXT_BILLING_PERIOD_REQUIRED');
    if($this->db->table($this->table('invoices'))->where('tenant_id',$scope['tenant_id'])->where('order_id',$order->id)->where('period',$period)->exists())throw new PlatformViolation('PERIOD_ALREADY_INVOICED',409);
    $this->db->table($this->table('invoices'))->insert(['id'=>$id,'tenant_id'=>$scope['tenant_id'],'order_id'=>$order->id,'period'=>$period,'total_atomic'=>$order->rate_atomic,'paid_atomic'=>0]);$this->query('orders')->where('id',$order->id)->increment('version');return ['id'=>$id];
   }
   $invoices=$this->db->table($this->table('invoices'))->where('tenant_id',$scope['tenant_id'])->where('order_id',$order->id)->get();$paid=$invoices->every(fn($i)=>(int)$i->paid_atomic===(int)$i->total_atomic);$status=W::text($in['status']??null,20);W::transition($type,$order->status,$status,$paid);
   if($status==='cancelled'&&$invoices->sum('paid_atomic')>0)throw new PlatformViolation('PAID_ORDER_CANNOT_CANCEL',409);
   if($type==='boarding'&&$status==='checked_out'){$next=W::nextPeriod($invoices->max('period'),$order->start_date);if($next<=$order->end_date&&$next<=$this->localDate($order->tenant_id)&&$next<$order->end_date)throw new PlatformViolation('RENTAL_PERIOD_INVOICE_REQUIRED',409);}
   if($status==='checked_in'&&$order->start_date>$this->localDate($order->tenant_id))throw new PlatformViolation('CHECK_IN_DATE_NOT_REACHED',409);
   $this->query('orders')->where('id',$order->id)->update(['status'=>$status,'version'=>$order->version+1]);return ['id'=>$order->id,'status'=>$status,'version'=>$order->version+1];
  });
 }
}
