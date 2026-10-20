<?php
// Author by Lab | zefry. Job descriptions never grant permissions; POS access is a separate explicit choice.
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{WorkforceAccounts,BusinessInput,BusinessViolation,BusinessAccess};
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Infrastructure\Pos\LaravelDesktopBusinessSettings;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Container\Container;
final readonly class LaravelWorkforceAccounts implements WorkforceAccounts {
 private const JOBS=['Cashier'=>['Kasir','Cashier','Transaksi POS, kas awal/akhir dan struk.'], 'Manager'=>['Manajer','Manager','Koordinasi operasional dan tim.'], 'Accounting'=>['Akuntansi','Accounting','Pencatatan dan pemeriksaan keuangan.'], 'Warehouse'=>['Gudang','Warehouse','Penerimaan barang dan stok.'], 'Kitchen'=>['Dapur','Kitchen','Penyiapan pesanan.'], 'Sales'=>['Penjualan','Sales','Layanan pelanggan dan penjualan.']];
 public function __construct(private Connection $db,private Container $app){}
 public function jobs(VerifiedOrganizationalContext $s):array {
  $rows=[];foreach(self::JOBS as$name=>[$id,$en,$description])$rows[]=['id'=>$name,'name'=>$name,'label_id'=>$id,'label_en'=>$en,'description'=>$description,'builtin'=>true];
  foreach($this->db->table('oneqay_business_job_titles')->where('tenant_id',$s->tenantId()->value())->orderBy('name')->get()as$r)$rows[]=['id'=>$r->id,'name'=>$r->name,'label_id'=>$r->name,'label_en'=>$r->name,'description'=>$r->description,'builtin'=>false];return $rows;
 }
 public function saveJob(VerifiedOrganizationalContext $s,array $in):array {
  $this->app->make(BusinessAccess::class)->require('authorization.policy.manage');BusinessInput::fields($in,['name','description']);$name=BusinessInput::text($in['name'],120);$description=BusinessInput::text($in['description'],1000);
  foreach(self::JOBS as$default=>[$id,$en])if(strcasecmp($name,$default)===0||strcasecmp($name,$id)===0)throw new BusinessViolation('JOB_ALREADY_EXISTS',409);
  $tenant=$s->tenantId()->value();$this->db->table('oneqay_outlets')->where('tenant_id',$tenant)->where('id',$s->outletId()->value())->lockForUpdate()->firstOrFail();
  if($this->db->table('oneqay_business_job_titles')->where('tenant_id',$tenant)->where('name',$name)->exists())throw new BusinessViolation('JOB_ALREADY_EXISTS',409);
  if($this->db->table('oneqay_business_job_titles')->where('tenant_id',$tenant)->count()>=100)throw new BusinessViolation('JOB_LIMIT');$id=bin2hex(random_bytes(16));$this->db->table('oneqay_business_job_titles')->insert(['tenant_id'=>$tenant,'id'=>$id,'name'=>$name,'description'=>$description,'created_at'=>time()]);return ['id'=>$id,'name'=>$name];
 }
 public function accounts(VerifiedOrganizationalContext $s):array {
  // Passwords and PIN verifiers are never included in the employee workspace.
  return $this->db->table('oneqay_business_staff_accounts as a')->join('oneqay_desktop_cashiers as c',function($j){$j->on('c.tenant_id','=','a.tenant_id')->on('c.identity_id','=','a.identity_id');})->where('a.tenant_id',$s->tenantId()->value())->where('c.organization_id',$s->organizationId()->value())->where('c.outlet_id',$s->outletId()->value())->get(['a.employee_id','c.login','c.enabled','c.version'])->keyBy('employee_id')->map(fn($r)=>(array)$r)->all();
 }
 public function sync(VerifiedOrganizationalContext $s,array $e,?array $account):void {
  $linked=$this->accounts($s)[$e['id']]??null;
  if($account===null){if(!$linked)return;$account=['login'=>$linked['login'],'enabled'=>(bool)$linked['enabled'],'version'=>(int)$linked['version'],'password'=>'','pin'=>''];}
  $this->app->make(BusinessAccess::class)->require('authorization.policy.manage');
  BusinessInput::fields($account,['login','enabled','version','password','pin']);
  if($linked&&strtolower(trim((string)$account['login']))!==$linked['login'])throw new BusinessViolation('STAFF_LOGIN_IMMUTABLE',409);
  $account['enabled']=BusinessInput::boolean($account['enabled']) && (bool)$e['active'];
  $this->app->make(LaravelDesktopBusinessSettings::class)->cashier($account+['name'=>$e['name'],'employee_id'=>$e['id'],'operation_id'=>'staff-'.hash('sha256',$e['id'].'|'.$e['version'])]);
 }
 public function linkCashier(VerifiedOrganizationalContext $s,string $login,string $identity,string $name,bool $enabled,?string $employeeId):void {
  $tenant=$s->tenantId()->value();$q=$this->db->table('oneqay_business_employees')->where('tenant_id',$tenant)->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()->value());
  $link=$this->db->table('oneqay_business_staff_accounts')->where('tenant_id',$tenant)->where('identity_id',$identity)->first();
  if($link){if($employeeId!==null&&$employeeId!==$link->employee_id)throw new BusinessViolation('STAFF_ALREADY_LINKED',409);$employeeId=$link->employee_id;}
  if($employeeId!==null){$employee=(clone$q)->where('id',$employeeId)->lockForUpdate()->first();if(!$employee)throw new BusinessViolation('RECORD_NOT_FOUND',404);if($enabled&&!$employee->active)throw new BusinessViolation('EMPLOYEE_NOT_ACTIVE');}
  else {$employeeId=bin2hex(random_bytes(16));$q->insert(['tenant_id'=>$tenant,'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value(),'id'=>$employeeId,'code'=>'POS-'.substr($identity,-16),'name'=>$name,'department'=>'Operations','job_title'=>'Cashier','start_date'=>gmdate('Y-m-d'),'active'=>$enabled,'created_by'=>$s->identityId()->value(),'version'=>1,'created_at_unix'=>time(),'updated_at_unix'=>time()]);}
  if(!$link)$this->db->table('oneqay_business_staff_accounts')->insert(['tenant_id'=>$tenant,'employee_id'=>$employeeId,'identity_id'=>$identity,'login'=>$login]);
 }
}
