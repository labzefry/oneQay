<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosActiveShiftPerformanceWorkspaceSnapshot
{
    /** @var array<string,mixed>|null */
    private ?array $activeShift;

    /** @var list<array<string,mixed>> */
    private array $buckets;

    /**
     * @param array<string,mixed>|null $activeShift
     * @param list<array<string,mixed>> $buckets
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        ?array $activeShift,
        array $buckets,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $deviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Active shift performance scope is invalid.');
            }
        }

        if ($activeShift === null) {
            if ($buckets !== []) {
                throw new InvalidArgumentException('Active shift performance cannot contain buckets without an active shift.');
            }
            $this->activeShift = null;
            $this->buckets = [];
            return;
        }

        $shiftKeys = array_keys($activeShift);
        $expectedShiftKeys = ['shift_id', 'opener_actor_identity_id', 'opened_at_unix'];
        sort($shiftKeys);
        sort($expectedShiftKeys);
        if ($shiftKeys !== $expectedShiftKeys
            || ! is_string($activeShift['shift_id']) || trim($activeShift['shift_id']) === ''
            || ! is_string($activeShift['opener_actor_identity_id']) || trim($activeShift['opener_actor_identity_id']) === ''
            || ! is_int($activeShift['opened_at_unix']) || $activeShift['opened_at_unix'] <= 0) {
            throw new InvalidArgumentException('Active shift performance shift identity is invalid.');
        }

        if (count($buckets) > 64) {
            throw new InvalidArgumentException('Active shift performance bucket bound is invalid.');
        }

        $seen = [];
        $validated = [];
        foreach ($buckets as $bucket) {
            $item = $this->validateBucket($bucket);
            $key = $item['tender_category'].'|'.$item['currency'].'|'.$item['scale'];
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Active shift performance contains a duplicate bucket.');
            }
            $seen[$key] = true;
            $validated[] = $item;
        }

        $this->activeShift = $activeShift;
        $this->buckets = $validated;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }

    /** @return array<string,mixed>|null */
    public function activeShift(): ?array { return $this->activeShift; }

    /** @return list<array<string,mixed>> */
    public function buckets(): array { return $this->buckets; }

    /** @param array<string,mixed> $bucket @return array<string,mixed> */
    private function validateBucket(array $bucket): array
    {
        $expected = [
            'tender_category',
            'currency',
            'scale',
            'completed_sales',
            'voided_sales',
            'active_sales',
            'refunded_sales',
            'gross_atomic',
            'voided_atomic',
            'active_net_atomic',
            'refunded_cash_atomic',
        ];
        $keys = array_keys($bucket);
        sort($keys);
        sort($expected);

        if ($keys !== $expected
            || ! is_string($bucket['tender_category'])
            || ! in_array($bucket['tender_category'], ['CASH', 'MANUAL_EXTERNAL'], true)
            || ! is_string($bucket['currency']) || preg_match('/\A[A-Z]{3}\z/', $bucket['currency']) !== 1
            || ! is_int($bucket['scale']) || $bucket['scale'] < 0 || $bucket['scale'] > 6) {
            throw new InvalidArgumentException('Active shift performance bucket identity is invalid.');
        }

        foreach (['completed_sales','voided_sales','active_sales','refunded_sales','gross_atomic','voided_atomic','active_net_atomic','refunded_cash_atomic'] as $field) {
            if (! is_string($bucket[$field]) || preg_match('/\A(?:0|[1-9][0-9]*)\z/', $bucket[$field]) !== 1) {
                throw new InvalidArgumentException('Active shift performance numeric value is invalid.');
            }
        }

        $completed = $this->safeDecimalToInt($bucket['completed_sales']);
        $voided = $this->safeDecimalToInt($bucket['voided_sales']);
        $active = $this->safeDecimalToInt($bucket['active_sales']);
        $refunded = $this->safeDecimalToInt($bucket['refunded_sales']);
        $gross = $this->safeDecimalToInt($bucket['gross_atomic']);
        $voidedAtomic = $this->safeDecimalToInt($bucket['voided_atomic']);
        $activeNet = $this->safeDecimalToInt($bucket['active_net_atomic']);
        $refundedCash = $this->safeDecimalToInt($bucket['refunded_cash_atomic']);

        if ($completed <= 0
            || $voided > $completed
            || $active !== $completed - $voided
            || $refunded > $voided
            || $voidedAtomic > $gross
            || $activeNet !== $gross - $voidedAtomic
            || $refundedCash > $voidedAtomic
            || ($bucket['tender_category'] === 'MANUAL_EXTERNAL' && ($refunded !== 0 || $refundedCash !== 0))) {
            throw new InvalidArgumentException('Active shift performance arithmetic is inconsistent.');
        }

        return $bucket;
    }

    private function safeDecimalToInt(string $value): int
    {
        $maximum = (string) PHP_INT_MAX;
        if (strlen($value) > strlen($maximum)
            || (strlen($value) === strlen($maximum) && strcmp($value, $maximum) > 0)) {
            throw new InvalidArgumentException('Active shift performance value exceeds supported range.');
        }

        return (int) $value;
    }
}
