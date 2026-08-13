<?php

namespace App\Infrastructure\Organization;

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final class RequestOrganizationalContextStore implements OrganizationalContextStore
{
    private ?VerifiedOrganizationalContext $current = null;

    public function current(): ?VerifiedOrganizationalContext
    {
        return $this->current;
    }

    public function setVerified(VerifiedOrganizationalContext $context): void
    {
        $this->current = $context;
    }

    public function clear(): void
    {
        $this->current = null;
    }
}
