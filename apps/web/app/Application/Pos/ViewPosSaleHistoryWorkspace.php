<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosSaleHistoryWorkspace
{
    private const VIEW_PERMISSION = 'pos.reporting.sales-summary.view';

    public function __construct(
        private PosSaleHistoryWorkspaceRepository $history,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(?string $selectedSaleId): PosSaleHistoryWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require(
            $verified,
            PermissionIdentifier::fromString(self::VIEW_PERMISSION),
        );

        return $this->history->read($context, $selectedSaleId);
    }
}
