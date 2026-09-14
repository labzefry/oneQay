<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosCashierCatalogItem;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosCashierWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosCashierWorkspaceController
{
    public function __construct(private readonly ViewPosCashierWorkspace $workspace) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $snapshot = $this->workspace->view();

            return Inertia::render('Pos/Cashier', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'device_id' => $snapshot->deviceId(),
                ],
                'active_shift_id' => $snapshot->activeShiftId(),
                'catalog' => array_map(
                    static fn (PosCashierCatalogItem $item): array => [
                        'product_id' => $item->productId(),
                        'display_name' => $item->displayName(),
                        'available_quantity' => $item->availableQuantity(),
                        'unit_price' => [
                            'atomic_units' => (string) $item->unitPrice()->atomicUnits(),
                            'currency' => $item->unitPrice()->currency(),
                            'scale' => $item->unitPrice()->scale(),
                        ],
                    ],
                    $snapshot->catalogItems(),
                ),
                'sale_endpoint' => route('pos.sales.complete', [], false),
                'csrf_token' => csrf_token(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASHIER_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASHIER_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
