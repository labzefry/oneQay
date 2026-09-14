<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosSaleCorrectionWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosSaleCorrectionWorkspaceController
{
    public function __construct(private readonly ViewPosSaleCorrectionWorkspace $workspace) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $snapshot = $this->workspace->view();

            return Inertia::render('Pos/SaleCorrections', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'device_id' => $snapshot->deviceId(),
                ],
                'permissions' => [
                    'can_void' => $snapshot->canVoid(),
                    'can_cash_refund' => $snapshot->canCashRefund(),
                ],
                'sales' => $snapshot->sales(),
                'void_endpoint' => route('pos.sales.void', [], false),
                'cash_refund_endpoint' => route('pos.sales.cash-refund', [], false),
                'csrf_token' => csrf_token(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SALE_CORRECTION_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SALE_CORRECTION_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
