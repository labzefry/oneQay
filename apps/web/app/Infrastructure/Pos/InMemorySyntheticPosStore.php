<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SyntheticPosStore;
use App\Domain\Pos\CatalogItem;
use App\Domain\Pos\Money;
use App\Domain\Pos\SaleLineResult;
use App\Domain\Pos\SaleReceipt;
use App\Domain\Pos\TenderCategory;
use InvalidArgumentException;
use OverflowException;
use Throwable;

// Author by Lab | zefry
final class InMemorySyntheticPosStore implements SyntheticPosStore
{
    /** @var array<string, CatalogItem> */
    private array $catalog = [];

    /** @var array<string, int> */
    private array $stock = [];

    /** @var array<string, SaleReceipt> */
    private array $sales = [];

    /** @var array<string, array<string, mixed>> */
    private array $paymentEffects = [];

    /** @var array<string, array{fingerprint:string,receipt:SaleReceipt}> */
    private array $idempotency = [];

    /** @var list<array<string, string>> */
    private array $audit = [];

    public function seed(CatalogItem $item, int $availableStock): void
    {
        if ($availableStock < 0) {
            throw new InvalidArgumentException('Synthetic stock must not be negative.');
        }

        $key = $this->catalogKey(
            $item->tenantId()->value(),
            $item->outletId()->value(),
            $item->productId()->value(),
        );

        $this->catalog[$key] = $item;
        $this->stock[$key] = $availableStock;
    }

    public function complete(PosExecutionContext $context, SaleCommand $command): SaleReceipt
    {
        $idempotencyKey = $context->tenantId().'|'.$command->operationId();
        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $command->semanticFingerprintPart(),
        ]));

        if (isset($this->idempotency[$idempotencyKey])) {
            $previous = $this->idempotency[$idempotencyKey];

            if (! hash_equals($previous['fingerprint'], $fingerprint)) {
                $this->recordAudit($context, $command, null, 'REJECTED_IDEMPOTENCY_CONFLICT');
                throw new PosTransactionViolation();
            }

            $this->recordAudit(
                $context,
                $command,
                $previous['receipt']->saleId(),
                'REPLAYED',
            );

            return $previous['receipt'];
        }

        /**
         * @var list<array{
         *   catalog_key:string,
         *   quantity:int,
         *   result:SaleLineResult
         * }> $resolvedLines
         */
        $resolvedLines = [];
        $total = null;

        foreach ($command->cart()->lines() as $cartLine) {
            $catalogKey = $this->catalogKey(
                $context->tenantId(),
                $context->outletId(),
                $cartLine->productId()->value(),
            );
            $item = $this->catalog[$catalogKey] ?? null;

            if (! $item instanceof CatalogItem) {
                $this->recordAudit($context, $command, null, 'REJECTED_CATALOG');
                throw new PosTransactionViolation();
            }

            try {
                $lineTotal = $item->unitPrice()->multiply($cartLine->quantity());
                $total = $total === null ? $lineTotal : $total->add($lineTotal);
            } catch (InvalidArgumentException|OverflowException) {
                $this->recordAudit($context, $command, null, 'REJECTED_MONEY');
                throw new PosTransactionViolation();
            }

            $resolvedLines[] = [
                'catalog_key' => $catalogKey,
                'quantity' => $cartLine->quantity(),
                'result' => new SaleLineResult(
                    $cartLine->productId(),
                    $cartLine->quantity(),
                    $item->unitPrice(),
                    $lineTotal,
                ),
            ];
        }

        if (! $total instanceof Money) {
            $this->recordAudit($context, $command, null, 'REJECTED_CART');
            throw new PosTransactionViolation();
        }

        try {
            if ($command->tenderedAmount()->compare($total) < 0) {
                $this->recordAudit($context, $command, null, 'REJECTED_PAYMENT_SUFFICIENCY');
                throw new PosTransactionViolation();
            }
        } catch (InvalidArgumentException) {
            $this->recordAudit($context, $command, null, 'REJECTED_MONEY');
            throw new PosTransactionViolation();
        }

        if (
            $command->tenderCategory() === TenderCategory::MANUAL_EXTERNAL
            && ! $command->tenderedAmount()->equals($total)
        ) {
            $this->recordAudit($context, $command, null, 'REJECTED_MANUAL_EXTERNAL_AMOUNT');
            throw new PosTransactionViolation();
        }

        foreach ($resolvedLines as $line) {
            $availableStock = $this->stock[$line['catalog_key']] ?? 0;
            if ($availableStock < $line['quantity']) {
                $this->recordAudit($context, $command, null, 'REJECTED_STOCK');
                throw new PosTransactionViolation();
            }
        }

        $saleId = 'sale-'.substr(hash('sha256', $idempotencyKey), 0, 24);
        $change = $command->tenderCategory() === TenderCategory::CASH
            ? $command->tenderedAmount()->subtract($total)
            : Money::fromAtomicUnits(0, $total->currency(), $total->scale());

        $receipt = new SaleReceipt(
            $saleId,
            $command->operationId(),
            $context->tenantId(),
            $context->actorId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            array_map(
                static fn (array $line): SaleLineResult => $line['result'],
                $resolvedLines,
            ),
            $total,
            $command->tenderCategory(),
            $command->tenderCategory()->evidenceMode(),
            $total,
            $change,
            $command->correlationId(),
        );

        $snapshot = [
            'stock' => $this->stock,
            'sales' => $this->sales,
            'paymentEffects' => $this->paymentEffects,
            'idempotency' => $this->idempotency,
            'audit' => $this->audit,
        ];

        try {
            foreach ($resolvedLines as $line) {
                $this->stock[$line['catalog_key']] -= $line['quantity'];
            }

            $this->sales[$context->tenantId().'|'.$saleId] = $receipt;
            $this->paymentEffects[$context->tenantId().'|'.$saleId] = [
                'tender_category' => $command->tenderCategory()->value,
                'evidence_mode' => $command->tenderCategory()->evidenceMode(),
                'applied_amount' => $total->canonicalFingerprintPart(),
            ];
            $this->idempotency[$idempotencyKey] = [
                'fingerprint' => $fingerprint,
                'receipt' => $receipt,
            ];
            $this->recordAudit($context, $command, $saleId, 'COMPLETED');

            return $receipt;
        } catch (Throwable $exception) {
            $this->stock = $snapshot['stock'];
            $this->sales = $snapshot['sales'];
            $this->paymentEffects = $snapshot['paymentEffects'];
            $this->idempotency = $snapshot['idempotency'];
            $this->audit = $snapshot['audit'];

            throw $exception;
        }
    }

    public function stockFor(string $tenantId, string $outletId, string $productId): ?int
    {
        return $this->stock[$this->catalogKey($tenantId, $outletId, $productId)] ?? null;
    }

    public function saleCount(): int
    {
        return count($this->sales);
    }

    public function paymentEffectCount(): int
    {
        return count($this->paymentEffects);
    }

    /** @return list<array<string, string>> */
    public function auditRecords(): array
    {
        return $this->audit;
    }

    private function catalogKey(string $tenantId, string $outletId, string $productId): string
    {
        return $tenantId.'|'.$outletId.'|'.$productId;
    }

    private function recordAudit(
        PosExecutionContext $context,
        SaleCommand $command,
        ?string $saleId,
        string $outcome,
    ): void {
        $this->audit[] = [
            'tenant' => $context->tenantId(),
            'actor' => $context->actorId(),
            'organization' => $context->organizationId(),
            'outlet' => $context->outletId(),
            'device' => $context->deviceId(),
            'correlation_id' => $command->correlationId(),
            'operation_id' => $command->operationId(),
            'sale_id' => $saleId ?? '',
            'outcome' => $outcome,
        ];
    }
}
