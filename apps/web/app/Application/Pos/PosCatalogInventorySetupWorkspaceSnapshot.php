<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCatalogInventorySetupWorkspaceSnapshot
{
    /** @var list<array<string, mixed>> */
    private array $items;

    /**
     * @param list<array<string, mixed>> $items
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        array $items,
    ) {
        foreach ([$this->tenantId, $this->organizationId, $this->outletId, $this->deviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Catalog inventory setup scope is invalid.');
            }
        }

        $validated = [];
        foreach ($items as $item) {
            $validated[] = $this->validateItem($item);
        }
        $this->items = $validated;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }

    /** @return list<array<string, mixed>> */
    public function items(): array { return $this->items; }

    /** @param array<string, mixed> $item @return array<string, mixed> */
    private function validateItem(array $item): array
    {
        $expected = [
            'product_id',
            'display_name',
            'unit_price_atomic',
            'currency',
            'scale',
            'available_quantity',
            'sellable',
            'baseline_established',
            'sale_history_exists',
            'baseline_eligible',
        ];
        $keys = array_keys($item);
        sort($keys);
        $sortedExpected = $expected;
        sort($sortedExpected);
        if ($keys !== $sortedExpected) {
            throw new InvalidArgumentException('Catalog inventory setup item shape is invalid.');
        }

        if (! is_string($item['product_id']) || trim($item['product_id']) === ''
            || ! is_string($item['display_name']) || trim($item['display_name']) === ''
            || ! is_string($item['unit_price_atomic']) || preg_match('/\A[0-9]+\z/', $item['unit_price_atomic']) !== 1
            || ! is_string($item['currency']) || preg_match('/\A[A-Z]{3}\z/', $item['currency']) !== 1
            || ! is_int($item['scale']) || $item['scale'] < 0 || $item['scale'] > 6
            || ! is_string($item['available_quantity']) || preg_match('/\A[0-9]+\z/', $item['available_quantity']) !== 1
            || ! is_bool($item['sellable'])
            || ! is_bool($item['baseline_established'])
            || ! is_bool($item['sale_history_exists'])
            || ! is_bool($item['baseline_eligible'])) {
            throw new InvalidArgumentException('Catalog inventory setup item value is invalid.');
        }

        $eligible = $item['available_quantity'] === '0'
            && $item['baseline_established'] === false
            && $item['sale_history_exists'] === false;
        if ($item['baseline_eligible'] !== $eligible) {
            throw new InvalidArgumentException('Catalog inventory setup baseline eligibility is inconsistent.');
        }

        return $item;
    }
}
