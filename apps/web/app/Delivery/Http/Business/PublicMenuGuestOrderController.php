<?php
// Author by Lab | zefry. Accepts guest orders only for a middleware-verified active business hostname.
declare(strict_types=1);

namespace App\Delivery\Http\Business;

use App\Application\Business\BusinessViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Business\LaravelMenuGuestOrders;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class PublicMenuGuestOrderController
{
    public function __construct(private LaravelMenuGuestOrders $orders) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlation=(string)$request->attributes->get('oneqay.correlation_id','correlation-missing');
        $headers=['Cache-Control'=>'no-store, private','Referrer-Policy'=>'no-referrer','X-Robots-Tag'=>'noindex'];
        // No request-supplied tenant/outlet, domain, price, coupon or payment intent is accepted.
        abort_unless((bool)config('oneqay.menu_guest_ordering.enabled',false)
            && config('platform.business_domains_enabled')===true,404);
        $host=$request->attributes->get('oneqay.host_business');
        abort_unless(is_array($host)&&($host['status']??null)==='active'
            && isset($host['tenant_id'],$host['organization_id'],$host['outlet_id']),404);

        try {
            if(strlen($request->getContent())>16384) throw new BusinessViolation('REQUEST_TOO_LARGE',413);
            $order=$this->orders->submit($host,$request->except('_token'));
            return response()->json(['order'=>$order],202,$headers);
        } catch (BusinessViolation $e) {
            return response()->json(SafeErrorEnvelope::make($e->reason,$correlation),$e->status,$headers);
        } catch (QueryException $e) {
            $conflict=(string)($e->errorInfo[0]??'')==='23000';
            return response()->json(SafeErrorEnvelope::make($conflict?'ORDER_CONFLICT':'ORDER_STORAGE_UNAVAILABLE',$correlation),$conflict?409:503,$headers);
        }
    }
}
