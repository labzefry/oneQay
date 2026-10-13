<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry. Identity/authorization integration boundary.
interface BusinessAccountProvisioner
{
    public function provision(array $business, #[\SensitiveParameter] string $password): void;
    public function verifyPassword(array $business, #[\SensitiveParameter] string $password): bool;
    public function revokeTenantSessions(string $tenant, int $now): void;
}
