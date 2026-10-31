<?php
// Author by Lab | zefry. A cashier-only, transactionally bound QR-to-canonical-POS-sale service.
declare(strict_types=1);

namespace App\Infrastructure\Business;

use App\Application\Business\{BusinessAccess,BusinessInput,BusinessViolation};
use App\Application\Pos\{CompleteSale,SaleCommand};
use App\Domain\Pos\{Cart,CartLine,Money,ProductId,TenderCategory};
use Illuminate\Database\Connection;
use InvalidArgumentException;
use ValueError;

final readonly class LaravelMenuGuestCheckout
{
    private const MAX_TOTAL = 1000000000000;
    private const MAX_ORDER_AGE = 86400;

    public function __construct(
        private Connection $db,
        private BusinessAccess $access,
        private CompleteSale $sales,
    ) {}

    private function scope(): array
    {
        $s=$this->access->require('pos.sale.complete');
        return [
            'tenant_id'=>$s->tenantId()->value(),
            'organization_id'=>$s->organizationId()->value(),
            'outlet_id'=>$s->outletId()->value(),
        ];
    }

    private function findLocked(array $scope, mixed $id): object
    {
        if (!is_string($id) || !preg_match('/\A[a-f0-9]{32}\z/D',$id)) {
            throw new BusinessViolation('ORDER_ID_INVALID');
        }
        $record=$this->db->table('oneqay_menu_guest_orders')->where($scope)->where('id',$id)
            ->lockForUpdate()->first();
        if (!$record) throw new BusinessViolation('ORDER_NOT_FOUND',404);
        return $record;
    }

    private function canonicalQuote(array $scope, object $order, mixed $selection): array
    {
        $stored=$this->db->table('oneqay_menu_guest_order_lines')
            ->where('tenant_id',$scope['tenant_id'])->where('order_id',$order->id)
            ->orderBy('line_no')->get(['product_id','quantity']);
        if ($stored->isEmpty() || $stored->count()>50) throw new BusinessViolation('ORDER_LINES_INVALID',409);
        $authorized=[];
        foreach ($stored as $line) {
            if (isset($authorized[$line->product_id])) throw new BusinessViolation('ORDER_LINES_INVALID',409);
            $authorized[$line->product_id]=(int)$line->quantity;
        }
        if ($selection===null) {
            $lines=[];
            foreach ($stored as $line) $lines[]=['product_id'=>$line->product_id,'quantity'=>(int)$line->quantity];
        } else {
            $lines=[];
            foreach (BusinessInput::rows($selection,50) as $line) {
                BusinessInput::fields($line,['product_id','quantity']);
                $id=BusinessInput::id($line['product_id']);
                $quantity=BusinessInput::integer($line['quantity'],1,99);
                if (!isset($authorized[$id]) || $quantity>$authorized[$id]) throw new BusinessViolation('ORDER_LINE_NOT_AUTHORIZED',409);
                if (isset($seen[$id])) throw new BusinessViolation('DUPLICATE_ORDER_PRODUCT');
                $seen[$id]=true;
                $lines[]=['product_id'=>$id,'quantity'=>$quantity];
            }
        }
        $ids=array_column($lines,'product_id');
        $flag=$this->db->getSchemaBuilder()->hasColumn('oneqay_pos_sale_catalog_items','tracks_stock');
        $columns=['product_id','unit_price_atomic','currency','currency_scale','available_quantity'];
        if ($flag) $columns[]='tracks_stock';
        $catalog=$this->db->table('oneqay_pos_sale_catalog_items')
            ->where('tenant_id',$scope['tenant_id'])->where('outlet_id',$scope['outlet_id'])
            ->where('active',true)->whereIn('product_id',$ids)->lockForUpdate()
            ->get($columns)->keyBy('product_id');
        $total=0;$currency=null;$scale=null;$cart=[];
        foreach ($lines as $line) {
            $row=$catalog->get($line['product_id']);
            if (!$row || ((!$flag || (bool)$row->tracks_stock) && (int)$row->available_quantity<$line['quantity'])) {
                throw new BusinessViolation('ORDER_PRODUCT_UNAVAILABLE',409);
            }
            $unit=BusinessInput::integer((string)$row->unit_price_atomic,0,self::MAX_TOTAL);
            $cur=BusinessInput::currency($row->currency);
            $sc=BusinessInput::integer((string)$row->currency_scale,0,6);
            if ($currency!==null && ($cur!==$currency || $sc!==$scale)) throw new BusinessViolation('ORDER_CURRENCY_MISMATCH',409);
            $currency=$cur;$scale=$sc;
            $sum=BusinessInput::multiply($unit,$line['quantity']);
            if ($sum>self::MAX_TOTAL-$total) throw new BusinessViolation('ORDER_TOTAL_TOO_LARGE',422);
            $total+=$sum;
            $cart[]=new CartLine(ProductId::fromString($line['product_id']),$line['quantity']);
        }
        if ($order->currency!==$currency || (int)$order->currency_scale!==$scale) {
            throw new BusinessViolation('ORDER_CURRENCY_CHANGED',409);
        }
        return ['total_atomic'=>$total,'currency'=>$currency,'currency_scale'=>$scale,'lines'=>$lines,'cart'=>Cart::fromLines($cart)];
    }

    /** Server quote for a cashier's explicit customer-visible total confirmation. Read-only. */
    public function quote(string $orderId, ?array $selectedLines=null): array
    {
        $scope=$this->scope();
        return $this->db->transaction(function()use($scope,$orderId,$selectedLines):array {
            $order=$this->findLocked($scope,$orderId);
            if ($order->status!=='pending' || $order->created_at<time()-self::MAX_ORDER_AGE) {
                throw new BusinessViolation('ORDER_NOT_PENDING',409);
            }
            $q=$this->canonicalQuote($scope,$order,$selectedLines);
            return ['order_id'=>$order->id,'status'=>'pending_unpaid','total_atomic'=>(string)$q['total_atomic'],
                'previous_total_atomic'=>(string)$order->total_atomic,'currency'=>$q['currency'],
                'currency_scale'=>$q['currency_scale'],'lines'=>$q['lines']];
        });
    }

    /** The only code path that may mark a guest order paid; sale and order commit atomically. */
    public function settle(array $input, string $correlationId): array
    {
        BusinessInput::fields($input,['order_id','operation_id','tender_category','tendered_atomic','expected_total_atomic'],['selected_lines']);
        $op=BusinessInput::id($input['operation_id']);
        $expected=BusinessInput::integer($input['expected_total_atomic'],0,self::MAX_TOTAL);
        $tendered=BusinessInput::integer($input['tendered_atomic'],0,self::MAX_TOTAL);
        $type=$input['tender_category'];
        if (!is_string($type) || !in_array($type,['CASH','MANUAL_EXTERNAL'],true)) throw new BusinessViolation('ORDER_TENDER_INVALID');
        $selected=$input['selected_lines']??null;
        // Pre-validate without reinterpreting or accepting monetary values from the basket.
        if ($selected!==null) foreach (BusinessInput::rows($selected,50) as $line) {
            BusinessInput::fields($line,['product_id','quantity']);
            BusinessInput::id($line['product_id']);BusinessInput::integer($line['quantity'],1,99);
        }
        if (!is_string($correlationId)||!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/D',$correlationId)) {
            throw new BusinessViolation('ORDER_CORRELATION_INVALID');
        }
        $fingerprint=hash('sha256',BusinessInput::fingerprint([
            'order_id'=>$input['order_id'],'op'=>$op,'tender_category'=>$type,
            'tendered'=>$tendered,'expected'=>$expected,'selected'=>$selected,
        ]));
        $scope=$this->scope();
        return $this->db->transaction(function()use($scope,$input,$op,$type,$tendered,$expected,$selected,$fingerprint,$correlationId):array {
            $order=$this->findLocked($scope,$input['order_id']);
            $opHash=hash('sha256',$op);
            if ($order->status==='paid') {
                if (hash_equals((string)$order->settlement_operation_hash,$opHash)
                    && hash_equals((string)$order->settlement_payload_hash,$fingerprint)
                    && is_string($order->sale_id) && $order->sale_id!=='') {
                    return ['status'=>'paid','order_id'=>$order->id,'sale_id'=>$order->sale_id,
                        'total_atomic'=>(string)$order->settled_total_atomic,'replayed'=>true];
                }
                throw new BusinessViolation('ORDER_ALREADY_PAID',409);
            }
            if ($order->status!=='pending' || (int)$order->created_at<time()-self::MAX_ORDER_AGE) {
                throw new BusinessViolation('ORDER_NOT_PENDING',409);
            }
            $quote=$this->canonicalQuote($scope,$order,$selected);
            if ($quote['total_atomic']!==$expected) throw new BusinessViolation('ORDER_PRICE_CHANGED',409);
            if ($tendered<$expected || ($type==='MANUAL_EXTERNAL' && $tendered!==$expected)) {
                throw new BusinessViolation('ORDER_TENDER_INVALID');
            }
            try {
                $command=new SaleCommand('qr-sale:'.$order->id,$quote['cart'],
                    TenderCategory::from($type),Money::fromAtomicUnits($tendered,$quote['currency'],$quote['currency_scale']),
                    $correlationId);
                // CompleteSale enforces POS cashier permission, active shift, stock and canonical price.
                $receipt=$this->sales->complete($command);
            } catch (InvalidArgumentException|ValueError) {
                throw new BusinessViolation('ORDER_SALE_COMMAND_INVALID');
            }
            // If price changed after quote validation, abort/rollback even a nested POS commit.
            if ($receipt->total()->atomicUnits()!==$expected || $receipt->total()->currency()!==$quote['currency']
                || $receipt->total()->scale()!==$quote['currency_scale']) {
                throw new BusinessViolation('ORDER_PRICE_CHANGED',409);
            }
            $changed=$this->db->table('oneqay_menu_guest_orders')->where($scope)->where('id',$order->id)
                ->where('status','pending')->update([
                    'status'=>'paid','settlement_operation_hash'=>$opHash,
                    'settlement_payload_hash'=>$fingerprint,'sale_id'=>$receipt->saleId(),
                    'settled_total_atomic'=>$receipt->total()->atomicUnits(),'settled_at'=>time(),
                ]);
            if ($changed!==1) throw new BusinessViolation('ORDER_STATE_CONFLICT',409);
            return ['status'=>'paid','order_id'=>$order->id,'sale_id'=>$receipt->saleId(),
                'total_atomic'=>(string)$receipt->total()->atomicUnits(),'replayed'=>false];
        },1);
    }

    /** Cashier may cancel an unpaid handoff only; irreversible paid sale follows the canonical POS void/refund. */
    public function cancel(string $orderId,string $operationId):array
    {
        $op=BusinessInput::id($operationId);
        $scope=$this->scope();
        return $this->db->transaction(function()use($scope,$orderId,$op):array {
            $order=$this->findLocked($scope,$orderId);
            $hash=hash('sha256',$op);
            if ($order->status==='cancelled' && hash_equals((string)$order->cancel_operation_hash,$hash)) {
                return ['order_id'=>$orderId,'status'=>'cancelled','replayed'=>true];
            }
            if ($order->status!=='pending') throw new BusinessViolation('ORDER_NOT_PENDING',409);
            $updated=$this->db->table('oneqay_menu_guest_orders')->where($scope)->where('id',$orderId)
                ->where('status','pending')->update(['status'=>'cancelled','cancel_operation_hash'=>$hash,'cancelled_at'=>time()]);
            if ($updated!==1) throw new BusinessViolation('ORDER_STATE_CONFLICT',409);
            return ['order_id'=>$orderId,'status'=>'cancelled','replayed'=>false];
        },1);
    }
}
