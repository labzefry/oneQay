<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosCatalogChangeHistoryWorkspaceRepository;
use App\Application\Pos\PosCatalogChangeHistoryWorkspaceSnapshot;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosCatalogChangeHistoryWorkspaceRepository implements PosCatalogChangeHistoryWorkspaceRepository
{
    private const VISIBLE_LIMIT = 200;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $workspaceEnabled,
        private bool $catalogPreparationEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosCatalogChangeHistoryWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $rows = $this->connection->table('oneqay_pos_catalog_preparation_journal')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->orderByDesc('occurred_at_unix')
                ->orderByDesc('mutation_id')
                ->limit(self::VISIBLE_LIMIT + 1)
                ->get([
                    'mutation_id',
                    'operation_id',
                    'payload_fingerprint',
                    'actor_identity_id',
                    'organization_id',
                    'outlet_id',
                    'device_id',
                    'product_id',
                    'before_exists',
                    'before_display_name',
                    'before_unit_price_atomic',
                    'before_currency',
                    'before_currency_scale',
                    'before_sellable',
                    'after_display_name',
                    'after_unit_price_atomic',
                    'after_currency',
                    'after_currency_scale',
                    'after_sellable',
                    'correlation_id',
                    'occurred_at_unix',
                ]);

            $truncated = $rows->count() > self::VISIBLE_LIMIT;
            $changes = [];
            foreach ($rows->take(self::VISIBLE_LIMIT) as $row) {
                $mutationId = $this->hexString($row->mutation_id ?? null, 32);
                $operationId = $this->stableIdentifier($row->operation_id ?? null);
                $this->hexString($row->payload_fingerprint ?? null, 64);
                $actorIdentityId = $this->requiredString($row->actor_identity_id ?? null);
                $organizationId = $this->requiredString($row->organization_id ?? null);
                $outletId = $this->requiredString($row->outlet_id ?? null);
                $deviceId = $this->requiredString($row->device_id ?? null);
                $productId = $this->requiredString($row->product_id ?? null);
                $beforeExists = $this->boolean($row->before_exists ?? null);
                $correlationId = $this->stableIdentifier($row->correlation_id ?? null);
                $occurredAtUnix = $this->positiveInteger($row->occurred_at_unix ?? null);

                if ($organizationId !== $context->organizationId() || $outletId !== $context->outletId()) {
                    throw new PosTransactionViolation();
                }

                $before = null;
                if ($beforeExists) {
                    $before = $this->state(
                        $row->before_display_name ?? null,
                        $row->before_unit_price_atomic ?? null,
                        $row->before_currency ?? null,
                        $row->before_currency_scale ?? null,
                        $row->before_sellable ?? null,
                    );
                } elseif (($row->before_display_name ?? null) !== null
                    || ($row->before_unit_price_atomic ?? null) !== null
                    || ($row->before_currency ?? null) !== null
                    || ($row->before_currency_scale ?? null) !== null
                    || ($row->before_sellable ?? null) !== null) {
                    throw new PosTransactionViolation();
                }

                $after = $this->state(
                    $row->after_display_name ?? null,
                    $row->after_unit_price_atomic ?? null,
                    $row->after_currency ?? null,
                    $row->after_currency_scale ?? null,
                    $row->after_sellable ?? null,
                );

                $changes[] = [
                    'mutation_id' => $mutationId,
                    'operation_id' => $operationId,
                    'actor_identity_id' => $actorIdentityId,
                    'device_id' => $deviceId,
                    'product_id' => $productId,
                    'change_type' => $beforeExists ? 'UPDATE' : 'CREATE',
                    'before' => $before,
                    'after' => $after,
                    'correlation_id' => $correlationId,
                    'occurred_at_unix' => $occurredAtUnix,
                ];
            }

            return new PosCatalogChangeHistoryWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $changes,
                $truncated,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->workspaceEnabled
            || ! $this->catalogPreparationEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    /** @return array{display_name:string,unit_price_atomic:string,currency:string,scale:int,sellable:bool} */
    private function state(mixed $displayName, mixed $atomic, mixed $currency, mixed $scale, mixed $sellable): array
    {
        $name = $this->requiredString($displayName);
        if (strlen($name) > 160 || preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
            throw new PosTransactionViolation();
        }

        $moneyAtomic = $this->unsignedString($atomic);
        $moneyCurrency = $this->requiredString($currency);
        $moneyScale = $this->smallInteger($scale, 0, 6);
        if (preg_match('/\A[A-Z]{3}\z/', $moneyCurrency) !== 1) {
            throw new PosTransactionViolation();
        }

        return [
            'display_name' => $name,
            'unit_price_atomic' => $moneyAtomic,
            'currency' => $moneyCurrency,
            'scale' => $moneyScale,
            'sellable' => $this->boolean($sellable),
        ];
    }

    private function hexString(mixed $value, int $length): string
    {
        if (! is_string($value) || preg_match('/\A[a-f0-9]{'.$length.'}\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function stableIdentifier(mixed $value): string
    {
        if (! is_string($value) || preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return trim($value);
    }

    private function unsignedString(mixed $value): string
    {
        if (is_int($value)) {
            if ($value < 0) {
                throw new PosTransactionViolation();
            }
            return (string) $value;
        }
        if (! is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        $normalized = ltrim($value, '0');
        return $normalized === '' ? '0' : $normalized;
    }

    private function positiveInteger(mixed $value): int
    {
        if (is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1) {
            if (strlen($value) > strlen((string) PHP_INT_MAX)
                || (strlen($value) === strlen((string) PHP_INT_MAX) && strcmp($value, (string) PHP_INT_MAX) > 0)) {
                throw new PosTransactionViolation();
            }
            $value = (int) $value;
        }
        if (! is_int($value) || $value <= 0) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function smallInteger(mixed $value, int $minimum, int $maximum): int
    {
        if (is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1) {
            $value = (int) $value;
        }
        if (! is_int($value) || $value < $minimum || $value > $maximum) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function boolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 0 || $value === '0') {
            return false;
        }
        if ($value === 1 || $value === '1') {
            return true;
        }
        throw new PosTransactionViolation();
    }
}
