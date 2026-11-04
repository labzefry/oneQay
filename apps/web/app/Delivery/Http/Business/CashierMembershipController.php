<?php
// Author by Lab | zefry. Exact lookup, no customer enumeration.
declare(strict_types=1);

namespace App\Delivery\Http\Business;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Business\{BusinessAccess,BusinessViolation};
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Business\LaravelCashierMemberships;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

final readonly class CashierMembershipController
{
    public function __construct(private LaravelCashierMemberships $members,private BusinessAccess $access) {}

    public function page(): mixed
    {
        abort_unless(config('cashier_membership.enabled',false),404);
        try { $this->access->require('pos.sale.complete'); }
        catch (DurableAuthorizationViolation|BusinessViolation) { abort(403); }
        return Inertia::render('Business/MenuGuestOrders',[
            'settlement_enabled'=>false,'membership_enabled'=>true,'directory_only'=>true,
        ]);
    }

    public function __invoke(Request $r,string $action): mixed
    {
        abort_unless(config('cashier_membership.enabled',false),404);
        $headers=['Cache-Control'=>'no-store, private','Referrer-Policy'=>'no-referrer'];
        $correlation=(string)$r->attributes->get('oneqay.correlation_id','correlation-missing');
        try {
            if (strlen($r->getContent())>4096) throw new BusinessViolation('REQUEST_TOO_LARGE',413);
            $input=$r->except('_token');
            $record=match($action) {
                'member-lookup'=>$this->members->lookup($input),
                'member-register'=>$this->members->register($input,$correlation),
                default=>abort(404),
            };
            return response()->json(['member'=>$record],200,$headers);
        } catch (DurableAuthorizationViolation) {
            $code='MEMBERSHIP_CASHIER_DENIED';$status=403;
        } catch (BusinessViolation $e) {
            $code=$e->reason;$status=$e->status;
        } catch (QueryException) {
            $code='MEMBERSHIP_STORAGE_UNAVAILABLE';$status=503;
        }
        return response()->json(SafeErrorEnvelope::make($code,$correlation),$status,$headers);
    }
}
