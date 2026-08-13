<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Organization\OrganizationalContextStore;
use App\Domain\Pos\SaleReceipt;

// Author by Lab | zefry
final readonly class CompleteSyntheticSale
{
    public function __construct(
        private SyntheticPosStore $store,
        private OrganizationalContextStore $contexts,
    ) {
    }

    public function complete(SaleCommand $command): SaleReceipt
    {
        return $this->store->complete(
            PosExecutionContext::fromVerified($this->contexts->current()),
            $command,
        );
    }
}
