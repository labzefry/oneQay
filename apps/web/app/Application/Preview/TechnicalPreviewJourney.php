<?php

declare(strict_types=1);

namespace App\Application\Preview;

use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\ExpectedCashResult;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\ShiftClosingCashResult;
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
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';
    private const MAX_PREVIEW_CASH_ATOMIC = 1_000_000_000;

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

        if ($tenderedAtomicUnits < 0 || $tenderedAtomicUnits > self::MAX_PREVIEW_CASH_ATOMIC) {
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

    /**
     * @return array{sale_id:string,status:string,void_operation_id:string,refund_operation_id:?string,refund_amount_atomic:int,tender_category:string,idempotent_replay:bool}
     */
    public function voidSale(
        PreviewProfile $profile,
        string $saleId,
        string $operationId,
    ): array {
        return $this->withinVerifiedContext(
            $profile,
            fn (): array => $this->fixtures->voidSale(
                $profile,
                new SaleVoidCommand($operationId, $saleId),
            ),
        );
    }

    /**
     * @return array{sale_id:string,status:string,void_operation_id:string,refund_operation_id:string,refund_amount_atomic:int,tender_category:string,idempotent_replay:bool}
     */
    public function refundCashSale(
        PreviewProfile $profile,
        string $saleId,
        string $operationId,
    ): array {
        return $this->withinVerifiedContext(
            $profile,
            fn (): array => $this->fixtures->refundCashSale(
                $profile,
                new SaleCashRefundCommand($operationId, $saleId),
            ),
        );
    }

    public function reconcileCash(
        PreviewProfile $profile,
        string $shiftId,
        string $openingCashEvidenceId,
        int $openingCashAtomic,
        int $cashSalesAtomic,
        int $cashRefundsAtomic,
        int $observedClosingAtomic,
        int $cutoffAtUnix,
        string $correlationId,
    ): CashVarianceResult {
        foreach ([$shiftId, $openingCashEvidenceId, $correlationId] as $identifier) {
            if (preg_match(self::IDENTIFIER_PATTERN, $identifier) !== 1) {
                throw new InvalidArgumentException('Technical Preview cash-control identifier is invalid.');
            }
        }

        if (
            $openingCashAtomic < 0
            || $cashSalesAtomic < 0
            || $cashRefundsAtomic < 0
            || $observedClosingAtomic < 0
            || $openingCashAtomic > self::MAX_PREVIEW_CASH_ATOMIC
            || $cashSalesAtomic > self::MAX_PREVIEW_CASH_ATOMIC
            || $cashRefundsAtomic > $cashSalesAtomic
            || $observedClosingAtomic > self::MAX_PREVIEW_CASH_ATOMIC
            || $cutoffAtUnix <= 0
        ) {
            throw new InvalidArgumentException('Technical Preview cash-control amount is invalid.');
        }

        $expectedCashAtomic = $openingCashAtomic + $cashSalesAtomic - $cashRefundsAtomic;
        if ($expectedCashAtomic < 0 || $expectedCashAtomic > self::MAX_PREVIEW_CASH_ATOMIC) {
            throw new InvalidArgumentException('Technical Preview expected cash exceeds the bounded preview limit.');
        }

        return $this->withinVerifiedContext(
            $profile,
            static function () use (
                $profile,
                $shiftId,
                $openingCashEvidenceId,
                $expectedCashAtomic,
                $observedClosingAtomic,
                $cutoffAtUnix,
                $correlationId,
            ): CashVarianceResult {
                $closingCashEvidenceId = 'preview-closing-'.substr(hash(
                    'sha256',
                    implode('|', [
                        $profile->tenantId(),
                        $profile->outletId(),
                        $shiftId,
                        (string) $cutoffAtUnix,
                        $correlationId,
                    ]),
                ), 0, 32);

                $closing = new ShiftClosingCashResult(
                    $closingCashEvidenceId,
                    $openingCashEvidenceId,
                    $shiftId,
                    'preview-close-'.substr(hash('sha256', $correlationId), 0, 32),
                    $profile->tenantId(),
                    $profile->outletId(),
                    $profile->deviceId(),
                    Money::fromAtomicUnits($observedClosingAtomic, 'IDR', 0),
                    'OPERATOR_OBSERVED_CLOSING_CASH',
                    $correlationId,
                    $cutoffAtUnix,
                );

                $expected = new ExpectedCashResult(
                    $profile->tenantId(),
                    $profile->organizationId(),
                    $profile->outletId(),
                    $shiftId,
                    $openingCashEvidenceId,
                    $closingCashEvidenceId,
                    $cutoffAtUnix,
                    Money::fromAtomicUnits($expectedCashAtomic, 'IDR', 0),
                );

                return (new DeriveCashVariance())->derive($expected, $closing);
            },
        );
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
