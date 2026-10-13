<?php
declare(strict_types=1);
namespace App\Application\Platform;
use App\Application\Organization\{OrganizationalContextStore, VerifiedOrganizationalContext};
// Author by Lab | zefry. Platform ownership is never inferred from a tenant role.
final readonly class PlatformOwnerAccess
{
    public function __construct(private OrganizationalContextStore $contexts, private string $ownerTenant, private string $ownerIdentity) {}
    public function allows(): bool
    {
        $actor = $this->contexts->current();
        return $actor !== null && $this->ownerTenant !== '' && $this->ownerIdentity !== ''
            && hash_equals($this->ownerTenant, $actor->tenantId()->value())
            && hash_equals($this->ownerIdentity, $actor->identityId()->value());
    }
    public function require(): VerifiedOrganizationalContext
    {
        if (!$this->allows()) throw new PlatformViolation('PLATFORM_OWNER_REQUIRED', 403);
        return $this->contexts->current();
    }
}
