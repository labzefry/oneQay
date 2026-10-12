<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry. All records are scoped by a server-verified context.
interface BusinessRepository
{
    public function once(VerifiedOrganizationalContext $scope, string $operationId, string $action, array $payload, string $correlation, callable $work): array;
    public function find(VerifiedOrganizationalContext $scope, string $resource, string $id, bool $lock = false): array;
    public function create(VerifiedOrganizationalContext $scope, string $resource, array $data): array;
    public function update(VerifiedOrganizationalContext $scope, string $resource, string $id, int $version, array $data): array;
    public function listing(VerifiedOrganizationalContext $scope, string $resource, array $filters = [], int $page = 1): array;
    public function catalogOptions(VerifiedOrganizationalContext $scope, string $search = '', int $page = 1): array;
    public function catalog(VerifiedOrganizationalContext $scope, string $productId): array;
    public function receiveStock(VerifiedOrganizationalContext $scope, string $operationId, string $productId, int $quantity, string $correlation): void;
    public function journalExists(VerifiedOrganizationalContext $scope, string $source): bool;
    public function balance(VerifiedOrganizationalContext $scope, string $from, string $to): array;
    public function leaveOverlaps(VerifiedOrganizationalContext $scope, string $employee, string $from, string $to): bool;
}
