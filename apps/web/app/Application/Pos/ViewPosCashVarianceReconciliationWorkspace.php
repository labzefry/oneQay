<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosCashVarianceReconciliationWorkspace
{
    public const REVIEW_PERMISSION = 'pos.shift.cash-variance-review-decision.record';

    public function __construct(
        private PosCashVarianceReconciliationWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(?string $selectedClosingCashEvidenceId): PosCashVarianceReconciliationWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $canExplain = $this->authorization->allows(
            $verified,
            PosPermission::recordCashVarianceExplanation(),
        );
        $canReview = $this->authorization->allows(
            $verified,
            PermissionIdentifier::fromString(self::REVIEW_PERMISSION),
        );

        if (! $canExplain && ! $canReview) {
            $this->authorization->require(
                $verified,
                PosPermission::recordCashVarianceExplanation(),
            );
        }

        return new PosCashVarianceReconciliationWorkspaceSnapshot(
            $this->workspace->read($context, $selectedClosingCashEvidenceId),
            $canExplain,
            $canReview,
        );
    }
}
