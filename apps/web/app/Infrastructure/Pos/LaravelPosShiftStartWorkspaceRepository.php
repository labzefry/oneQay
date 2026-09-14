<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosShiftStartWorkspaceRepository;
use App\Application\Pos\PosShiftStartWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\Money;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosShiftStartWorkspaceRepository implements PosShiftStartWorkspaceRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $workspaceEnabled,
        private bool $shiftOpeningEnabled,
        private bool $openingCashEnabled,
    ) {}

    public function snapshot(PosExecutionContext $context): PosShiftStartWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $shift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('active_slot', 1)
                ->first();

            if ($shift === null) {
                return $this->emptySnapshot($context);
            }

            if (! is_string($shift->shift_id)
                || $shift->shift_id === ''
                || ! isset($shift->opened_at_unix)) {
                throw new PosTransactionViolation();
            }

            $openedAtUnix = $this->safeUnsignedBigIntToInt($shift->opened_at_unix);
            if ($openedAtUnix <= 0) {
                throw new PosTransactionViolation();
            }

            $evidence = $this->connection->table('oneqay_pos_shift_opening_cash_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('shift_id', $shift->shift_id)
                ->first();

            if ($evidence === null) {
                return new PosShiftStartWorkspaceSnapshot(
                    $context->tenantId(),
                    $context->organizationId(),
                    $context->outletId(),
                    $context->deviceId(),
                    $shift->shift_id,
                    $openedAtUnix,
                    null,
                    null,
                    null,
                    null,
                );
            }

            if (! is_string($evidence->evidence_id)
                || $evidence->evidence_id === ''
                || ! is_string($evidence->currency)
                || ! isset($evidence->currency_scale)
                || ! isset($evidence->opening_cash_atomic)
                || ! is_string($evidence->evidence_mode)
                || $evidence->evidence_mode === ''
                || ! isset($evidence->recorded_at_unix)) {
                throw new PosTransactionViolation();
            }

            $openingCash = Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($evidence->opening_cash_atomic),
                $evidence->currency,
                (int) $evidence->currency_scale,
            );
            $recordedAtUnix = $this->safeUnsignedBigIntToInt($evidence->recorded_at_unix);
            if ($recordedAtUnix <= 0) {
                throw new PosTransactionViolation();
            }

            return new PosShiftStartWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $shift->shift_id,
                $openedAtUnix,
                $evidence->evidence_id,
                $openingCash,
                $evidence->evidence_mode,
                $recordedAtUnix,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function emptySnapshot(PosExecutionContext $context): PosShiftStartWorkspaceSnapshot
    {
        return new PosShiftStartWorkspaceSnapshot(
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->workspaceEnabled
            || ! $this->shiftOpeningEnabled
            || ! $this->openingCashEnabled) {
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
