<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosOperationsHubSnapshot;
use App\Application\Pos\ViewPosOperationsHub;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosOperationsHubController
{
    public function __construct(private readonly ViewPosOperationsHub $hub) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $snapshot = $this->hub->view();
            $destinations = $this->destinations($snapshot);

            if ($destinations === []) {
                return response()->json(
                    SafeErrorEnvelope::make('POS_OPERATIONS_HUB_NO_DELIVERED_DESTINATIONS', $correlationId),
                    503,
                    ['Cache-Control' => 'no-store, private'],
                );
            }

            return Inertia::render('Pos/OperationsHub', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'device_id' => $snapshot->deviceId(),
                ],
                'destinations' => $destinations,
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_OPERATIONS_HUB_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    /** @return list<array{key:string,title:string,description:string,url:string,category:string}> */
    private function destinations(PosOperationsHubSnapshot $snapshot): array
    {
        $candidates = [
            [$snapshot->canCatalogInventorySetup(), 'pos.catalog-inventory.setup', 'catalog_inventory', 'Catalog & Opening Stock', 'Prepare sale items and establish one-time opening inventory.', 'SETUP'],
            [$snapshot->canInventoryReplenishment(), 'pos.inventory.replenishment.workspace', 'inventory_replenishment', 'Inventory Replenishment', 'Receive positive stock after the canonical opening baseline with immutable evidence.', 'STOCK'],
            [$snapshot->canInventoryAccountability(), 'pos.inventory.accountability.workspace', 'inventory_accountability', 'Inventory Accountability', 'Reconcile current stock against canonical opening, receiving, sale, and void evidence.', 'REVIEW'],
            [$snapshot->canShiftStart(), 'pos.shift-start.workspace', 'shift_start', 'Start Shift', 'Open the exact device shift and record opening cash evidence.', 'SHIFT'],
            [$snapshot->canCashier(), 'pos.cashier.workspace', 'cashier', 'Cashier', 'Run sale entry on the active exact-device shift.', 'SELL'],
            [$snapshot->canReporting(), 'pos.reporting.sales-summary', 'sales_summary', 'Sales Summary', 'Review current operational sales counters and totals.', 'REVIEW'],
            [$snapshot->canReporting(), 'pos.reporting.sales-history', 'sale_history', 'Sale History', 'Inspect immutable receipt and correction evidence.', 'REVIEW'],
            [$snapshot->canCorrections(), 'pos.sales.corrections.workspace', 'corrections', 'Sale Corrections', 'Perform only the void or refund actions granted to this context.', 'CONTROL'],
            [$snapshot->canCashVarianceReconciliation(), 'pos.shifts.reconciliation.workspace', 'cash_variance_reconciliation', 'Cash Variance Reconciliation', 'Explain and independently review non-zero closing-cash variance before final shift close.', 'CONTROL'],
            [$snapshot->canShiftClose(), 'pos.shifts.close.page', 'shift_close', 'Close Shift', 'Complete the guarded final shift-close workflow when delivered.', 'SHIFT'],
        ];

        $destinations = [];
        foreach ($candidates as [$allowed, $routeName, $key, $title, $description, $category]) {
            if (! $allowed || ! Route::has($routeName)) {
                continue;
            }

            $destinations[] = [
                'key' => $key,
                'title' => $title,
                'description' => $description,
                'url' => route($routeName, [], false),
                'category' => $category,
            ];
        }

        return $destinations;
    }
}
