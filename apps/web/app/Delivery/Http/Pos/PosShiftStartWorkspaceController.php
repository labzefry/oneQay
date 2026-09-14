<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosShiftStartWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosShiftStartWorkspaceController
{
    public function __construct(private readonly ViewPosShiftStartWorkspace $workspace) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $snapshot = $this->workspace->view();
            $openingCash = $snapshot->openingCash();

            return Inertia::render('Pos/ShiftStart', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'device_id' => $snapshot->deviceId(),
                ],
                'active_shift' => $snapshot->activeShiftId() === null ? null : [
                    'shift_id' => $snapshot->activeShiftId(),
                    'opened_at_unix' => $snapshot->openedAtUnix(),
                    'opening_cash_evidence' => $snapshot->openingCashEvidenceId() === null ? null : [
                        'evidence_id' => $snapshot->openingCashEvidenceId(),
                        'amount' => [
                            'atomic_units' => (string) $openingCash?->atomicUnits(),
                            'currency' => $openingCash?->currency(),
                            'scale' => $openingCash?->scale(),
                        ],
                        'evidence_mode' => $snapshot->openingCashEvidenceMode(),
                        'recorded_at_unix' => $snapshot->openingCashRecordedAtUnix(),
                    ],
                ],
                'ready_for_cashier' => $snapshot->readyForCashier(),
                'open_shift_endpoint' => route('pos.shifts.open', [], false),
                'opening_cash_endpoint' => route('pos.shifts.opening-cash', [], false),
                'cashier_url' => Route::has('pos.cashier.workspace')
                    ? route('pos.cashier.workspace', [], false)
                    : null,
                'csrf_token' => csrf_token(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_START_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_START_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
