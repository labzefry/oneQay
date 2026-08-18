<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface FirstControlPrincipalCredentialBootstrapRepository
{
    public const OUTCOME_APPLIED = 'applied';

    public function bootstrap(
        TenantId $tenantId,
        #[\SensitiveParameter] string $password,
    ): string;
}
