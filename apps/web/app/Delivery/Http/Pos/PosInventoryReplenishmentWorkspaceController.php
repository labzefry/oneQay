<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosInventoryReplenishmentWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosInventoryReplenishmentWorkspaceController
{
    public function __construct(private readonly ViewPosInventoryReplenishmentWorkspace $workspace) {}

    public function __invoke(Request $request): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $snapshot = $this->workspace->view();

            return Inertia::render('Pos/InventoryReplenishment', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'device_id' => $snapshot->deviceId(),
                ],
                'items' => $snapshot->items(),
                'recent_replenishments' => $snapshot->recentReplenishments(),
                'endpoint' => route('pos.inventory.replenishment.record', [], false),
                'csrf_token' => csrf_token(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_REPLENISHMENT_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_REPLENISHMENT_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
