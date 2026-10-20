<?php
declare(strict_types=1);
namespace App\Infrastructure\Pos;
use App\Application\Pos\{PosExecutionContext,ShiftClosureWorkspaceRepository};
use Illuminate\Database\Connection;
// Author by Lab | zefry. Exact device scope; no updates, guessed cash balance or role mutation.
final readonly class LaravelShiftClosureWorkspaceRepository implements ShiftClosureWorkspaceRepository
{
    public function __construct(private Connection $db) {}
    public function read(PosExecutionContext $context): ?array
    {
        $shift=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$context->tenantId())->where('organization_id',$context->organizationId())->where('outlet_id',$context->outletId())->where('device_id',$context->deviceId())->where('active_slot',1)->first();
        if(!$shift)return null;
        $evidence=fn(string $table)=>$this->db->table($table)->where('tenant_id',$context->tenantId())->where('organization_id',$context->organizationId())->where('outlet_id',$context->outletId())->where('device_id',$context->deviceId())->where('shift_id',$shift->shift_id)->first();
        $opening=$evidence('oneqay_pos_shift_opening_cash_evidence');$closing=$evidence('oneqay_pos_shift_closing_cash_evidence');
        return ['id'=>$shift->shift_id,'opened_at'=>(int)$shift->opened_at_unix,'opened_by_you'=>hash_equals($shift->actor_identity_id,$context->actorId()),'opening_cash'=>$opening?(string)$opening->opening_cash_atomic:null,'currency'=>$opening?->currency,'scale'=>$opening?(int)$opening->currency_scale:0,'closing_cash'=>$closing?(string)$closing->closing_cash_atomic:null,'closing_recorded_at'=>$closing?(int)$closing->recorded_at_unix:null];
    }
}
