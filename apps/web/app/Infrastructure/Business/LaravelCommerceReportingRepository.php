<?php
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{BusinessViolation,CommerceReportingRepository};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Correction evidence matches the canonical POS summary.
final readonly class LaravelCommerceReportingRepository implements CommerceReportingRepository {
    public function __construct(private Connection $db){}
    public function sales(VerifiedOrganizationalContext $s,string $from,string $to):array {
        $zone=new \DateTimeZone('Asia/Jakarta');$start=(new \DateTimeImmutable($from.' 00:00:00',$zone))->getTimestamp();$end=(new \DateTimeImmutable($to.' 00:00:00',$zone))->modify('+1 day')->getTimestamp();
        $rows=$this->db->table('oneqay_pos_sales as s')->leftJoin('oneqay_pos_sale_voids as v',fn($j)=>$j->on('v.tenant_id','=','s.tenant_id')->on('v.sale_id','=','s.sale_id'))->leftJoin('oneqay_pos_sale_cash_refunds as r',fn($j)=>$j->on('r.tenant_id','=','s.tenant_id')->on('r.sale_id','=','s.sale_id'))
            ->where('s.tenant_id',$s->tenantId()->value())->where('s.organization_id',$s->organizationId()->value())->where('s.outlet_id',$s->outletId()?->value())->where('s.completed_at_unix','>=',$start)->where('s.completed_at_unix','<',$end)->orderBy('s.completed_at_unix')->orderBy('s.sale_id')->limit(10001)->get(['s.sale_id','s.completed_at_unix','s.total_atomic','s.currency','s.currency_scale','s.tender_category','v.void_id','r.refund_id','r.refunded_atomic'])->all();
        if(count($rows)>10000)throw new BusinessViolation('REPORT_ROW_LIMIT_SELECT_SMALLER_PERIOD',413);
        return array_map(static function($r)use($zone):array {$v=(array)$r;$v['status']=$r->refund_id!==null?'REFUNDED':($r->void_id!==null?'VOIDED':'COMPLETED');$v['date']=(new \DateTimeImmutable('@'.$r->completed_at_unix))->setTimezone($zone)->format('Y-m-d H:i:s');$v['total_atomic']=(string)$r->total_atomic;$v['refunded_atomic']=(string)($r->refunded_atomic??0);$v['active_atomic']=$r->void_id===null?(string)$r->total_atomic:'0';unset($v['void_id'],$v['refund_id']);return $v;},$rows);
    }
}
