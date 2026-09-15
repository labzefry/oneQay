<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosProductSalesPerformanceWorkspaceSnapshot
{
    /** @var list<array<string, mixed>> */
    private array $rows;

    /** @param list<array<string, mixed>> $rows */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        array $rows,
        private bool $truncated,
    ) {
        foreach ([$tenantId, $organizationId, $outletId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Product sales performance scope is invalid.');
            }
        }

        if (count($rows) > 250) {
            throw new InvalidArgumentException('Product sales performance row bound is invalid.');
        }

        $seen = [];
        $validated = [];
        foreach ($rows as $row) {
            $item = $this->validateRow($row);
            $key = $item['product_id'].'|'.$item['currency'].'|'.$item['scale'];
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Product sales performance contains a duplicate product/currency bucket.');
            }
            $seen[$key] = true;
            $validated[] = $item;
        }

        $this->rows = $validated;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }

    /** @return list<array<string, mixed>> */
    public function rows(): array { return $this->rows; }

    public function truncated(): bool { return $this->truncated; }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function validateRow(array $row): array
    {
        $expected = [
            'product_id',
            'display_name',
            'currency',
            'scale',
            'gross_quantity',
            'voided_quantity',
            'net_quantity',
            'gross_atomic',
            'voided_atomic',
            'net_atomic',
        ];
        $keys = array_keys($row);
        sort($keys);
        sort($expected);

        if ($keys !== $expected
            || ! is_string($row['product_id']) || trim($row['product_id']) === ''
            || ! is_string($row['display_name']) || trim($row['display_name']) === ''
            || ! is_string($row['currency']) || preg_match('/\A[A-Z]{3}\z/', $row['currency']) !== 1
            || ! is_int($row['scale']) || $row['scale'] < 0 || $row['scale'] > 6) {
            throw new InvalidArgumentException('Product sales performance row identity is invalid.');
        }

        foreach (['gross_quantity','voided_quantity','net_quantity','gross_atomic','voided_atomic','net_atomic'] as $field) {
            if (! is_string($row[$field]) || preg_match('/\A(?:0|[1-9][0-9]*)\z/', $row[$field]) !== 1) {
                throw new InvalidArgumentException('Product sales performance numeric value is invalid.');
            }
        }

        $grossQuantity = $this->safeDecimalToInt($row['gross_quantity']);
        $voidedQuantity = $this->safeDecimalToInt($row['voided_quantity']);
        $netQuantity = $this->safeDecimalToInt($row['net_quantity']);
        $grossAtomic = $this->safeDecimalToInt($row['gross_atomic']);
        $voidedAtomic = $this->safeDecimalToInt($row['voided_atomic']);
        $netAtomic = $this->safeDecimalToInt($row['net_atomic']);

        if ($grossQuantity <= 0
            || $voidedQuantity > $grossQuantity
            || $netQuantity !== $grossQuantity - $voidedQuantity
            || $voidedAtomic > $grossAtomic
            || $netAtomic !== $grossAtomic - $voidedAtomic) {
            throw new InvalidArgumentException('Product sales performance arithmetic is inconsistent.');
        }

        return $row;
    }

    private function safeDecimalToInt(string $value): int
    {
        $maximum = (string) PHP_INT_MAX;
        if (strlen($value) > strlen($maximum)
            || (strlen($value) === strlen($maximum) && strcmp($value, $maximum) > 0)) {
            throw new InvalidArgumentException('Product sales performance value exceeds supported range.');
        }

        return (int) $value;
    }
}
