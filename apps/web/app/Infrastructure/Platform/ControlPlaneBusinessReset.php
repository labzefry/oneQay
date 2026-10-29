<?php
// Author by Lab | zefry. Explicit owner-directed data maintenance, never a migration rollback.
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\PlatformViolation;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Encryption\Encrypter;
final readonly class ControlPlaneBusinessReset {
 private const GROUPS=[
  'transactions'=>['oneqay_pos_shift_close_evidence','oneqay_pos_cash_variance_review_decision_evidence','oneqay_pos_cash_variance_explanation_evidence','oneqay_pos_shift_closing_cash_evidence','oneqay_pos_shift_opening_cash_evidence','oneqay_pos_sale_cash_refunds','oneqay_pos_sale_voids','oneqay_pos_sale_events','oneqay_pos_sale_lines','oneqay_pos_sales','oneqay_pos_shifts','oneqay_desktop_operations','oneqay_desktop_events','oneqay_pos_shift_recoveries'],
  'products'=>['oneqay_menu_book_items','oneqay_menu_book_categories','oneqay_business_product_profiles','oneqay_business_product_categories','oneqay_pos_inventory_replenishments','oneqay_pos_inventory_baselines','oneqay_pos_sale_catalog_items','oneqay_pos_catalog_preparation_journal'],
  'inventory'=>['oneqay_pos_inventory_replenishments','oneqay_pos_inventory_baselines'],
  'finance'=>['oneqay_business_settlements','oneqay_business_invoices','oneqay_business_receipts','oneqay_business_purchases','oneqay_business_journals'],
  'partners'=>['oneqay_business_interactions','oneqay_business_partners'],
  'people'=>['oneqay_business_attendance','oneqay_business_leave','oneqay_business_staff_accounts','oneqay_business_employees','oneqay_business_job_titles'],
  'settings'=>['oneqay_menu_book_publication','oneqay_desktop_preferences','oneqay_business_settings'],
 ];
 public function __construct(private Connection $db,private Encrypter $cipher){}
 private function tables(string $scope):array {
  if($scope!=='all'&&!isset(self::GROUPS[$scope]))throw new PlatformViolation('RESET_SCOPE_INVALID');
  $names=$scope==='all'?array_merge(...array_values(self::GROUPS)):self::GROUPS[$scope];
  if($scope==='all')$names[]='oneqay_business_events';
  foreach(['laundry','boarding','homestay']as$module){if(in_array($scope,['transactions','all'],true))foreach(['payments','invoices','orders','shifts']as$table)$names[]='oneqay_'.$module.'_'.$table;if(in_array($scope,['products','all'],true))$names[]='oneqay_'.$module.'_resources';}
  // Every reset invalidates old offline leases. Payloads on Windows are retained for reconciliation.
  $names[]='oneqay_desktop_leases';
  return array_values(array_filter(array_unique($names),fn($name)=>$this->db->getSchemaBuilder()->hasTable($name)));
 }
 private function rows(string $table,string $tenant,bool $lock=false):\Illuminate\Database\Query\Builder {
  $q=$this->db->table($table)->where('tenant_id',$tenant);$keys=[];
  foreach($this->db->getSchemaBuilder()->getIndexes($table)as$index)if($index['primary'])$keys=$index['columns'];
  if(!$keys)throw new PlatformViolation('RESET_SCHEMA_UNSUPPORTED',409);
  foreach($keys as$key)$q->orderBy($key);if($lock)$q->lockForUpdate();return $q;
 }
 public function preview(array $business,string $scope,bool $lock=false):array {
  $tenant=$business['tenant_id'];$tables=$this->tables($scope);$entries=[];$hash=hash_init('sha256');
  foreach($tables as$table){$n=0;hash_update($hash,$table."\n");foreach($this->rows($table,$tenant,$lock)->cursor()as$row){$a=(array)$row;ksort($a);hash_update($hash,json_encode($a,JSON_THROW_ON_ERROR)."\n");++$n;}$entries[]=['table'=>$table,'rows'=>$n,'action'=>'delete'];}
  if($scope==='inventory'){
   $q=$this->rows('oneqay_pos_sale_catalog_items',$tenant,$lock);$n=0;foreach($q->cursor()as$row){hash_update($hash,json_encode((array)$row,JSON_THROW_ON_ERROR)."\n");++$n;}$entries[]=['table'=>'oneqay_pos_sale_catalog_items','rows'=>$n,'action'=>'zero_tracked_stock'];
  }
  $blocked=[];
  if($business['status']!=='suspended')$blocked[]='RESET_SUSPEND_BUSINESS_FIRST';
  if($business['status']==='suspended'&&(int)$business['updated_at']>time()-60)$blocked[]='RESET_WAIT_FOR_REQUESTS_TO_DRAIN';
  if($this->db->table('oneqay_pos_shifts')->where('tenant_id',$tenant)->where('active_slot',1)->exists())$blocked[]='RESET_CLOSE_OR_RECOVER_ACTIVE_SHIFTS';
  foreach(['laundry','boarding','homestay']as$module){if($this->db->getSchemaBuilder()->hasTable('oneqay_'.$module.'_shifts')&&$this->db->table('oneqay_'.$module.'_shifts')->where('tenant_id',$tenant)->where('active_slot',1)->exists())$blocked[]='RESET_CLOSE_OR_RECOVER_ACTIVE_SHIFTS';if($scope==='products'&&$this->db->getSchemaBuilder()->hasTable('oneqay_'.$module.'_orders')&&$this->db->table('oneqay_'.$module.'_orders')->where('tenant_id',$tenant)->exists())$blocked[]='RESET_TRANSACTIONS_DEPEND_ON_SERVICE_RESOURCES';}
  if($scope==='partners'&&$this->db->getSchemaBuilder()->hasTable('oneqay_business_purchases')&&($this->db->table('oneqay_business_purchases')->where('tenant_id',$tenant)->exists()||$this->db->table('oneqay_business_invoices')->where('tenant_id',$tenant)->exists()))$blocked[]='RESET_FINANCE_DEPENDS_ON_PARTNERS';
  $state=['business_id'=>$business['id'],'tenant_id'=>$tenant,'version'=>(int)$business['version'],'scope'=>$scope,'entries'=>$entries,'data_digest'=>hash_final($hash),'blocked'=>$blocked];
  return $state+['review_digest'=>hash('sha256',json_encode($state,JSON_THROW_ON_ERROR)),'preserved'=>['business account','identities and credentials','RBAC and TOTP','platform audit and reset receipts','SMTP and website configuration'],'offline_action'=>'Stop all registers and reconcile or export local queues before reset. Old leases are invalidated; local queues are never deleted.'];
 }
 public function execute(array $business,array $in,string $actor):array {
  return $this->db->transaction(function()use($business,$in,$actor):array {
   if(($in['reviewed']??false)!==true||($in['offline_reconciled']??false)!==true||($in['backup_acknowledged']??false)!==true||($in['confirmation']??'')!=='RESET '.$business['id'])throw new PlatformViolation('RESET_CONFIRMATION_REQUIRED');
   if(!is_string($in['scope']??null)||!is_string($in['review_digest']??null)||mb_strlen(trim($in['reason']??''))<12)throw new PlatformViolation('RESET_REVIEW_REQUIRED');
   foreach($this->db->table('oneqay_devices')->where('tenant_id',$business['tenant_id'])->orderBy('id')->lockForUpdate()->get(['id'])as$_){}
   $plan=$this->preview($business,$in['scope'],true);
   if($plan['blocked'])throw new PlatformViolation($plan['blocked'][0],409);
   if(!hash_equals($plan['review_digest'],$in['review_digest']))throw new PlatformViolation('RESET_PREVIEW_STALE',409);
   $ref=hash('sha256',$actor.'|'.$in['operation_id']);$dir=(string)config('platform.reset_backup_path');if($dir===''||$dir[0]!=='/'||(config('app.env')==='production'&&str_starts_with($dir,rtrim(base_path(),'/').'/')))throw new PlatformViolation('RESET_PRIVATE_SHARED_BACKUP_PATH_REQUIRED',503);
   if(is_link($dir)||(!is_dir($dir)&&!mkdir($dir,0700,true)))throw new PlatformViolation('RESET_BACKUP_UNAVAILABLE',503);
   chmod($dir,0700);$path=$dir.'/'.$ref.'.jsonl.enc';$file=@fopen($path,'x+b');if(!$file)throw new PlatformViolation('RESET_BACKUP_UNAVAILABLE',503);chmod($path,0600);
   try{
    $write=function(array $record)use($file):void{$line=$this->cipher->encryptString(json_encode($record,JSON_THROW_ON_ERROR))."\n";if(fwrite($file,$line)!==strlen($line))throw new PlatformViolation('RESET_BACKUP_WRITE_FAILED',503);};
    $write(['format'=>'oneqay-business-reset-v1','actor'=>$actor,'at'=>time(),'plan'=>$plan]);
    foreach($plan['entries']as$entry)foreach($this->rows($entry['table'],$business['tenant_id'])->cursor()as$row)$write(['table'=>$entry['table'],'row'=>(array)$row]);
    if(!fflush($file)||(function_exists('fsync')&&!fsync($file)))throw new PlatformViolation('RESET_BACKUP_WRITE_FAILED',503);fclose($file);$file=null;
    $backupHash=hash_file('sha256',$path);
    // Break only the selected tenant's self-reference before deleting its journal rows.
    if(in_array($in['scope'],['finance','all'],true)&&$this->db->getSchemaBuilder()->hasTable('oneqay_business_journals'))$this->db->table('oneqay_business_journals')->where('tenant_id',$business['tenant_id'])->update(['reversal_of'=>null]);
    $deleted=[];foreach($plan['entries']as$entry){$q=$this->db->table($entry['table'])->where('tenant_id',$business['tenant_id']);$deleted[$entry['table']]=$entry['action']==='delete'?$q->delete():$q->where('tracks_stock',true)->update(['available_quantity'=>0]);}
    $this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$business['tenant_id'])->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>time()]);
    return ['business_id'=>$business['id'],'scope'=>$in['scope'],'affected'=>$deleted,'backup_reference'=>$ref,'backup_sha256'=>$backupHash,'status'=>'reset','account_status'=>'suspended'];
   }catch(\Throwable $error){if(is_resource($file))fclose($file);@unlink($path);throw $error;}
  });
 }
}
