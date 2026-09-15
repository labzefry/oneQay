<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class ViewPosCatalogChangeHistoryWorkspace
{
    public function __construct(
        private PosCatalogChangeHistoryWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function view(): PosCatalogChangeHistoryWorkspaceSnapshot
    {
        $verified = $this->contexts->current();
        $this->authorization->require($verified, PosPermission::prepareCatalog());

        return $this->workspace->read(PosExecutionContext::fromVerified($verified));
    }
}
