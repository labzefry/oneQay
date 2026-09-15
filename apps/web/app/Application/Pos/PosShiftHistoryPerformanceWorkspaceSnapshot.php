<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosShiftHistoryPerformanceWorkspaceSnapshot
{
    /** @var list<array<string,mixed>> */
    private array $closedShifts;

    /** @var array<string,mixed>|null */
    private ?array $selectedShift;

    /** @var list<array<string,mixed>> */
    private array $buckets;

    /**
     * @param list<array<string,mixed>> $closedShifts
     * @param array<string,mixed>|null $selectedShift
     * @param list<array<string,mixed>> $buckets
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $requesterDeviceId,
        array $closedShifts,
        ?array $selectedShift,
        array $buckets,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $requesterDeviceId] as $identifier) {
            if (trim($identifier) === '') {
                throw new InvalidArgumentException('Shift history performance scope is invalid.');
            }
        }
        if (count($closedShifts) > 50 || count($buckets) > 64) {
            throw new InvalidArgumentException('Shift history performance bound is invalid.');
        }

        $seen = [];
        $validatedShifts = [];
        foreach ($closedShifts as $shift) {
            $item = $this->validateShift($shift);
            if (isset($seen[$item['shift_id']])) {
                throw new InvalidArgumentException('Shift history performance contains a duplicate shift.');
            }
            $seen[$item['shift_id']] = true;
            $validatedShifts[] = $item;
        }

        if ($selectedShift === null) {
            if ($buckets !== []) {
                throw new InvalidArgumentException('Shift history performance cannot contain buckets without a selected shift.');
            }
        } else {
            $selectedShift = $this->validateShift($selectedShift);
            if (! isset($seen[$selectedShift['shift_id']])) {
                throw new InvalidArgumentException('Selected shift is outside the bounded history.');
            }
        }

        $bucketSeen = [];
        $validatedBuckets = [];
        foreach ($buckets as $bucket) {
            $item = $this->validateBucket($bucket);
            $key = $item['tender_category'].'|'.$item['currency'].'|'.$item['scale'];
            if (isset($bucketSeen[$key])) {
                throw new InvalidArgumentException('Shift history performance contains a duplicate bucket.');
            }
            $bucketSeen[$key] = true;
            $validatedBuckets[] = $item;
        }

        $this->closedShifts = $validatedShifts;
        $this->selectedShift = $selectedShift;
        $this->buckets = $validatedBuckets;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function requesterDeviceId(): string { return $this->requesterDeviceId; }

    /** @return list<array<string,mixed>> */
    public function closedShifts(): array { return $this->closedShifts; }

    /** @return array<string,mixed>|null */
    public function selectedShift(): ?array { return $this->selectedShift; }

    /** @return list<array<string,mixed>> */
    public function buckets(): array { return $this->buckets; }

    /** @param array<string,mixed> $shift @return array<string,mixed> */
    private function validateShift(array $shift): array
    {
        $expectedKeys = [
            'shift_id', 'device_id', 'opener_actor_identity_id', 'closer_actor_identity_id',
            'opened_at_unix', 'closed_at_unix', 'duration_seconds', 'cutoff_at_unix',
            'expected_cash_atomic', 'observed_closing_cash_atomic', 'variance_atomic',
            'variance_direction', 'currency', 'scale', 'review_outcome',
        ];
        $keys = array_keys($shift);
        sort($keys);
        sort($expectedKeys);
        if ($keys !== $expectedKeys) {
            throw new InvalidArgumentException('Shift history performance shift shape is invalid.');
        }

        foreach (['shift_id', 'device_id', 'opener_actor_identity_id', 'closer_actor_identity_id'] as $field) {
            if (! is_string($shift[$field]) || trim($shift[$field]) === '') {
                throw new InvalidArgumentException('Shift history performance identity is invalid.');
            }
        }
        if (preg_match('/\A[a-f0-9]{32}\z/', $shift['shift_id']) !== 1) {
            throw new InvalidArgumentException('Shift history performance shift id is invalid.');
        }
        foreach (['opened_at_unix', 'closed_at_unix', 'duration_seconds', 'cutoff_at_unix', 'scale'] as $field) {
            if (! is_int($shift[$field])) {
                throw new InvalidArgumentException('Shift history performance integer field is invalid.');
            }
        }
        if ($shift['opened_at_unix'] <= 0
            || $shift['closed_at_unix'] < $shift['opened_at_unix']
            || $shift['duration_seconds'] !== $shift['closed_at_unix'] - $shift['opened_at_unix']
            || $shift['cutoff_at_unix'] < $shift['opened_at_unix']
            || $shift['cutoff_at_unix'] > $shift['closed_at_unix']
            || $shift['scale'] < 0 || $shift['scale'] > 6
            || ! is_string($shift['currency'])
            || preg_match('/\A[A-Z]{3}\z/', $shift['currency']) !== 1) {
            throw new InvalidArgumentException('Shift history performance close metadata is invalid.');
        }

        foreach (['expected_cash_atomic', 'observed_closing_cash_atomic'] as $field) {
            if (! is_string($shift[$field]) || preg_match('/\A(?:0|[1-9][0-9]*)\z/', $shift[$field]) !== 1) {
                throw new InvalidArgumentException('Shift history performance cash value is invalid.');
            }
        }
        if (! is_string($shift['variance_atomic']) || preg_match('/\A-?(?:0|[1-9][0-9]*)\z/', $shift['variance_atomic']) !== 1) {
            throw new InvalidArgumentException('Shift history performance variance is invalid.');
        }

        $expected = $this->safeUnsignedDecimalToInt($shift['expected_cash_atomic']);
        $observed = $this->safeUnsignedDecimalToInt($shift['observed_closing_cash_atomic']);
        $variance = $this->safeSignedDecimalToInt($shift['variance_atomic']);
        if ($variance !== $observed - $expected) {
            throw new InvalidArgumentException('Shift history performance variance arithmetic is inconsistent.');
        }

        $direction = $shift['variance_direction'];
        $reviewOutcome = $shift['review_outcome'];
        if (! is_string($direction)
            || ! in_array($direction, ['MATCH', 'OVER', 'SHORT'], true)
            || ($reviewOutcome !== null && ! is_string($reviewOutcome))) {
            throw new InvalidArgumentException('Shift history performance variance state is invalid.');
        }
        $validState = ($direction === 'MATCH' && $variance === 0 && $reviewOutcome === null)
            || ($direction === 'OVER' && $variance > 0 && $reviewOutcome === 'REVIEW_ACCEPTED')
            || ($direction === 'SHORT' && $variance < 0 && $reviewOutcome === 'REVIEW_ACCEPTED');
        if (! $validState) {
            throw new InvalidArgumentException('Shift history performance review state is inconsistent.');
        }

        return $shift;
    }

    /** @param array<string,mixed> $bucket @return array<string,mixed> */
    private function validateBucket(array $bucket): array
    {
        $expected = [
            'tender_category', 'currency', 'scale', 'completed_sales', 'voided_sales',
            'active_sales', 'refunded_sales', 'gross_atomic', 'voided_atomic',
            'active_net_atomic', 'refunded_cash_atomic',
        ];
        $keys = array_keys($bucket);
        sort($keys);
        sort($expected);
        if ($keys !== $expected
            || ! is_string($bucket['tender_category'])
            || ! in_array($bucket['tender_category'], ['CASH', 'MANUAL_EXTERNAL'], true)
            || ! is_string($bucket['currency'])
            || preg_match('/\A[A-Z]{3}\z/', $bucket['currency']) !== 1
            || ! is_int($bucket['scale']) || $bucket['scale'] < 0 || $bucket['scale'] > 6) {
            throw new InvalidArgumentException('Shift history performance bucket identity is invalid.');
        }

        foreach (['completed_sales','voided_sales','active_sales','refunded_sales','gross_atomic','voided_atomic','active_net_atomic','refunded_cash_atomic'] as $field) {
            if (! is_string($bucket[$field]) || preg_match('/\A(?:0|[1-9][0-9]*)\z/', $bucket[$field]) !== 1) {
                throw new InvalidArgumentException('Shift history performance bucket value is invalid.');
            }
        }

        $completed = $this->safeUnsignedDecimalToInt($bucket['completed_sales']);
        $voided = $this->safeUnsignedDecimalToInt($bucket['voided_sales']);
        $active = $this->safeUnsignedDecimalToInt($bucket['active_sales']);
        $refunded = $this->safeUnsignedDecimalToInt($bucket['refunded_sales']);
        $gross = $this->safeUnsignedDecimalToInt($bucket['gross_atomic']);
        $voidedAtomic = $this->safeUnsignedDecimalToInt($bucket['voided_atomic']);
        $activeNet = $this->safeUnsignedDecimalToInt($bucket['active_net_atomic']);
        $refundedCash = $this->safeUnsignedDecimalToInt($bucket['refunded_cash_atomic']);

        if ($completed <= 0
            || $voided > $completed
            || $active !== $completed - $voided
            || $refunded > $voided
            || $voidedAtomic > $gross
            || $activeNet !== $gross - $voidedAtomic
            || $refundedCash > $voidedAtomic
            || ($bucket['tender_category'] === 'MANUAL_EXTERNAL' && ($refunded !== 0 || $refundedCash !== 0))) {
            throw new InvalidArgumentException('Shift history performance bucket arithmetic is inconsistent.');
        }

        return $bucket;
    }

    private function safeUnsignedDecimalToInt(string $value): int
    {
        $maximum = (string) PHP_INT_MAX;
        if (strlen($value) > strlen($maximum)
            || (strlen($value) === strlen($maximum) && strcmp($value, $maximum) > 0)) {
            throw new InvalidArgumentException('Shift history performance value exceeds supported range.');
        }
        return (int) $value;
    }

    private function safeSignedDecimalToInt(string $value): int
    {
        if ($value[0] !== '-') {
            return $this->safeUnsignedDecimalToInt($value);
        }
        $magnitude = substr($value, 1);
        $minimumMagnitude = ltrim((string) PHP_INT_MIN, '-');
        if (strlen($magnitude) > strlen($minimumMagnitude)
            || (strlen($magnitude) === strlen($minimumMagnitude) && strcmp($magnitude, $minimumMagnitude) > 0)) {
            throw new InvalidArgumentException('Shift history performance signed value exceeds supported range.');
        }
        return (int) $value;
    }
}
