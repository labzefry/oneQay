<?php

declare(strict_types=1);

namespace App\Delivery\Http\Business;

use App\Application\Authorization\{AdministrationPermission, DurableAuthorizationViolation, DurablePolicyAdministrationViolation};
use App\Application\Business\{BusinessAccess, BusinessRoleAdministration, BusinessViolation};
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Author by Lab | zefry
final readonly class BusinessAccessController
{
    public function __construct(private BusinessAccess $access, private BusinessRoleAdministration $roles) {}

    public function show(Request $request): mixed
    {
        return $this->respond($request, function () use ($request): mixed {
            $scope = $this->access->require(AdministrationPermission::MANAGE);
            return Inertia::render('Business/Access', [
                'identity_id' => $scope->identityId()->value(),
                'initial_role' => $request->query('purpose') === 'shift-close' ? 'oneqay-cashier' : 'oneqay-business-manager',
                'return_url' => $request->query('purpose') === 'shift-close' ? '/pos/shifts/close' : '/pos',
                'outlet_id' => $scope->outletId()?->value(),
                'templates' => BusinessRoleAdministration::templates(),
                'step_up_url' => Route::has('auth.privileged-step-up.reauthenticate') ? route('auth.privileged-step-up.reauthenticate', [], false) : null,
            ])->toResponse($request);
        });
    }

    public function apply(Request $request): mixed
    {
        return $this->respond($request, function () use ($request): mixed {
            if (strlen($request->getContent()) > 4096) throw new BusinessViolation('REQUEST_TOO_LARGE', 413);
            return response()->json(['status' => 'saved', 'record' => $this->roles->apply($request->except('_token'))]);
        });
    }

    private function respond(Request $request, callable $work): mixed
    {
        try {
            $response = $work();
            $response->headers->set('Cache-Control', 'no-store, private');
            return $response;
        } catch (BusinessViolation $e) {
            return $this->error($request, $e->reason, $e->status);
        } catch (\InvalidArgumentException) {
            return $this->error($request, 'INVALID_FIELDS', 422);
        } catch (\App\Application\Persistence\DurablePersistenceViolation) {
            return $this->error($request, 'BUSINESS_STORAGE_UNAVAILABLE', 503);
        } catch (DurableAuthorizationViolation | DurablePolicyAdministrationViolation) {
            return $this->error($request, 'BUSINESS_ACCESS_CHANGE_DENIED', 403);
        }
    }

    private function error(Request $request, string $code, int $status): mixed
    {
        return response()->json(SafeErrorEnvelope::make($code, (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing')), $status, ['Cache-Control' => 'no-store, private']);
    }
}
