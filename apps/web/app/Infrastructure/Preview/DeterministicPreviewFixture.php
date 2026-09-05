<?php

declare(strict_types=1);

namespace App\Infrastructure\Preview;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\SaleCommand;
use App\Application\Preview\PreviewFixtureGateway;
use App\Application\Preview\PreviewProfile;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\CatalogItem;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\SaleReceipt;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Pos\InMemorySyntheticPosStore;

// Author by Lab | zefry
final class DeterministicPreviewFixture implements PreviewFixtureGateway
{
    /** @var array<string, PreviewProfile> */
    private array $profiles;

    /** @var array<string, PreviewProfile> */
    private array $reviewers;

    /** @var array<string, list<CatalogItem>> */
    private array $catalog = [];

    private InMemorySyntheticPosStore $store;

    public function __construct()
    {
        $this->profiles = [
            'synthetic-principal-a' => new PreviewProfile(
                'synthetic-principal-a', 'Demo Alpha', 'tenant-alpha',
                'organization-alpha', 'outlet-alpha', 'device-alpha',
            ),
            'synthetic-principal-b' => new PreviewProfile(
                'synthetic-principal-b', 'Demo Beta', 'tenant-beta',
                'organization-beta', 'outlet-beta', 'device-beta',
            ),
        ];

        $this->reviewers = [
            'synthetic-principal-a' => new PreviewProfile(
                'synthetic-principal-reviewer-a', 'Independent Reviewer Alpha', 'tenant-alpha',
                'organization-alpha', 'outlet-alpha', 'device-alpha-reviewer',
            ),
            'synthetic-principal-b' => new PreviewProfile(
                'synthetic-principal-reviewer-b', 'Independent Reviewer Beta', 'tenant-beta',
                'organization-beta', 'outlet-beta', 'device-beta-reviewer',
            ),
        ];

        $this->store = new InMemorySyntheticPosStore();
        $this->seed('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha', 'Synthetic Alpha Product', 1999, 10);
        $this->seed('tenant-alpha', 'outlet-alpha', 'synthetic-product-secondary', 'Synthetic Secondary Product', 501, 5);
        $this->seed('tenant-beta', 'outlet-beta', 'synthetic-product-beta', 'Synthetic Beta Product', 4900, 8);
        $this->seed('tenant-beta', 'outlet-beta', 'synthetic-product-beta-secondary', 'Synthetic Beta Secondary', 1100, 7);
    }

    public function profiles(): array
    {
        return array_values($this->profiles);
    }

    public function profile(string $principalId): ?PreviewProfile
    {
        return $this->profiles[$principalId] ?? null;
    }

    public function reviewerFor(string $operatorPrincipalId): ?PreviewProfile
    {
        return $this->reviewers[$operatorPrincipalId] ?? null;
    }

    public function verifiedIdentity(string $principalId): ?VerifiedPlatformIdentity
    {
        $known = isset($this->profiles[$principalId]);
        if (! $known) {
            foreach ($this->reviewers as $reviewer) {
                if ($reviewer->principalId() === $principalId) {
                    $known = true;
                    break;
                }
            }
        }

        if (! $known) {
            return null;
        }

        return new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString($principalId));
    }

    public function catalogFor(string $tenantId, string $outletId): array
    {
        return $this->catalog[$tenantId.'|'.$outletId] ?? [];
    }

    public function complete(PosExecutionContext $context, SaleCommand $command): SaleReceipt
    {
        return $this->store->complete($context, $command);
    }

    private function seed(
        string $tenantId,
        string $outletId,
        string $productId,
        string $name,
        int $price,
        int $stock,
    ): void {
        $item = new CatalogItem(
            TenantId::fromString($tenantId),
            OutletId::fromString($outletId),
            ProductId::fromString($productId),
            $name,
            Money::fromAtomicUnits($price, 'IDR', 0),
        );

        $this->catalog[$tenantId.'|'.$outletId][] = $item;
        $this->store->seed($item, $stock);
    }
}
