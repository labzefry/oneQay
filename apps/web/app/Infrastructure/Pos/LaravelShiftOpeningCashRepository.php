<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftOpeningCashCommand;
use App\Application\Pos\ShiftOpeningCashRepository;
use App\Application\Pos\ShiftOpeningCashResult;
use App\Domain\Pos\Money;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelShiftOpeningCashRepository implements ShiftOpeningCashRepository
{
    private const EVIDENCE_MODE = 'OPERATOR_OBSERVED_OPENING_CASH';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function record(
        PosExecutionContext $context,
        ShiftOpeningCashCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): ShiftOpeningCashResult {
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
            $existingOperation = $this->connection
                ->table('oneqay_pos_shift_opening_cash_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existingOperation !== null) {
                return $this->replayOrFail($existingOperation, $fingerprint);
            }

            $shift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('active_slot', 1)
                ->lockForUpdate()
                ->first();

            if ($shift === null
                || ! is_string($shift->organization_id)
                || ! hash_equals($context->organizationId(), $shift->organization_id)
                || ! is_string($shift->shift_id)
                || $shift->shift_id === '') {
                throw new PosTransactionViolation();
            }

            $existingShiftEvidence = $this->connection
                ->table('oneqay_pos_shift_opening_cash_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $shift->shift_id)
                ->lockForUpdate()
                ->first();

            if ($existingShiftEvidence !== null) {
                throw new PosTransactionViolation();
            }

            $evidenceId = 'cashopen-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                23,
            );

            $openingCash = $command->openingCash();

            $this->connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
                'tenant_id' => $context->tenantId(),
                'evidence_id' => $evidenceId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'shift_id' => $shift->shift_id,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'opening_cash_atomic' => $openingCash->atomicUnits(),
                'currency' => $openingCash->currency(),
                'currency_scale' => $openingCash->scale(),
                'evidence_mode' => self::EVIDENCE_MODE,
                'correlation_id' => $correlationId,
                'recorded_at_unix' => $recordedAtUnix,
            ]);

            return new ShiftOpeningCashResult(
                $evidenceId,
                $shift->shift_id,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $context->deviceId(),
                $openingCash,
                self::EVIDENCE_MODE,
                $correlationId,
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

    private function replayOrFail(object $row, string $fingerprint): ShiftOpeningCashResult
    {
        if (! is_string($row->payload_fingerprint)
            || ! hash_equals($row->payload_fingerprint, $fingerprint)
            || ! is_string($row->evidence_mode)
            || ! hash_equals(self::EVIDENCE_MODE, $row->evidence_mode)) {
            throw new PosTransactionViolation();
        }

        return new ShiftOpeningCashResult(
            (string) $row->evidence_id,
            (string) $row->shift_id,
            (string) $row->operation_id,
            (string) $row->tenant_id,
            (string) $row->outlet_id,
            (string) $row->device_id,
            Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($row->opening_cash_atomic),
                (string) $row->currency,
                (int) $row->currency_scale,
            ),
            (string) $row->evidence_mode,
            (string) $row->correlation_id,
            $this->safeUnsignedBigIntToInt($row->recorded_at_unix),
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
