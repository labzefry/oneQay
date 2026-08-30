<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Infrastructure\Pos;

use App\Application\Pos\DurablePosSaleRepository;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
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
    ) {}

    public function complete(PosExecutionContext $context, SaleCommand $command, int $occurredAtUnix): SaleReceipt
    {
        $this->assertOperational();

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

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled) {
            throw new PosTransactionViolation();
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }
}
