<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosOperationalSalesSummary
{
    private const VIEW_PERMISSION = 'pos.reporting.sales-summary.view';

    public function __construct(
        private PosOperationalSalesSummaryRepository $reports,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosOperationalSalesSummary
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PermissionIdentifier::fromString(self::VIEW_PERMISSION));

        return $this->reports->summarize($context);
    }
}
