<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosSaleHistoryWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosSaleHistoryWorkspaceController
{
    public function __construct(private readonly ViewPosSaleHistoryWorkspace $history) {}

    public function __invoke(Request $request, ?string $sale_id = null): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $snapshot = $this->history->view($sale_id);
            $receipt = $snapshot->selectedReceipt();

            return Inertia::render('Pos/SaleHistory', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                ],
                'recent_sales' => $snapshot->recentSales(),
                'selected_sale_id' => $snapshot->selectedSaleId(),
                'selected_found' => $snapshot->selectedSaleId() === null || $receipt !== null,
                'selected_receipt' => $receipt,
                'history_base_url' => route('pos.reporting.sales-history', [], false),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_REPORTING_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SALE_HISTORY_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
