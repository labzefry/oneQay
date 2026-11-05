<?php
// Author by Lab | zefry. Bearer-authenticated adapter for the canonical cashier workflow.
declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Delivery\Http\Business\{CashierMembershipController,MenuGuestCashierController};
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Pos\{LaravelDesktopBusinessSettings,LaravelDesktopPosService};
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;

final readonly class DesktopMenuOrdersController
{
    public function __construct(
        private LaravelDesktopPosService $desktop,
        private LaravelDesktopBusinessSettings $settings,
        private MenuGuestCashierController $orders,
        private CashierMembershipController $members,
        private DatabaseManager $database,
    ) {}

    public function __invoke(Request $request, string $action): mixed
    {
        $feature=str_starts_with($action,'member-')?'cashier_membership.enabled':'oneqay.menu_guest_ordering.enabled';
        abort_unless(config($feature,false),404);
        abort_if(strlen($request->getContent())>16384,413);
        try {
            $this->desktop->authenticate((string)$request->bearerToken());
            abort_unless($this->settings->cashierForScope($this->desktop->context()),403);
            $db=$this->database->connection();
            if ($db->getSchemaBuilder()->hasTable('oneqay_business_profiles')) {
                $scope=$this->desktop->scope();
                $type=$db->table('oneqay_business_profiles')->where('tenant_id',$scope['tenant_id'])->value('business_type');
                abort_if(in_array($type,['laundry','boarding','homestay'],true),403);
            }
            return match ($action) {
                'lookup'=>$this->orders->lookup($request),
                'quote'=>$this->orders->quote($request),
                'settle'=>$this->orders->settle($request),
                'cancel'=>$this->orders->cancel($request),
                'member-lookup','member-register'=>($this->members)($request,$action),
                default=>abort(404),
            };
        } catch (DurableAuthorizationViolation) {
            return response()->json(SafeErrorEnvelope::make('ORDER_CASHIER_DENIED',
                (string)$request->attributes->get('oneqay.correlation_id','correlation-missing')),403,['Cache-Control'=>'no-store, private']);
        } finally {
            $this->desktop->clear();
        }
    }
}
