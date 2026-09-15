<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosShiftHistoryPerformanceWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosShiftHistoryPerformanceWorkspaceController
{
    public function __construct(private readonly ViewPosShiftHistoryPerformanceWorkspace $reports) {}

    public function __invoke(Request $request, ?string $shiftId = null): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $snapshot = $this->reports->view($shiftId);

            return Inertia::render('Pos/ShiftHistoryPerformance', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'requester_device_id' => $snapshot->requesterDeviceId(),
                ],
                'closed_shifts' => $snapshot->closedShifts(),
                'selected_shift' => $snapshot->selectedShift(),
                'buckets' => $snapshot->buckets(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_HISTORY_PERFORMANCE_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_HISTORY_PERFORMANCE_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
