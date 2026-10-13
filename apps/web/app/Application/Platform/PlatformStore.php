<?php
declare(strict_types=1);
namespace App\Application\Platform;
// Author by Lab | zefry
interface PlatformStore
{
    public function transaction(callable $work): mixed;
    public function once(string $actor, string $operation, array $input, callable $work): array;
    public function create(array $business): array;
    public function business(string $id, bool $lock = false): array;
    public function loginBusiness(string $email, ?string $id): ?array;
    public function byToken(string $tokenHash): array;
    public function update(string $id, int $version, array $changes): array;
    public function listing(int $page, string $search): array;
    public function audit(string $actor, string $action, string $business, string $reason, int $now): void;
    public function tenantAllowed(string $tenant): bool;
}
