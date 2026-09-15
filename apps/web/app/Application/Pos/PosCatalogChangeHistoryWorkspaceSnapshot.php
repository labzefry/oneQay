<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCatalogChangeHistoryWorkspaceSnapshot
{
    /** @var list<array<string, mixed>> */
    private array $changes;

    /**
     * @param list<array<string, mixed>> $changes
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        array $changes,
        private bool $truncated,
    ) {
        foreach ([$this->tenantId, $this->organizationId, $this->outletId, $this->deviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Catalog change history scope is invalid.');
            }
        }

        $validated = [];
        foreach ($changes as $change) {
            $validated[] = $this->validateChange($change);
        }
        $this->changes = $validated;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }

    /** @return list<array<string, mixed>> */
    public function changes(): array { return $this->changes; }
    public function truncated(): bool { return $this->truncated; }

    /** @param array<string, mixed> $change @return array<string, mixed> */
    private function validateChange(array $change): array
    {
        $expected = [
            'mutation_id',
            'operation_id',
            'actor_identity_id',
            'device_id',
            'product_id',
            'change_type',
            'before',
            'after',
            'correlation_id',
            'occurred_at_unix',
        ];
        $keys = array_keys($change);
        sort($keys);
        $sortedExpected = $expected;
        sort($sortedExpected);
        if ($keys !== $sortedExpected) {
            throw new InvalidArgumentException('Catalog change history item shape is invalid.');
        }

        if (! is_string($change['mutation_id']) || preg_match('/\A[a-f0-9]{32}\z/', $change['mutation_id']) !== 1
            || ! $this->stableIdentifier($change['operation_id'])
            || ! $this->nonEmptyString($change['actor_identity_id'])
            || ! $this->nonEmptyString($change['device_id'])
            || ! $this->nonEmptyString($change['product_id'])
            || ! in_array($change['change_type'], ['CREATE', 'UPDATE'], true)
            || ! $this->stableIdentifier($change['correlation_id'])
            || ! is_int($change['occurred_at_unix'])
            || $change['occurred_at_unix'] <= 0) {
            throw new InvalidArgumentException('Catalog change history item value is invalid.');
        }

        $after = $this->validateCatalogState($change['after']);
        $before = null;

        if ($change['change_type'] === 'CREATE') {
            if ($change['before'] !== null) {
                throw new InvalidArgumentException('Catalog CREATE history must not contain before-state.');
            }
        } else {
            if (! is_array($change['before'])) {
                throw new InvalidArgumentException('Catalog UPDATE history requires before-state.');
            }
            $before = $this->validateCatalogState($change['before']);
        }

        $change['before'] = $before;
        $change['after'] = $after;

        return $change;
    }

    /** @return array{display_name:string,unit_price_atomic:string,currency:string,scale:int,sellable:bool} */
    private function validateCatalogState(mixed $state): array
    {
        if (! is_array($state)) {
            throw new InvalidArgumentException('Catalog history state is invalid.');
        }

        $expected = ['display_name', 'unit_price_atomic', 'currency', 'scale', 'sellable'];
        $keys = array_keys($state);
        sort($keys);
        $sortedExpected = $expected;
        sort($sortedExpected);
        if ($keys !== $sortedExpected
            || ! is_string($state['display_name'])
            || trim($state['display_name']) === ''
            || strlen($state['display_name']) > 160
            || preg_match('/[\x00-\x1F\x7F]/', $state['display_name']) === 1
            || ! is_string($state['unit_price_atomic'])
            || preg_match('/\A[0-9]+\z/', $state['unit_price_atomic']) !== 1
            || ! is_string($state['currency'])
            || preg_match('/\A[A-Z]{3}\z/', $state['currency']) !== 1
            || ! is_int($state['scale'])
            || $state['scale'] < 0
            || $state['scale'] > 6
            || ! is_bool($state['sellable'])) {
            throw new InvalidArgumentException('Catalog history state value is invalid.');
        }

        return [
            'display_name' => trim($state['display_name']),
            'unit_price_atomic' => $state['unit_price_atomic'],
            'currency' => $state['currency'],
            'scale' => $state['scale'],
            'sellable' => $state['sellable'],
        ];
    }

    private function stableIdentifier(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/', $value) === 1;
    }

    private function nonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
