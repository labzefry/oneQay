<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftOpeningCommand;
use App\Application\Pos\ShiftOpeningRepository;
use App\Application\Pos\ShiftOpeningResult;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelShiftOpeningRepository implements ShiftOpeningRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function open(
        PosExecutionContext $context,
        ShiftOpeningCommand $command,
        string $correlationId,
        int $openedAtUnix,
    ): ShiftOpeningResult {
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
            $existing = $this->operationRow($context->tenantId(), $command->operationId());
            if ($existing !== null) {
                return $this->replayOrFail($existing, $fingerprint);
            }

            $shiftId = substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                32,
            );

            try {
                $this->connection->table('oneqay_pos_shifts')->insert([
                    'tenant_id' => $context->tenantId(),
                    'shift_id' => $shiftId,
                    'operation_id' => $command->operationId(),
                    'payload_fingerprint' => $fingerprint,
                    'actor_identity_id' => $context->actorId(),
                    'organization_id' => $context->organizationId(),
                    'outlet_id' => $context->outletId(),
                    'device_id' => $context->deviceId(),
                    'active_slot' => 1,
                    'correlation_id' => $correlationId,
                    'opened_at_unix' => $openedAtUnix,
                ]);
            } catch (Throwable) {
                $raced = $this->operationRow($context->tenantId(), $command->operationId());
                if ($raced !== null) {
                    return $this->replayOrFail($raced, $fingerprint);
                }

                throw new PosTransactionViolation();
            }

            return new ShiftOpeningResult(
                $shiftId,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $context->deviceId(),
                $correlationId,
                $openedAtUnix,
                true,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function operationRow(string $tenantId, string $operationId): ?object
    {
        return $this->connection->table('oneqay_pos_shifts')
            ->where('tenant_id', $tenantId)
            ->where('operation_id', $operationId)
            ->lockForUpdate()
            ->first();
    }

    private function replayOrFail(object $row, string $fingerprint): ShiftOpeningResult
    {
        if (! is_string($row->payload_fingerprint)
            || ! hash_equals($row->payload_fingerprint, $fingerprint)) {
            throw new PosTransactionViolation();
        }

        return new ShiftOpeningResult(
            (string) $row->shift_id,
            (string) $row->operation_id,
            (string) $row->tenant_id,
            (string) $row->outlet_id,
            (string) $row->device_id,
            (string) $row->correlation_id,
            (int) $row->opened_at_unix,
            (int) $row->active_slot === 1,
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
