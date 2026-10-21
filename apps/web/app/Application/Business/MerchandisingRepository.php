<?php
declare(strict_types=1);
namespace App\Application\Business;
use App\Application\Organization\VerifiedOrganizationalContext;
// Author by Lab | zefry. Currency preferences never convert historical money.
interface MerchandisingRepository
{
    public function settings(VerifiedOrganizationalContext $scope, bool $lock = false): array;
    public function skuExists(VerifiedOrganizationalContext $scope, string $sku): bool;
    public function saveSettings(VerifiedOrganizationalContext $scope, array $data, int $version): array;
    public function products(VerifiedOrganizationalContext $scope, array $filters, int $page): array;
    public function product(VerifiedOrganizationalContext $scope, string $id, bool $lock = false): ?array;
    public function createCategory(VerifiedOrganizationalContext $scope, string $name): array;
    public function saveProfile(VerifiedOrganizationalContext $scope, array $data, ?int $version): void;
}
