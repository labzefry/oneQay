<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosCashVarianceReconciliationWorkspace;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class PosCashVarianceReconciliationWorkspaceController
{
    public function __construct(
        private readonly ViewPosCashVarianceReconciliationWorkspace $workspace,
    ) {}

    public function __invoke(Request $request, ?string $closing_evidence_id = null): Response|JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $snapshot = $this->workspace->view($closing_evidence_id);
            $selected = $snapshot->selected();
            if ($selected !== null) {
                foreach (['expected_cash_atomic', 'observed_closing_cash_atomic', 'variance_atomic'] as $key) {
                    $selected[$key] = (string) $selected[$key];
                }
            }

            return Inertia::render('Pos/CashVarianceReconciliation', [
                'scope' => [
                    'tenant_id' => $snapshot->tenantId(),
                    'organization_id' => $snapshot->organizationId(),
                    'outlet_id' => $snapshot->outletId(),
                    'actor_id' => $snapshot->actorId(),
                ],
                'permissions' => [
                    'can_explain' => $snapshot->canExplain(),
                    'can_review' => $snapshot->canReview(),
                ],
                'cases' => $snapshot->cases(),
                'selected' => $selected,
                'workspace_base_url' => route('pos.shifts.reconciliation.workspace', [], false),
                'explanation_endpoint' => route('pos.shifts.reconciliation.explanation', [], false),
                'review_endpoint' => route('pos.shifts.reconciliation.review', [], false),
                'csrf_token' => csrf_token(),
                'correlation_id' => $correlationId,
            ]);
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASH_VARIANCE_RECONCILIATION_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASH_VARIANCE_RECONCILIATION_UNAVAILABLE', $correlationId),
                503,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }
}
