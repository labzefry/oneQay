<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface FirstControlPrincipalCredentialBootstrapRepository
{
    public const OUTCOME_APPLIED = 'applied';

    /**
     * Prove the exact Sprint 23 control principal is currently eligible before
     * an irreversible password hash is created or a durable transaction starts.
     */
    public function assertEligible(TenantId $tenantId): void;

    /**
     * Repeat the critical eligibility checks inside the transaction and insert
     * exactly one credential row for the repository-derived target.
     */
    public function bootstrapFresh(
        TenantId $tenantId,
        #[\SensitiveParameter] string $password,
    ): string;
}
