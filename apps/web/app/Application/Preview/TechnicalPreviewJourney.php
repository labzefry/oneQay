<?php

declare(strict_types=1);

namespace App\Application\Preview;

use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\SaleCommand;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\TenantContextStore;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Pos\Cart;
use App\Domain\Pos\CartLine;
use App\Domain\Pos\CatalogItem;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\SaleReceipt;
use App\Domain\Pos\TenderCategory;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class TechnicalPreviewJourney
{
    public function __construct(
        private PreviewFixtureGateway $fixtures,
        private TenantMembershipVerifier $memberships,
        private TenantContextStore $tenantContexts,
        private EnterOrganizationalContext $organizations,
        private CompleteSyntheticSale $sales,
    ) {
    }

    /** @return list<PreviewProfile> */
    public function profiles(): array
    {
        return $this->fixtures->profiles();
    }

    public function profile(string $principalId): ?PreviewProfile
    {
        return $this->fixtures->profile($principalId);
    }

    /**
     * Execute one bounded operation while the server-verified Preview tenant and
     * organizational context remain active. The callback receives only the
     * verified tenant context; raw tenant hints never become authority here.
     *
     * @template T
     * @param callable(VerifiedTenantContext): T $operation
     * @return T
     */
    public function withinVerifiedContext(PreviewProfile $profile, callable $operation): mixed
    {
        $this->enterVerifiedContext($profile);

        try {
            $context = $this->tenantContexts->current();
            if (! $context instanceof VerifiedTenantContext) {
                throw new MissingTenantContext('Verified tenant context is required.');
            }

            return $operation($context);
        } finally {
            $this->clearContext();
        }
    }

    /** @return list<CatalogItem> */
    public function catalog(PreviewProfile $profile): array
    {
        $this->enterVerifiedContext($profile);

        try {
            return $this->fixtures->catalogFor($profile->tenantId(), $profile->outletId());
        } finally {
            $this->clearContext();
        }
    }

    /**
     * @param list<array{product_id:string,quantity:int}> $lines
     */
    public function completeSale(
        PreviewProfile $profile,
        array $lines,
        string $tenderCategory,
        int $tenderedAtomicUnits,
        string $operationId,
        string $correlationId,
    ): SaleReceipt {
        if ($lines === [] || count($lines) > 20) {
            throw new InvalidArgumentException('Technical Preview cart is invalid.');
        }

        $category = TenderCategory::tryFrom($tenderCategory);
        if ($category === null) {
            throw new InvalidArgumentException('Technical Preview tender is invalid.');
        }

        if ($tenderedAtomicUnits < 0 || $tenderedAtomicUnits > 1_000_000_000) {
            throw new InvalidArgumentException('Technical Preview tender amount is invalid.');
        }

        $cartLines = [];
        foreach ($lines as $line) {
            $productId = (string) ($line['product_id'] ?? '');
            $quantity = (int) ($line['quantity'] ?? 0);

            if ($quantity < 1 || $quantity > 99) {
                throw new InvalidArgumentException('Technical Preview quantity is invalid.');
            }

            $cartLines[] = new CartLine(ProductId::fromString($productId), $quantity);
        }

        $this->enterVerifiedContext($profile);

        try {
            return $this->sales->complete(new SaleCommand(
                $operationId,
                Cart::fromLines($cartLines),
                $category,
                Money::fromAtomicUnits($tenderedAtomicUnits, 'IDR', 0),
                $correlationId,
            ));
        } finally {
            $this->clearContext();
        }
    }

    private function enterVerifiedContext(PreviewProfile $profile): void
    {
        $this->clearContext();

        $identity = $this->fixtures->verifiedIdentity($profile->principalId());
        $tenant = $this->memberships->verify($profile->principalId(), $profile->tenantId());

        if ($identity === null || $tenant === null) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        $this->tenantContexts->setVerified($tenant);

        try {
            $this->organizations->enter(
                $identity,
                $tenant,
                $profile->organizationId(),
                $profile->outletId(),
                $profile->deviceId(),
            );
        } catch (\Throwable $exception) {
            $this->clearContext();
            throw $exception;
        }
    }

    private function clearContext(): void
    {
        $this->organizations->clear();
        $this->tenantContexts->clear();
    }
}
