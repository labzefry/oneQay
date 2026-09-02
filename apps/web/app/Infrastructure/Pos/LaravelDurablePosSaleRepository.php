<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Infrastructure\Pos;

use App\Application\Pos\DurablePosSaleRepository;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\SaleVoidResult;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\SaleLineResult;
use App\Domain\Pos\SaleReceipt;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use OverflowException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelDurablePosSaleRepository implements DurablePosSaleRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
        private bool $voidFeatureEnabled,
    ) {}

    public function complete(PosExecutionContext $context, SaleCommand $command, int $occurredAtUnix): SaleReceipt
    {
        $this->assertCompletionOperational();

        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $command->semanticFingerprintPart(),
        ]));

        try {
            $existing = $this->connection->table('oneqay_pos_sales')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (! is_string($existing->payload_fingerprint)
                    || ! hash_equals($existing->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                $receipt = $this->hydrateReceipt($context->tenantId(), $existing);
                $this->recordEvent($context, $command, $receipt->saleId(), 'REPLAYED', $occurredAtUnix);
                return $receipt;
            }

            $activeShift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('active_slot', 1)
                ->lockForUpdate()
                ->first();

            if ($activeShift === null) {
                throw new PosTransactionViolation();
            }

            $resolved = [];
            $total = null;
            foreach ($command->cart()->lines() as $cartLine) {
                $catalog = $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', $context->outletId())
                    ->where('product_id', $cartLine->productId()->value())
                    ->where('active', true)
                    ->lockForUpdate()
                    ->first();

                if ($catalog === null || (int) $catalog->available_quantity < $cartLine->quantity()) {
                    throw new PosTransactionViolation();
                }

                $unitPrice = Money::fromAtomicUnits(
                    (int) $catalog->unit_price_atomic,
                    (string) $catalog->currency,
                    (int) $catalog->currency_scale,
                );
                $lineTotal = $unitPrice->multiply($cartLine->quantity());
                $total = $total === null ? $lineTotal : $total->add($lineTotal);
                $resolved[] = [
                    'product_id' => $cartLine->productId()->value(),
                    'quantity' => $cartLine->quantity(),
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            if (! $total instanceof Money || $command->tenderedAmount()->compare($total) < 0) {
                throw new PosTransactionViolation();
            }
            if ($command->tenderCategory() === TenderCategory::MANUAL_EXTERNAL
                && ! $command->tenderedAmount()->equals($total)) {
                throw new PosTransactionViolation();
            }

            $saleId = 'sale-'.substr(hash('sha256', $context->tenantId().'|'.$command->operationId()), 0, 24);
            $change = $command->tenderCategory() === TenderCategory::CASH
                ? $command->tenderedAmount()->subtract($total)
                : Money::fromAtomicUnits(0, $total->currency(), $total->scale());

            foreach ($resolved as $line) {
                $updated = $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', $context->outletId())
                    ->where('product_id', $line['product_id'])
                    ->where('active', true)
                    ->where('available_quantity', '>=', $line['quantity'])
                    ->decrement('available_quantity', $line['quantity']);
                if ($updated !== 1) {
                    throw new PosTransactionViolation();
                }
            }

            $this->connection->table('oneqay_pos_sales')->insert([
                'tenant_id' => $context->tenantId(),
                'sale_id' => $saleId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'total_atomic' => $total->atomicUnits(),
                'currency' => $total->currency(),
                'currency_scale' => $total->scale(),
                'tender_category' => $command->tenderCategory()->value,
                'evidence_mode' => $command->tenderCategory()->evidenceMode(),
                'applied_atomic' => $total->atomicUnits(),
                'change_atomic' => $change->atomicUnits(),
                'correlation_id' => $command->correlationId(),
                'completed_at_unix' => $occurredAtUnix,
            ]);

            $lineResults = [];
            foreach ($resolved as $index => $line) {
                $this->connection->table('oneqay_pos_sale_lines')->insert([
                    'tenant_id' => $context->tenantId(),
                    'sale_id' => $saleId,
                    'line_no' => $index + 1,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price_atomic' => $line['unit_price']->atomicUnits(),
                    'line_total_atomic' => $line['line_total']->atomicUnits(),
                    'currency' => $line['unit_price']->currency(),
                    'currency_scale' => $line['unit_price']->scale(),
                ]);
                $lineResults[] = new SaleLineResult(
                    ProductId::fromString($line['product_id']),
                    $line['quantity'],
                    $line['unit_price'],
                    $line['line_total'],
                );
            }

            $this->recordEvent($context, $command, $saleId, 'COMPLETED', $occurredAtUnix);

            return new SaleReceipt(
                $saleId,
                $command->operationId(),
                $context->tenantId(),
                $context->actorId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $lineResults,
                $total,
                $command->tenderCategory(),
                $command->tenderCategory()->evidenceMode(),
                $total,
                $change,
                $command->correlationId(),
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException|OverflowException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }


    public function voidCompletedSale(
        PosExecutionContext $context,
        SaleVoidCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): SaleVoidResult {
        $this->assertVoidOperational();

        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $command->semanticFingerprintPart(),
        ]));

        try {
            $existing = $this->connection->table('oneqay_pos_sale_voids')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (! is_string($existing->payload_fingerprint)
                    || ! hash_equals($existing->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                return $this->hydrateVoidResult($existing);
            }

            $sale = $this->connection->table('oneqay_pos_sales')
                ->where('tenant_id', $context->tenantId())
                ->where('sale_id', $command->saleId())
                ->lockForUpdate()
                ->first();

            if ($sale === null
                || ! is_string($sale->organization_id)
                || ! hash_equals($context->organizationId(), $sale->organization_id)
                || ! is_string($sale->outlet_id)
                || ! hash_equals($context->outletId(), $sale->outlet_id)) {
                throw new PosTransactionViolation();
            }

            $alreadyVoided = $this->connection->table('oneqay_pos_sale_voids')
                ->where('tenant_id', $context->tenantId())
                ->where('sale_id', $command->saleId())
                ->lockForUpdate()
                ->first();

            if ($alreadyVoided !== null) {
                throw new PosTransactionViolation();
            }

            $lines = $this->connection->table('oneqay_pos_sale_lines')
                ->where('tenant_id', $context->tenantId())
                ->where('sale_id', $command->saleId())
                ->orderBy('line_no')
                ->lockForUpdate()
                ->get();

            if ($lines->count() === 0) {
                throw new PosTransactionViolation();
            }

            $restoreByProduct = [];
            foreach ($lines as $line) {
                $productId = is_string($line->product_id) ? $line->product_id : '';
                $quantity = (int) $line->quantity;

                if ($productId === '' || $quantity <= 0) {
                    throw new PosTransactionViolation();
                }

                $currentRestore = $restoreByProduct[$productId] ?? 0;
                if ($quantity > PHP_INT_MAX - $currentRestore) {
                    throw new PosTransactionViolation();
                }

                $restoreByProduct[$productId] = $currentRestore + $quantity;
            }

            foreach ($restoreByProduct as $productId => $quantity) {
                $catalog = $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', (string) $sale->outlet_id)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if ($catalog === null) {
                    throw new PosTransactionViolation();
                }

                $available = $this->safeUnsignedBigIntToInt($catalog->available_quantity);
                if ($quantity > PHP_INT_MAX - $available) {
                    throw new PosTransactionViolation();
                }
            }

            foreach ($restoreByProduct as $productId => $quantity) {
                $updated = $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', (string) $sale->outlet_id)
                    ->where('product_id', $productId)
                    ->increment('available_quantity', $quantity);

                if ($updated !== 1) {
                    throw new PosTransactionViolation();
                }
            }

            $reversed = Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($sale->applied_atomic),
                (string) $sale->currency,
                (int) $sale->currency_scale,
            );
            $tenderCategory = TenderCategory::from((string) $sale->tender_category);
            $voidId = 'void-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                24,
            );
            $evidenceMode = 'FULL_SALE_VOID';

            $this->connection->table('oneqay_pos_sale_voids')->insert([
                'tenant_id' => $context->tenantId(),
                'void_id' => $voidId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'sale_id' => $command->saleId(),
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'reversed_atomic' => $reversed->atomicUnits(),
                'currency' => $reversed->currency(),
                'currency_scale' => $reversed->scale(),
                'tender_category' => $tenderCategory->value,
                'evidence_mode' => $evidenceMode,
                'correlation_id' => $correlationId,
                'voided_at_unix' => $occurredAtUnix,
            ]);

            $this->recordVoidEvent(
                $context,
                $command,
                $correlationId,
                $occurredAtUnix,
            );

            return new SaleVoidResult(
                $voidId,
                $command->saleId(),
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $reversed,
                $tenderCategory,
                $evidenceMode,
                $correlationId,
                $occurredAtUnix,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException|OverflowException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function hydrateVoidResult(object $void): SaleVoidResult
    {
        return new SaleVoidResult(
            (string) $void->void_id,
            (string) $void->sale_id,
            (string) $void->operation_id,
            (string) $void->tenant_id,
            (string) $void->outlet_id,
            Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($void->reversed_atomic),
                (string) $void->currency,
                (int) $void->currency_scale,
            ),
            TenderCategory::from((string) $void->tender_category),
            (string) $void->evidence_mode,
            (string) $void->correlation_id,
            $this->safeUnsignedBigIntToInt($void->voided_at_unix),
        );
    }

    private function recordVoidEvent(
        PosExecutionContext $context,
        SaleVoidCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): void {
        $eventId = substr(hash('sha256', implode('|', [
            $context->tenantId(),
            $command->saleId(),
            'VOIDED',
            $command->operationId(),
        ])), 0, 32);

        $this->connection->table('oneqay_pos_sale_events')->insert([
            'tenant_id' => $context->tenantId(),
            'event_id' => $eventId,
            'sale_id' => $command->saleId(),
            'operation_id' => $command->operationId(),
            'actor_identity_id' => $context->actorId(),
            'event_type' => 'VOIDED',
            'correlation_id' => $correlationId,
            'occurred_at_unix' => $occurredAtUnix,
        ]);
    }

    private function hydrateReceipt(string $tenantId, object $sale): SaleReceipt
    {
        $rows = $this->connection->table('oneqay_pos_sale_lines')
            ->where('tenant_id', $tenantId)
            ->where('sale_id', (string) $sale->sale_id)
            ->orderBy('line_no')
            ->get();

        $lines = [];
        foreach ($rows as $row) {
            $unit = Money::fromAtomicUnits((int) $row->unit_price_atomic, (string) $row->currency, (int) $row->currency_scale);
            $lines[] = new SaleLineResult(
                ProductId::fromString((string) $row->product_id),
                (int) $row->quantity,
                $unit,
                Money::fromAtomicUnits((int) $row->line_total_atomic, (string) $row->currency, (int) $row->currency_scale),
            );
        }

        $total = Money::fromAtomicUnits((int) $sale->total_atomic, (string) $sale->currency, (int) $sale->currency_scale);

        return new SaleReceipt(
            (string) $sale->sale_id,
            (string) $sale->operation_id,
            (string) $sale->tenant_id,
            (string) $sale->actor_identity_id,
            (string) $sale->organization_id,
            (string) $sale->outlet_id,
            (string) $sale->device_id,
            $lines,
            $total,
            TenderCategory::from((string) $sale->tender_category),
            (string) $sale->evidence_mode,
            Money::fromAtomicUnits((int) $sale->applied_atomic, (string) $sale->currency, (int) $sale->currency_scale),
            Money::fromAtomicUnits((int) $sale->change_atomic, (string) $sale->currency, (int) $sale->currency_scale),
            (string) $sale->correlation_id,
        );
    }

    private function recordEvent(PosExecutionContext $context, SaleCommand $command, string $saleId, string $eventType, int $occurredAtUnix): void
    {
        $eventId = substr(hash('sha256', implode('|', [
            $context->tenantId(),
            $saleId,
            $eventType,
            $command->correlationId(),
            (string) $occurredAtUnix,
        ])), 0, 32);

        $this->connection->table('oneqay_pos_sale_events')->insert([
            'tenant_id' => $context->tenantId(),
            'event_id' => $eventId,
            'sale_id' => $saleId,
            'operation_id' => $command->operationId(),
            'actor_identity_id' => $context->actorId(),
            'event_type' => $eventType,
            'correlation_id' => $command->correlationId(),
            'occurred_at_unix' => $occurredAtUnix,
        ]);
    }

    private function assertCompletionOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled) {
            throw new PosTransactionViolation();
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function assertVoidOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled || ! $this->voidFeatureEnabled) {
            throw new PosTransactionViolation();
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function safeUnsignedBigIntToInt(mixed $value): int
    {
        if (is_int($value)) {
            if ($value < 0) {
                throw new PosTransactionViolation();
            }

            return $value;
        }

        if (! is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }

        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximum = (string) PHP_INT_MAX;

        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }
}

// Sprint48 JRN-005 Sprint46 compatibility preservation anchor.
