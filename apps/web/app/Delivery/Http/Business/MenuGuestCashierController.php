<?php
// Author by Lab | zefry. Verified cashier context required by routing and both services.
declare(strict_types=1);

namespace App\Delivery\Http\Business;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Business\{BusinessInput,BusinessViolation};
use App\Application\Pos\PosTransactionViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Business\{LaravelMenuGuestOrders,LaravelMenuGuestCheckout};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

final readonly class MenuGuestCashierController
{
    public function __construct(
        private LaravelMenuGuestOrders $orders,
        private LaravelMenuGuestCheckout $checkout,
    ) {}

    private function correlation(Request $r): string
    {
        return (string)$r->attributes->get('oneqay.correlation_id','correlation-missing');
    }

    private function respond(Request $r,callable $work): mixed
    {
        $headers=['Cache-Control'=>'no-store, private','Referrer-Policy'=>'no-referrer'];
        try {
            $result=$work();
            if(method_exists($result,'toResponse')) $result=$result->toResponse($r);
            $result->headers->set('Cache-Control','no-store, private');
            $result->headers->set('Referrer-Policy','no-referrer');
            return $result;
        } catch (DurableAuthorizationViolation) {
            return response()->json(SafeErrorEnvelope::make('ORDER_CASHIER_DENIED',$this->correlation($r)),403,$headers);
        } catch (BusinessViolation $e) {
            return response()->json(SafeErrorEnvelope::make($e->reason,$this->correlation($r)),$e->status,$headers);
        } catch (PosTransactionViolation) {
            return response()->json(SafeErrorEnvelope::make('ORDER_POS_SALE_REJECTED',$this->correlation($r)),409,$headers);
        } catch (QueryException) {
            return response()->json(SafeErrorEnvelope::make('ORDER_STORAGE_UNAVAILABLE',$this->correlation($r)),503,$headers);
        }
    }

    private function payload(Request $r): array
    {
        if(strlen($r->getContent())>16384)throw new BusinessViolation('REQUEST_TOO_LARGE',413);
        return $r->except('_token');
    }

    public function page(Request $r):mixed
    {
        return $this->respond($r,function() {
            // Refuse to render customer-contact workflows without durable cashier permission.
            $this->orders->findByPhone('+628123456789');
            return Inertia::render('Business/MenuGuestOrders',[
                'settlement_enabled'=>(bool)config('oneqay.pos_sale_completion.enabled',false),
            ]);
        });
    }

    public function lookup(Request $r):mixed
    {
        return $this->respond($r,function()use($r) {
            $kind=BusinessInput::text($r->query('kind'),8);
            if($kind==='qr') {
                $token=BusinessInput::text($r->query('token'),64);
                return response()->json(['order'=>$this->orders->findByQr($token)]);
            }
            if($kind==='phone') {
                $phone=BusinessInput::text($r->query('phone'),17);
                return response()->json(['orders'=>$this->orders->findByPhone($phone)]);
            }
            throw new BusinessViolation('ORDER_LOOKUP_INVALID');
        });
    }

    public function quote(Request $r):mixed
    {
        return $this->respond($r,function()use($r) {
            $p=$this->payload($r);
            BusinessInput::fields($p,['order_id'],['selected_lines']);
            $id=BusinessInput::text($p['order_id'],32);
            return response()->json(['quote'=>$this->checkout->quote($id,$p['selected_lines']??null)]);
        });
    }

    public function settle(Request $r):mixed
    {
        return $this->respond($r,fn()=>response()->json([
            'settlement'=>$this->checkout->settle($this->payload($r),$this->correlation($r))
        ]));
    }

    public function cancel(Request $r):mixed
    {
        return $this->respond($r,function()use($r) {
            $p=$this->payload($r);
            BusinessInput::fields($p,['order_id','operation_id']);
            return response()->json(['cancellation'=>$this->checkout->cancel(
                BusinessInput::text($p['order_id'],32),BusinessInput::id($p['operation_id'])
            )]);
        });
    }
}
