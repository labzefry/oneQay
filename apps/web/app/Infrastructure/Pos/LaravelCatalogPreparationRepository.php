<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\CatalogPreparationCommand;
use App\Application\Pos\CatalogPreparationRepository;
use App\Application\Pos\CatalogPreparationResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\Money;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelCatalogPreparationRepository implements CatalogPreparationRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function prepare(
        PosExecutionContext $context,
        CatalogPreparationCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): CatalogPreparationResult {
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
            $existing = $this->connection->table('oneqay_pos_catalog_preparation_journal')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (! is_string($existing->payload_fingerprint)
                    || ! hash_equals($existing->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                return $this->hydrateResult($existing);
            }

            $catalog = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->lockForUpdate()
                ->first();

            $beforeExists = $catalog !== null;
            $beforeDisplayName = $beforeExists ? (string) $catalog->display_name : null;
            $beforeUnitPriceAtomic = $beforeExists ? (int) $catalog->unit_price_atomic : null;
            $beforeCurrency = $beforeExists ? (string) $catalog->currency : null;
            $beforeCurrencyScale = $beforeExists ? (int) $catalog->currency_scale : null;
            $beforeSellable = $beforeExists ? (bool) $catalog->active : null;

            if ($beforeExists) {
                $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', $context->outletId())
                    ->where('product_id', $command->productId()->value())
                    ->update([
                        'display_name' => $command->displayName(),
                        'unit_price_atomic' => $command->unitPrice()->atomicUnits(),
                        'currency' => $command->unitPrice()->currency(),
                        'currency_scale' => $command->unitPrice()->scale(),
                        'active' => $command->sellable(),
                    ]);
            } else {
                $this->connection->table('oneqay_pos_sale_catalog_items')->insert([
                    'tenant_id' => $context->tenantId(),
                    'outlet_id' => $context->outletId(),
                    'product_id' => $command->productId()->value(),
                    'display_name' => $command->displayName(),
                    'unit_price_atomic' => $command->unitPrice()->atomicUnits(),
                    'currency' => $command->unitPrice()->currency(),
                    'currency_scale' => $command->unitPrice()->scale(),
                    'available_quantity' => 0,
                    'active' => $command->sellable(),
                ]);
            }

            $mutationId = substr(hash('sha256', $context->tenantId().'|'.$command->operationId()), 0, 32);

            $this->connection->table('oneqay_pos_catalog_preparation_journal')->insert([
                'tenant_id' => $context->tenantId(),
                'mutation_id' => $mutationId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'product_id' => $command->productId()->value(),
                'before_exists' => $beforeExists,
                'before_display_name' => $beforeDisplayName,
                'before_unit_price_atomic' => $beforeUnitPriceAtomic,
                'before_currency' => $beforeCurrency,
                'before_currency_scale' => $beforeCurrencyScale,
                'before_sellable' => $beforeSellable,
                'after_display_name' => $command->displayName(),
                'after_unit_price_atomic' => $command->unitPrice()->atomicUnits(),
                'after_currency' => $command->unitPrice()->currency(),
                'after_currency_scale' => $command->unitPrice()->scale(),
                'after_sellable' => $command->sellable(),
                'correlation_id' => $correlationId,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            return new CatalogPreparationResult(
                $mutationId,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $command->productId()->value(),
                $command->displayName(),
                $command->unitPrice(),
                $command->sellable(),
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function hydrateResult(object $journal): CatalogPreparationResult
    {
        return new CatalogPreparationResult(
            (string) $journal->mutation_id,
            (string) $journal->operation_id,
            (string) $journal->tenant_id,
            (string) $journal->outlet_id,
            (string) $journal->product_id,
            (string) $journal->after_display_name,
            Money::fromAtomicUnits(
                (int) $journal->after_unit_price_atomic,
                (string) $journal->after_currency,
                (int) $journal->after_currency_scale,
            ),
            (bool) $journal->after_sellable,
        );
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
