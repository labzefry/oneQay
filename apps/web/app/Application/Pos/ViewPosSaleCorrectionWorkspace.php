<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosSaleCorrectionWorkspace
{
    public function __construct(
        private PosSaleCorrectionWorkspaceRepository $repository,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosSaleCorrectionWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $canVoid = $this->authorization->allows($verified, PosPermission::voidSale());
        $canCashRefund = $this->authorization->allows($verified, PosPermission::refundSale());

        if (! $canVoid && ! $canCashRefund) {
            $this->authorization->require($verified, PosPermission::voidSale());
        }

        return new PosSaleCorrectionWorkspaceSnapshot(
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $canVoid,
            $canCashRefund,
            $this->repository->recent($context),
        );
    }
}
