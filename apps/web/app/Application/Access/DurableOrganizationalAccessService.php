<?php

namespace App\Application\Access;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\PersistenceTransaction;

// Author by Lab | zefry
final readonly class DurableOrganizationalAccessService
{
    public function __construct(
        private DurableOrganizationalAccessRepository $repository,
        private PersistenceTransaction $transaction,
    ) {
    }

    public function recordVerifiedContext(VerifiedOrganizationalContext $context): void
    {
        $grant = DurableOrganizationalAccessGrant::fromVerifiedContext($context);

        $this->transaction->run(function () use ($grant): void {
            $this->repository->record($grant);
        });
    }
}
