<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\CashVarianceExplanationCommand;
use App\Application\Pos\CashVarianceExplanationRepository;
use App\Application\Pos\CashVarianceExplanationResult;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelCashVarianceExplanationRepository implements CashVarianceExplanationRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): CashVarianceExplanationResult {
        $this->assertOperational();
        $this->assertCanonicalBinding($context, $variance, $recordedAtUnix);

        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            (string) $variance->cutoffAtUnix(),
            (string) $variance->expectedCashAtomic(),
            (string) $variance->observedClosingAtomic(),
            (string) $variance->varianceAtomic(),
            $variance->direction(),
            $variance->currency(),
            (string) $variance->currencyScale(),
            $command->semanticFingerprintPart(),
        ]));

        try {
            $existingOperation = $this->connection
                ->table('oneqay_pos_cash_variance_explanation_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existingOperation !== null) {
                return $this->replayOrFail($existingOperation, $fingerprint);
            }

            $shift = $this->connection
                ->table('oneqay_pos_shifts')
                ->where('tenant_id', $variance->tenantId())
                ->where('shift_id', $variance->shiftId())
                ->lockForUpdate()
                ->first();

            if (
                $shift === null
                || ! is_string($shift->organization_id)
                || ! hash_equals($variance->organizationId(), $shift->organization_id)
                || ! is_string($shift->outlet_id)
                || ! hash_equals($variance->outletId(), $shift->outlet_id)
            ) {
                throw new PosTransactionViolation();
            }

            $openingEvidence = $this->connection
                ->table('oneqay_pos_shift_opening_cash_evidence')
                ->where('tenant_id', $variance->tenantId())
                ->where('evidence_id', $variance->openingCashEvidenceId())
                ->lockForUpdate()
                ->first();

            if (
                $openingEvidence === null
                || ! is_string($openingEvidence->shift_id)
                || ! hash_equals($variance->shiftId(), $openingEvidence->shift_id)
                || ! is_string($openingEvidence->organization_id)
                || ! hash_equals($variance->organizationId(), $openingEvidence->organization_id)
                || ! is_string($openingEvidence->outlet_id)
                || ! hash_equals($variance->outletId(), $openingEvidence->outlet_id)
                || ! is_string($openingEvidence->currency)
                || ! hash_equals($variance->currency(), $openingEvidence->currency)
                || (int) $openingEvidence->currency_scale !== $variance->currencyScale()
            ) {
                throw new PosTransactionViolation();
            }

            $closingEvidence = $this->connection
                ->table('oneqay_pos_shift_closing_cash_evidence')
                ->where('tenant_id', $variance->tenantId())
                ->where('evidence_id', $variance->closingCashEvidenceId())
                ->lockForUpdate()
                ->first();

            if (
                $closingEvidence === null
                || ! is_string($closingEvidence->shift_id)
                || ! hash_equals($variance->shiftId(), $closingEvidence->shift_id)
                || ! is_string($closingEvidence->opening_cash_evidence_id)
                || ! hash_equals($variance->openingCashEvidenceId(), $closingEvidence->opening_cash_evidence_id)
                || ! is_string($closingEvidence->organization_id)
                || ! hash_equals($variance->organizationId(), $closingEvidence->organization_id)
                || ! is_string($closingEvidence->outlet_id)
                || ! hash_equals($variance->outletId(), $closingEvidence->outlet_id)
                || ! is_string($closingEvidence->currency)
                || ! hash_equals($variance->currency(), $closingEvidence->currency)
                || (int) $closingEvidence->currency_scale !== $variance->currencyScale()
                || $this->safeUnsignedBigIntToInt($closingEvidence->recorded_at_unix) !== $variance->cutoffAtUnix()
                || $this->safeUnsignedBigIntToInt($closingEvidence->closing_cash_atomic) !== $variance->observedClosingAtomic()
            ) {
                throw new PosTransactionViolation();
            }

            $existingVarianceExplanation = $this->connection
                ->table('oneqay_pos_cash_variance_explanation_evidence')
                ->where('tenant_id', $variance->tenantId())
                ->where('closing_cash_evidence_id', $variance->closingCashEvidenceId())
                ->lockForUpdate()
                ->first();

            if ($existingVarianceExplanation !== null) {
                throw new PosTransactionViolation();
            }

            $evidenceId = 'varexp-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                25,
            );

            $this->connection->table('oneqay_pos_cash_variance_explanation_evidence')->insert([
                'tenant_id' => $variance->tenantId(),
                'evidence_id' => $evidenceId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'shift_id' => $variance->shiftId(),
                'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
                'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $variance->organizationId(),
                'outlet_id' => $variance->outletId(),
                'cutoff_at_unix' => $variance->cutoffAtUnix(),
                'expected_cash_atomic' => $variance->expectedCashAtomic(),
                'observed_closing_cash_atomic' => $variance->observedClosingAtomic(),
                'variance_atomic' => $variance->varianceAtomic(),
                'variance_direction' => $variance->direction(),
                'currency' => $variance->currency(),
                'currency_scale' => $variance->currencyScale(),
                'explanation_text' => $command->explanationText(),
                'correlation_id' => $correlationId,
                'recorded_at_unix' => $recordedAtUnix,
            ]);

            return new CashVarianceExplanationResult(
                $evidenceId,
                $command->operationId(),
                $variance->tenantId(),
                $variance->organizationId(),
                $variance->outletId(),
                $variance->shiftId(),
                $variance->openingCashEvidenceId(),
                $variance->closingCashEvidenceId(),
                $context->actorId(),
                $variance->cutoffAtUnix(),
                $variance->expectedCashAtomic(),
                $variance->observedClosingAtomic(),
                $variance->varianceAtomic(),
                $variance->direction(),
                $variance->currency(),
                $variance->currencyScale(),
                $command->explanationText(),
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

    private function replayOrFail(object $row, string $fingerprint): CashVarianceExplanationResult
    {
        if (! is_string($row->payload_fingerprint) || ! hash_equals($row->payload_fingerprint, $fingerprint)) {
            throw new PosTransactionViolation();
        }

        $varianceAtomic = $this->safeSignedBigIntToInt($row->variance_atomic);
        $direction = (string) $row->variance_direction;
        $validOver = $direction === CashVarianceResult::DIRECTION_OVER && $varianceAtomic > 0;
        $validShort = $direction === CashVarianceResult::DIRECTION_SHORT && $varianceAtomic < 0;

        if (! $validOver && ! $validShort) {
            throw new PosTransactionViolation();
        }

        return new CashVarianceExplanationResult(
            (string) $row->evidence_id,
            (string) $row->operation_id,
            (string) $row->tenant_id,
            (string) $row->organization_id,
            (string) $row->outlet_id,
            (string) $row->shift_id,
            (string) $row->opening_cash_evidence_id,
            (string) $row->closing_cash_evidence_id,
            (string) $row->actor_identity_id,
            $this->safeUnsignedBigIntToInt($row->cutoff_at_unix),
            $this->safeUnsignedBigIntToInt($row->expected_cash_atomic),
            $this->safeUnsignedBigIntToInt($row->observed_closing_cash_atomic),
            $varianceAtomic,
            $direction,
            (string) $row->currency,
            (int) $row->currency_scale,
            (string) $row->explanation_text,
            (string) $row->correlation_id,
            $this->safeUnsignedBigIntToInt($row->recorded_at_unix),
        );
    }

    private function assertCanonicalBinding(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        int $recordedAtUnix,
    ): void {
        if (
            $recordedAtUnix <= 0
            || $context->tenantId() !== $variance->tenantId()
            || $context->organizationId() !== $variance->organizationId()
            || $context->outletId() !== $variance->outletId()
            || trim($variance->shiftId()) === ''
            || trim($variance->openingCashEvidenceId()) === ''
            || trim($variance->closingCashEvidenceId()) === ''
            || $variance->cutoffAtUnix() <= 0
            || $variance->expectedCashAtomic() < 0
            || $variance->observedClosingAtomic() < 0
            || preg_match('/\A[A-Z]{3}\z/', $variance->currency()) !== 1
            || $variance->currencyScale() < 0
            || $variance->currencyScale() > 6
        ) {
            throw new PosTransactionViolation();
        }

        $validOver = $variance->direction() === CashVarianceResult::DIRECTION_OVER
            && $variance->varianceAtomic() > 0;
        $validShort = $variance->direction() === CashVarianceResult::DIRECTION_SHORT
            && $variance->varianceAtomic() < 0;

        if (! $validOver && ! $validShort) {
            throw new PosTransactionViolation();
        }
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

        if (
            strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)
        ) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }

    private function safeSignedBigIntToInt(mixed $value): int
    {
        if (is_int($value)) {
            if ($value === PHP_INT_MIN) {
                throw new PosTransactionViolation();
            }

            return $value;
        }

        if (! is_string($value) || preg_match('/\A-?[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }

        $negative = str_starts_with($value, '-');
        $digits = $negative ? substr($value, 1) : $value;
        $digits = ltrim($digits, '0');
        $digits = $digits === '' ? '0' : $digits;
        $maximum = (string) PHP_INT_MAX;

        if (
            strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)
        ) {
            throw new PosTransactionViolation();
        }

        $integer = (int) $digits;

        return $negative ? -$integer : $integer;
    }
}
