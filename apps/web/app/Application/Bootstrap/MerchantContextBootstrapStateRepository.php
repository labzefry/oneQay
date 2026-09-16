<?php

declare(strict_types=1);

namespace App\Application\Bootstrap;

use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface MerchantContextBootstrapStateRepository
{
    public function assertFresh(TenantId $tenantId): void;
}
