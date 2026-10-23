<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\{PlatformStore, PlatformViolation};
use Illuminate\Database\Connection;
// Author by Lab | zefry
final readonly class LaravelPlatformStore implements PlatformStore
{
    public function __construct(private Connection $db) {}
    public function transaction(callable $work): mixed { return $this->db->transaction($work); }
    public function once(string $actor, string $operation, array $input, callable $work): array
    {
        $id = hash('sha256', $actor.'|'.$operation);
        $fingerprint = hash('sha256', json_encode($input, JSON_THROW_ON_ERROR));
        return $this->transaction(function () use ($id, $fingerprint, $work): array {
            // Shared owner control row also serializes create/idempotency races on MySQL.
            $this->db->table('oneqay_mail_settings')->where('id',1)->lockForUpdate()->firstOrFail();
            $prior = $this->db->table('oneqay_platform_operations')->where('id',$id)->first();
            if ($prior !== null) {
                if (!hash_equals($prior->fingerprint, $fingerprint)) throw new PlatformViolation('OPERATION_CONFLICT',409);
                return json_decode($prior->result,true,32,JSON_THROW_ON_ERROR);
            }
            $result = $work();
            $this->db->table('oneqay_platform_operations')->insert(['id'=>$id,'fingerprint'=>$fingerprint,'result'=>json_encode($result,JSON_THROW_ON_ERROR)]);
            return $result;
        });
    }
    public function create(array $business): array { (new \App\Infrastructure\Identity\LaravelAccountEmailRegistry($this->db))->requireUnused($business['email']); $business['email']=strtolower(trim($business['email'])); $this->db->table('oneqay_platform_businesses')->insert($business); return $business; }
    public function business(string $id, bool $lock = false): array
    {
        $query = $this->db->table('oneqay_platform_businesses')->where('id',$id);
        $row = ($lock ? $query->lockForUpdate() : $query)->first();
        if ($row === null) throw new PlatformViolation('BUSINESS_NOT_FOUND',404);
        return (array)$row;
    }
    public function loginBusiness(string $email, ?string $id): ?array
    {
        $q=$this->db->table('oneqay_platform_businesses')->where('email',strtolower($email))->where('status','active');
        if($id!==null && $id!=='')$q->where('id',$id);
        $rows=$q->limit(2)->get();
        if($rows->count()===1)return (array)$rows->first();
        if($rows->count()>1 || $this->db->table('oneqay_platform_businesses')->where('email',strtolower($email))->exists())return null;
        // Existing cashier credentials may enter the web POS for independent shift approval.
        // Canonical login still enforces eligibility, credential epochs, MFA and outlet RBAC.
        if(!$this->db->getSchemaBuilder()->hasTable('oneqay_desktop_cashiers'))return null;
        $cashier=$this->db->table('oneqay_desktop_cashiers')->where('login',strtolower($email))->where('enabled',true)->first();
        if(!$cashier || !$this->tenantAllowed($cashier->tenant_id))return null;
        if($id!==null && $id!=='' && !$this->db->table('oneqay_platform_businesses')->where('id',$id)->where('tenant_id',$cashier->tenant_id)->where('status','active')->exists())return null;
        return array_intersect_key((array)$cashier,array_flip(['tenant_id','identity_id','organization_id','outlet_id','device_id']));
    }
    public function byToken(string $tokenHash): array
    {
        $row = $this->db->table('oneqay_platform_businesses')->where('token_hash',$tokenHash)->lockForUpdate()->first();
        if ($row === null) throw new PlatformViolation('INVALID_ACTIVATION');
        return (array)$row;
    }
    public function update(string $id, int $version, array $changes): array
    {
        if ($this->db->table('oneqay_platform_businesses')->where('id',$id)->where('version',$version)->update($changes + ['version'=>$version+1]) !== 1) throw new PlatformViolation('STALE_VERSION',409);
        return $this->business($id);
    }
    public function listing(int $page, string $search): array
    {
        $query = $this->db->table('oneqay_platform_businesses');
        if ($search !== '') $query->where('name','like','%'.str_replace(['%','_'],['\\%','\\_'],substr($search,0,160)).'%');
        return ['total'=>(clone $query)->count(), 'rows'=>$query->orderByDesc('created_at')->orderBy('id')->offset((max(1,$page)-1)*25)->limit(25)->get(['id','name','email','locale','status','version','tenant_id','identity_id','created_at','updated_at'])->map(fn($r)=>(array)$r)->all(),
            'audit'=>$this->db->table('oneqay_platform_audit')->orderByDesc('id')->limit(30)->get()->map(fn($r)=>(array)$r)->all()];
    }
    public function audit(string $actor, string $action, string $business, string $reason, int $now): void
    { $this->db->table('oneqay_platform_audit')->insert(['actor'=>$actor,'action'=>$action,'business_id'=>$business,'reason'=>$reason,'occurred_at'=>$now]); }
    public function tenantAllowed(string $tenant): bool
    {
        $state = $this->db->table('oneqay_platform_businesses')->where('tenant_id',$tenant)->value('status');
        // Existing tenants retain their canonical eligibility. All newly managed tenants are explicit.
        return $state === null || $state === 'active';
    }
}
