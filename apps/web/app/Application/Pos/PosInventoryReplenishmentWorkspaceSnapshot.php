<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosInventoryReplenishmentWorkspaceSnapshot
{
    /** @var list<array<string, mixed>> */
    private array $items;
    /** @var list<array<string, mixed>> */
    private array $recentReplenishments;

    /**
     * @param list<array<string, mixed>> $items
     * @param list<array<string, mixed>> $recentReplenishments
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        array $items,
        array $recentReplenishments,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $deviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Inventory replenishment workspace scope is invalid.');
            }
        }

        if (count($items) > 250 || count($recentReplenishments) > 50) {
            throw new InvalidArgumentException('Inventory replenishment workspace bounds are invalid.');
        }

        $seenProducts = [];
        $validatedItems = [];
        foreach ($items as $item) {
            $validated = $this->validateItem($item);
            if (isset($seenProducts[$validated['product_id']])) {
                throw new InvalidArgumentException('Inventory replenishment workspace contains duplicate products.');
            }
            $seenProducts[$validated['product_id']] = true;
            $validatedItems[] = $validated;
        }

        $seenEvidence = [];
        $validatedRecent = [];
        foreach ($recentReplenishments as $row) {
            $validated = $this->validateReplenishment($row);
            if (isset($seenEvidence[$validated['replenishment_id']])) {
                throw new InvalidArgumentException('Inventory replenishment workspace contains duplicate evidence.');
            }
            $seenEvidence[$validated['replenishment_id']] = true;
            $validatedRecent[] = $validated;
        }

        $this->items = $validatedItems;
        $this->recentReplenishments = $validatedRecent;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    /** @return list<array<string, mixed>> */
    public function items(): array { return $this->items; }
    /** @return list<array<string, mixed>> */
    public function recentReplenishments(): array { return $this->recentReplenishments; }

    /** @param array<string, mixed> $item @return array<string, mixed> */
    private function validateItem(array $item): array
    {
        $expected = ['product_id', 'display_name', 'available_quantity'];
        $keys = array_keys($item);
        sort($keys);
        sort($expected);
        if ($keys !== $expected
            || ! is_string($item['product_id']) || trim($item['product_id']) === ''
            || ! is_string($item['display_name']) || trim($item['display_name']) === ''
            || ! is_string($item['available_quantity']) || preg_match('/\A[0-9]+\z/', $item['available_quantity']) !== 1) {
            throw new InvalidArgumentException('Inventory replenishment workspace item is invalid.');
        }

        return $item;
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function validateReplenishment(array $row): array
    {
        $expected = [
            'replenishment_id',
            'product_id',
            'before_available_quantity',
            'replenished_quantity',
            'after_available_quantity',
            'occurred_at_unix',
        ];
        $keys = array_keys($row);
        sort($keys);
        sort($expected);
        if ($keys !== $expected
            || ! is_string($row['replenishment_id']) || preg_match('/\A[a-f0-9]{32}\z/', $row['replenishment_id']) !== 1
            || ! is_string($row['product_id']) || trim($row['product_id']) === ''
            || ! is_string($row['before_available_quantity']) || preg_match('/\A[0-9]+\z/', $row['before_available_quantity']) !== 1
            || ! is_string($row['replenished_quantity']) || preg_match('/\A[1-9][0-9]*\z/', $row['replenished_quantity']) !== 1
            || ! is_string($row['after_available_quantity']) || preg_match('/\A[0-9]+\z/', $row['after_available_quantity']) !== 1
            || ! is_int($row['occurred_at_unix']) || $row['occurred_at_unix'] <= 0) {
            throw new InvalidArgumentException('Inventory replenishment workspace evidence is invalid.');
        }

        $before = $this->safeDecimalToInt($row['before_available_quantity']);
        $quantity = $this->safeDecimalToInt($row['replenished_quantity']);
        $after = $this->safeDecimalToInt($row['after_available_quantity']);
        if ($quantity > PHP_INT_MAX - $before || $before + $quantity !== $after) {
            throw new InvalidArgumentException('Inventory replenishment evidence arithmetic is inconsistent.');
        }

        return $row;
    }

    private function safeDecimalToInt(string $value): int
    {
        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new InvalidArgumentException('Inventory replenishment quantity exceeds supported range.');
        }
        return (int) $normalized;
    }
}
