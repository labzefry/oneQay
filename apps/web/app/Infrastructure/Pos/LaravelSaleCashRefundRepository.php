<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleCashRefundRepository;
use App\Application\Pos\SaleCashRefundResult;
use App\Domain\Pos\Money;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use OverflowException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelSaleCashRefundRepository implements SaleCashRefundRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function record(
        PosExecutionContext $context,
        SaleCashRefundCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): SaleCashRefundResult {
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
            $existingOperation = $this->connection->table('oneqay_pos_sale_cash_refunds')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existingOperation !== null) {
                if (! is_string($existingOperation->payload_fingerprint)
                    || ! hash_equals($existingOperation->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                return $this->hydrateResult($existingOperation);
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
                || ! hash_equals($context->outletId(), $sale->outlet_id)
                || ! is_string($sale->tender_category)
                || $sale->tender_category !== TenderCategory::CASH->value) {
                throw new PosTransactionViolation();
            }

            $void = $this->connection->table('oneqay_pos_sale_voids')
                ->where('tenant_id', $context->tenantId())
                ->where('sale_id', $command->saleId())
                ->lockForUpdate()
                ->first();

            if ($void === null
                || ! is_string($void->void_id)
                || $void->void_id === ''
                || ! is_string($void->organization_id)
                || ! hash_equals($sale->organization_id, $void->organization_id)
                || ! is_string($void->outlet_id)
                || ! hash_equals($sale->outlet_id, $void->outlet_id)
                || ! is_string($void->tender_category)
                || $void->tender_category !== TenderCategory::CASH->value
                || ! is_string($void->evidence_mode)
                || $void->evidence_mode !== 'FULL_SALE_VOID'
                || ! is_string($sale->currency)
                || ! is_string($void->currency)
                || ! hash_equals($sale->currency, $void->currency)
                || (int) $sale->currency_scale !== (int) $void->currency_scale) {
                throw new PosTransactionViolation();
            }

            $appliedAtomic = $this->safeUnsignedBigIntToInt($sale->applied_atomic);
            $reversedAtomic = $this->safeUnsignedBigIntToInt($void->reversed_atomic);
            if ($appliedAtomic !== $reversedAtomic) {
                throw new PosTransactionViolation();
            }

            $existingSaleRefund = $this->connection->table('oneqay_pos_sale_cash_refunds')
                ->where('tenant_id', $context->tenantId())
                ->where('sale_id', $command->saleId())
                ->lockForUpdate()
                ->first();

            if ($existingSaleRefund !== null) {
                throw new PosTransactionViolation();
            }

            $refundedAmount = Money::fromAtomicUnits(
                $appliedAtomic,
                $sale->currency,
                (int) $sale->currency_scale,
            );
            $refundId = 'refund-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                24,
            );

            $this->connection->table('oneqay_pos_sale_cash_refunds')->insert([
                'tenant_id' => $context->tenantId(),
                'refund_id' => $refundId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'sale_id' => $command->saleId(),
                'void_id' => $void->void_id,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'refunded_atomic' => $refundedAmount->atomicUnits(),
                'currency' => $refundedAmount->currency(),
                'currency_scale' => $refundedAmount->scale(),
                'tender_category' => TenderCategory::CASH->value,
                'evidence_mode' => 'FULL_CASH_REFUND',
                'correlation_id' => $correlationId,
                'refunded_at_unix' => $occurredAtUnix,
            ]);

            $this->recordRefundEvent(
                $context,
                $command,
                $correlationId,
                $occurredAtUnix,
            );

            return new SaleCashRefundResult(
                $refundId,
                $command->saleId(),
                $void->void_id,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $refundedAmount,
                TenderCategory::CASH,
                'FULL_CASH_REFUND',
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

    private function hydrateResult(object $refund): SaleCashRefundResult
    {
        return new SaleCashRefundResult(
            (string) $refund->refund_id,
            (string) $refund->sale_id,
            (string) $refund->void_id,
            (string) $refund->operation_id,
            (string) $refund->tenant_id,
            (string) $refund->outlet_id,
            Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($refund->refunded_atomic),
                (string) $refund->currency,
                (int) $refund->currency_scale,
            ),
            TenderCategory::from((string) $refund->tender_category),
            (string) $refund->evidence_mode,
            (string) $refund->correlation_id,
            $this->safeUnsignedBigIntToInt($refund->refunded_at_unix),
        );
    }

    private function recordRefundEvent(
        PosExecutionContext $context,
        SaleCashRefundCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): void {
        $eventId = substr(hash('sha256', implode('|', [
            $context->tenantId(),
            $command->saleId(),
            'REFUNDED',
            $command->operationId(),
        ])), 0, 32);

        $this->connection->table('oneqay_pos_sale_events')->insert([
            'tenant_id' => $context->tenantId(),
            'event_id' => $eventId,
            'sale_id' => $command->saleId(),
            'operation_id' => $command->operationId(),
            'actor_identity_id' => $context->actorId(),
            'event_type' => 'REFUNDED',
            'correlation_id' => $correlationId,
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
