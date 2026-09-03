<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\ExpectedCashRepository;
use App\Application\Pos\ExpectedCashResult;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use OverflowException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelExpectedCashRepository implements ExpectedCashRepository
{
    private const OPENING_MODE = 'OPERATOR_OBSERVED_OPENING_CASH';
    private const CLOSING_MODE = 'OPERATOR_OBSERVED_CLOSING_CASH';
    private const VOID_MODE = 'FULL_SALE_VOID';
    private const REFUND_MODE = 'FULL_CASH_REFUND';

    public function __construct(private Connection $connection) {}

    public function deriveFrom(ShiftClosingCashResult $closingCashEvidence): ExpectedCashResult
    {
        if ($this->connection->transactionLevel() !== 0) {
            throw new PosTransactionViolation();
        }

        $driver = strtolower(trim($this->connection->getDriverName()));
        if ($driver === 'mysql') {
            $this->connection->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        } elseif ($driver !== 'sqlite') {
            throw new PosTransactionViolation();
        }

        try {
            return $this->connection->transaction(
                fn (): ExpectedCashResult => $this->deriveFromStableSnapshot($closingCashEvidence),
                1,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException|OverflowException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function deriveFromStableSnapshot(ShiftClosingCashResult $closingCashEvidence): ExpectedCashResult
    {
        if ($this->connection->transactionLevel() < 1) {
            throw new PosTransactionViolation();
        }

        $closingRows = $this->connection->table('oneqay_pos_shift_closing_cash_evidence')
            ->where('tenant_id', $closingCashEvidence->tenantId())
            ->where('evidence_id', $closingCashEvidence->evidenceId())
            ->get();
        if ($closingRows->count() !== 1) {
            throw new PosTransactionViolation();
        }
        $closing = $closingRows->first();
        if (! is_object($closing)) {
            throw new PosTransactionViolation();
        }

        $this->assertStringEquals($closing->tenant_id ?? null, $closingCashEvidence->tenantId());
        $this->assertStringEquals($closing->evidence_id ?? null, $closingCashEvidence->evidenceId());
        $this->assertStringEquals($closing->opening_cash_evidence_id ?? null, $closingCashEvidence->openingCashEvidenceId());
        $this->assertStringEquals($closing->shift_id ?? null, $closingCashEvidence->shiftId());
        $this->assertStringEquals($closing->operation_id ?? null, $closingCashEvidence->operationId());
        $this->assertStringEquals($closing->outlet_id ?? null, $closingCashEvidence->outletId());
        $this->assertStringEquals($closing->device_id ?? null, $closingCashEvidence->deviceId());
        $this->assertStringEquals($closing->evidence_mode ?? null, self::CLOSING_MODE);
        $this->assertStringEquals($closing->evidence_mode ?? null, $closingCashEvidence->evidenceMode());
        $this->assertStringEquals($closing->correlation_id ?? null, $closingCashEvidence->correlationId());

        $closingAt = $this->safeUnsignedBigIntToInt($closing->recorded_at_unix ?? null);
        if ($closingAt !== $closingCashEvidence->recordedAtUnix()) {
            throw new PosTransactionViolation();
        }

        $closingMoney = Money::fromAtomicUnits(
            $this->safeUnsignedBigIntToInt($closing->closing_cash_atomic ?? null),
            $this->requiredString($closing->currency ?? null),
            $this->safeScale($closing->currency_scale ?? null),
        );
        if (! $closingMoney->equals($closingCashEvidence->closingCash())) {
            throw new PosTransactionViolation();
        }

        $organizationId = $this->requiredString($closing->organization_id ?? null);
        $outletId = $this->requiredString($closing->outlet_id ?? null);
        $deviceId = $this->requiredString($closing->device_id ?? null);
        $shiftId = $this->requiredString($closing->shift_id ?? null);
        $openingEvidenceId = $this->requiredString($closing->opening_cash_evidence_id ?? null);

        $shiftRows = $this->connection->table('oneqay_pos_shifts')
            ->where('tenant_id', $closingCashEvidence->tenantId())
            ->where('shift_id', $shiftId)
            ->get();
        if ($shiftRows->count() !== 1) {
            throw new PosTransactionViolation();
        }
        $shift = $shiftRows->first();
        if (! is_object($shift)) {
            throw new PosTransactionViolation();
        }
        $this->assertStringEquals($shift->organization_id ?? null, $organizationId);
        $this->assertStringEquals($shift->outlet_id ?? null, $outletId);
        $this->assertStringEquals($shift->device_id ?? null, $deviceId);

        $openingRows = $this->connection->table('oneqay_pos_shift_opening_cash_evidence')
            ->where('tenant_id', $closingCashEvidence->tenantId())
            ->where('evidence_id', $openingEvidenceId)
            ->get();
        if ($openingRows->count() !== 1) {
            throw new PosTransactionViolation();
        }
        $opening = $openingRows->first();
        if (! is_object($opening)) {
            throw new PosTransactionViolation();
        }

        $this->assertStringEquals($opening->shift_id ?? null, $shiftId);
        $this->assertStringEquals($opening->organization_id ?? null, $organizationId);
        $this->assertStringEquals($opening->outlet_id ?? null, $outletId);
        $this->assertStringEquals($opening->device_id ?? null, $deviceId);
        $this->assertStringEquals($opening->evidence_mode ?? null, self::OPENING_MODE);

        $openingAt = $this->safeUnsignedBigIntToInt($opening->recorded_at_unix ?? null);
        if ($openingAt > $closingAt) {
            throw new PosTransactionViolation();
        }

        $expected = Money::fromAtomicUnits(
            $this->safeUnsignedBigIntToInt($opening->opening_cash_atomic ?? null),
            $this->requiredString($opening->currency ?? null),
            $this->safeScale($opening->currency_scale ?? null),
        );
        $this->assertMoneyBasis($expected, $closingMoney);

        $legacyNullSales = $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $closingCashEvidence->tenantId())
            ->where('organization_id', $organizationId)
            ->where('outlet_id', $outletId)
            ->where('device_id', $deviceId)
            ->whereNull('shift_id')
            ->get();
        foreach ($legacyNullSales as $legacy) {
            $tender = $this->requiredString($legacy->tender_category ?? null);
            if ($tender === TenderCategory::CASH->value
                && $this->safeUnsignedBigIntToInt($legacy->completed_at_unix ?? null) >= $openingAt) {
                throw new PosTransactionViolation();
            }
            if (! in_array($tender, [TenderCategory::CASH->value, TenderCategory::MANUAL_EXTERNAL->value], true)) {
                throw new PosTransactionViolation();
            }
        }

        $sales = $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $closingCashEvidence->tenantId())
            ->where('shift_id', $shiftId)
            ->get();

        foreach ($sales as $sale) {
            if (! is_object($sale)) {
                throw new PosTransactionViolation();
            }

            $this->assertStringEquals($sale->organization_id ?? null, $organizationId);
            $this->assertStringEquals($sale->outlet_id ?? null, $outletId);
            $this->assertStringEquals($sale->device_id ?? null, $deviceId);
            $this->assertStringEquals($sale->shift_id ?? null, $shiftId);

            $tender = $this->requiredString($sale->tender_category ?? null);
            if ($tender === TenderCategory::MANUAL_EXTERNAL->value) {
                $this->assertStringEquals($sale->evidence_mode ?? null, TenderCategory::MANUAL_EXTERNAL->evidenceMode());
                continue;
            }
            if ($tender !== TenderCategory::CASH->value) {
                throw new PosTransactionViolation();
            }
            $this->assertStringEquals($sale->evidence_mode ?? null, TenderCategory::CASH->evidenceMode());

            $saleAt = $this->safeUnsignedBigIntToInt($sale->completed_at_unix ?? null);
            $this->assertArithmeticEventWindow($saleAt, $openingAt, $closingAt);

            $saleMoney = Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($sale->applied_atomic ?? null),
                $this->requiredString($sale->currency ?? null),
                $this->safeScale($sale->currency_scale ?? null),
            );
            $this->assertMoneyBasis($expected, $saleMoney);
            if ($this->safeUnsignedBigIntToInt($sale->total_atomic ?? null) !== $saleMoney->atomicUnits()) {
                throw new PosTransactionViolation();
            }

            $saleId = $this->requiredString($sale->sale_id ?? null);
            $voidRows = $this->connection->table('oneqay_pos_sale_voids')
                ->where('tenant_id', $closingCashEvidence->tenantId())
                ->where('sale_id', $saleId)
                ->get();
            if ($voidRows->count() > 1) {
                throw new PosTransactionViolation();
            }
            $void = $voidRows->first();
            if ($void !== null && ! is_object($void)) {
                throw new PosTransactionViolation();
            }

            if (is_object($void)) {
                $this->assertStringEquals($void->organization_id ?? null, $organizationId);
                $this->assertStringEquals($void->outlet_id ?? null, $outletId);
                $this->assertStringEquals($void->tender_category ?? null, TenderCategory::CASH->value);
                $this->assertStringEquals($void->evidence_mode ?? null, self::VOID_MODE);
                $voidMoney = Money::fromAtomicUnits(
                    $this->safeUnsignedBigIntToInt($void->reversed_atomic ?? null),
                    $this->requiredString($void->currency ?? null),
                    $this->safeScale($void->currency_scale ?? null),
                );
                if (! $voidMoney->equals($saleMoney)) {
                    throw new PosTransactionViolation();
                }
            }

            $refundRows = $this->connection->table('oneqay_pos_sale_cash_refunds')
                ->where('tenant_id', $closingCashEvidence->tenantId())
                ->where('sale_id', $saleId)
                ->get();
            if ($refundRows->count() > 1) {
                throw new PosTransactionViolation();
            }
            $refund = $refundRows->first();
            if ($refund === null) {
                $expected = $expected->add($saleMoney);
                continue;
            }
            if (! is_object($refund) || ! is_object($void)) {
                throw new PosTransactionViolation();
            }

            $this->assertStringEquals($refund->void_id ?? null, $void->void_id ?? null);
            $this->assertStringEquals($refund->organization_id ?? null, $organizationId);
            $this->assertStringEquals($refund->outlet_id ?? null, $outletId);
            $this->assertStringEquals($refund->tender_category ?? null, TenderCategory::CASH->value);
            $this->assertStringEquals($refund->evidence_mode ?? null, self::REFUND_MODE);

            $refundMoney = Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($refund->refunded_atomic ?? null),
                $this->requiredString($refund->currency ?? null),
                $this->safeScale($refund->currency_scale ?? null),
            );
            if (! $refundMoney->equals($saleMoney)) {
                throw new PosTransactionViolation();
            }

            $refundAt = $this->safeUnsignedBigIntToInt($refund->refunded_at_unix ?? null);
            $this->assertArithmeticEventWindow($refundAt, $openingAt, $closingAt);
            // A fully refunded CASH sale has zero net expected-cash contribution.
            // Validation above proves the subtraction equals the exact applied amount.
            $saleMoney->subtract($refundMoney);
        }

        if ($expected->atomicUnits() < 0) {
            throw new PosTransactionViolation();
        }

        return new ExpectedCashResult(
            $closingCashEvidence->tenantId(),
            $organizationId,
            $outletId,
            $shiftId,
            $openingEvidenceId,
            $closingCashEvidence->evidenceId(),
            $closingAt,
            $expected,
        );
    }

    private function assertArithmeticEventWindow(int $eventAt, int $openingAt, int $closingAt): void
    {
        if ($eventAt < $openingAt || $eventAt >= $closingAt) {
            throw new PosTransactionViolation();
        }
    }

    private function assertMoneyBasis(Money $basis, Money $candidate): void
    {
        if ($basis->currency() !== $candidate->currency() || $basis->scale() !== $candidate->scale()) {
            throw new PosTransactionViolation();
        }
    }

    private function assertStringEquals(mixed $actual, mixed $expected): void
    {
        if (! is_string($actual) || ! is_string($expected) || $actual === '' || $expected === '' || ! hash_equals($expected, $actual)) {
            throw new PosTransactionViolation();
        }
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }

        return $value;
    }

    private function safeScale(mixed $value): int
    {
        $scale = $this->safeUnsignedBigIntToInt($value);
        if ($scale > 6) {
            throw new PosTransactionViolation();
        }

        return $scale;
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
