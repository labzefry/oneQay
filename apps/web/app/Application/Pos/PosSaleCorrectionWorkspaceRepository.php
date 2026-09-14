<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosSaleCorrectionWorkspaceRepository
{
    /**
     * @return list<array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   amount_atomic:int,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   original_device_id:string,
     *   state:string,
     *   shift_active:bool,
     *   void_id:?string,
     *   voided_at_unix:?int,
     *   refund_id:?string,
     *   refunded_at_unix:?int
     * }>
     */
    public function recent(PosExecutionContext $context): array;
}
