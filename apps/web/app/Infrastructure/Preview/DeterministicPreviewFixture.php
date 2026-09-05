<?php

declare(strict_types=1);

namespace App\Infrastructure\Preview;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
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

    /** @var array<string, list<CatalogItem>> */
    private array $catalog = [];

    /** @var array<string, SaleReceipt> */
    private array $receipts = [];

    /** @var array<string, array{sale_id:string,status:string,void_operation_id:string,refund_operation_id:?string,refund_amount_atomic:int,tender_category:string}> */
    private array $adjustments = [];

    /** @var array<string, string> */
    private array $operationFingerprints = [];

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

    public function verifiedIdentity(string $principalId): ?VerifiedPlatformIdentity
    {
        if (! isset($this->profiles[$principalId])) {
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
        $receipt = $this->store->complete($context, $command);
        $this->receipts[$this->receiptKey(
            $receipt->tenantId(),
            $receipt->organizationId(),
            $receipt->outletId(),
            $receipt->saleId(),
        )] = $receipt;

        return $receipt;
    }

    public function voidSale(PreviewProfile $profile, SaleVoidCommand $command): array
    {
        $receipt = $this->receiptFor($profile, $command->saleId());
        $fingerprint = $command->semanticFingerprintPart();
        $operationId = $command->operationId();

        if (isset($this->operationFingerprints[$operationId])) {
            if ($this->operationFingerprints[$operationId] !== $fingerprint) {
                throw new PosTransactionViolation();
            }

            $existing = $this->adjustments[$this->adjustmentKey($profile, $command->saleId())] ?? null;
            if ($existing === null || $existing['void_operation_id'] !== $operationId) {
                throw new PosTransactionViolation();
            }

            return $existing + ['idempotent_replay' => true];
        }

        $key = $this->adjustmentKey($profile, $command->saleId());
        if (isset($this->adjustments[$key])) {
            throw new PosTransactionViolation();
        }

        $this->operationFingerprints[$operationId] = $fingerprint;
        $this->adjustments[$key] = [
            'sale_id' => $receipt->saleId(),
            'status' => 'VOIDED',
            'void_operation_id' => $operationId,
            'refund_operation_id' => null,
            'refund_amount_atomic' => 0,
            'tender_category' => $receipt->tenderCategory()->value,
        ];

        return $this->adjustments[$key] + ['idempotent_replay' => false];
    }

    public function refundCashSale(PreviewProfile $profile, SaleCashRefundCommand $command): array
    {
        $receipt = $this->receiptFor($profile, $command->saleId());
        if ($receipt->tenderCategory()->value !== 'CASH') {
            throw new PosTransactionViolation();
        }

        $key = $this->adjustmentKey($profile, $command->saleId());
        $adjustment = $this->adjustments[$key] ?? null;
        if ($adjustment === null || $adjustment['status'] !== 'VOIDED') {
            if (
                $adjustment !== null
                && $adjustment['status'] === 'REFUNDED'
                && $adjustment['refund_operation_id'] === $command->operationId()
                && ($this->operationFingerprints[$command->operationId()] ?? null) === $command->semanticFingerprintPart()
            ) {
                return $adjustment + ['idempotent_replay' => true];
            }

            throw new PosTransactionViolation();
        }

        $operationId = $command->operationId();
        $fingerprint = $command->semanticFingerprintPart();
        if (isset($this->operationFingerprints[$operationId])) {
            throw new PosTransactionViolation();
        }

        $this->operationFingerprints[$operationId] = $fingerprint;
        $adjustment['status'] = 'REFUNDED';
        $adjustment['refund_operation_id'] = $operationId;
        $adjustment['refund_amount_atomic'] = $receipt->total()->atomicUnits();
        $this->adjustments[$key] = $adjustment;

        return $adjustment + ['idempotent_replay' => false];
    }

    private function receiptFor(PreviewProfile $profile, string $saleId): SaleReceipt
    {
        $receipt = $this->receipts[$this->receiptKey(
            $profile->tenantId(),
            $profile->organizationId(),
            $profile->outletId(),
            $saleId,
        )] ?? null;

        if (! $receipt instanceof SaleReceipt || $receipt->actorId() !== $profile->principalId()) {
            throw new PosTransactionViolation();
        }

        return $receipt;
    }

    private function adjustmentKey(PreviewProfile $profile, string $saleId): string
    {
        return $this->receiptKey(
            $profile->tenantId(),
            $profile->organizationId(),
            $profile->outletId(),
            $saleId,
        );
    }

    private function receiptKey(string $tenantId, string $organizationId, string $outletId, string $saleId): string
    {
        return implode('|', [$tenantId, $organizationId, $outletId, $saleId]);
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
