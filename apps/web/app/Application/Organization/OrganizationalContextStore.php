<?php

namespace App\Application\Organization;

// Author by Lab | zefry
interface OrganizationalContextStore
{
    public function current(): ?VerifiedOrganizationalContext;

    public function setVerified(VerifiedOrganizationalContext $context): void;

    public function clear(): void;
}
