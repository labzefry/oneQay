<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosOperationalSalesSummary;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosOperationalSalesSummaryController
{
    public function __construct(private readonly ViewPosOperationalSalesSummary $reports) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $summary = $this->reports->view();

            return Inertia::render('Pos/SalesSummary', [
                'scope' => [
                    'tenant_id' => $summary->tenantId(),
                    'organization_id' => $summary->organizationId(),
                    'outlet_id' => $summary->outletId(),
                ],
                'counters' => [
                    'completed_sales' => $summary->completedSales(),
                    'voided_sales' => $summary->voidedSales(),
                    'cash_refunded_sales' => $summary->cashRefundedSales(),
                ],
                'gross_totals' => $summary->grossTotals(),
                'recent_sales' => $summary->recentSales(),
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
                SafeErrorEnvelope::make('POS_REPORTING_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}