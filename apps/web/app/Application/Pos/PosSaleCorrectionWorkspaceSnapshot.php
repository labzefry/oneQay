<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosSaleCorrectionWorkspaceSnapshot
{
    private const SALE_ID_PATTERN = '/\Asale-[a-f0-9]{24}\z/';
    private const VOID_ID_PATTERN = '/\Avoid-[a-f0-9]{24}\z/';
    private const REFUND_ID_PATTERN = '/\Arefund-[a-f0-9]{24}\z/';

    /**
     * @var list<array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   amount_atomic:int,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   original_device_id:string,
     *   state:string,
     *   shift_active:bool,
     *   void_id:?string,
     *   voided_at_unix:?int,
     *   refund_id:?string,
     *   refunded_at_unix:?int,
     *   void_eligible:bool,
     *   cash_refund_eligible:bool,
     *   external_settlement_required:bool
     * }>
     */
    private array $sales;

    /**
     * @param list<array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   amount_atomic:int,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   original_device_id:string,
     *   state:string,
     *   shift_active:bool,
     *   void_id:?string,
     *   voided_at_unix:?int,
     *   refund_id:?string,
     *   refunded_at_unix:?int
     * }> $sales
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private bool $canVoid,
        private bool $canCashRefund,
        array $sales,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $deviceId] as $scopeValue) {
            if (trim($scopeValue) === '') {
                throw new InvalidArgumentException('Sale correction workspace scope is invalid.');
            }
        }

        $normalized = [];
        foreach ($sales as $sale) {
            $this->assertSale($sale);

            $state = $sale['state'];
            $tender = $sale['tender_category'];
            $shiftActive = $sale['shift_active'];

            $normalized[] = [
                ...$sale,
                'void_eligible' => $canVoid && $state === 'COMPLETED' && $shiftActive,
                'cash_refund_eligible' => $canCashRefund
                    && $state === 'VOIDED'
                    && $tender === 'CASH'
                    && $shiftActive,
                'external_settlement_required' => $state === 'VOIDED'
                    && $tender === 'MANUAL_EXTERNAL',
            ];
        }

        $this->sales = $normalized;
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function canVoid(): bool { return $this->canVoid; }
    public function canCashRefund(): bool { return $this->canCashRefund; }

    /**
     * @return list<array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   amount_atomic:int,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   original_device_id:string,
     *   state:string,
     *   shift_active:bool,
     *   void_id:?string,
     *   voided_at_unix:?int,
     *   refund_id:?string,
     *   refunded_at_unix:?int,
     *   void_eligible:bool,
     *   cash_refund_eligible:bool,
     *   external_settlement_required:bool
     * }>
     */
    public function sales(): array { return $this->sales; }

    /** @param array<string, mixed> $sale */
    private function assertSale(array $sale): void
    {
        $required = [
            'sale_id',
            'completed_at_unix',
            'amount_atomic',
            'currency',
            'scale',
            'tender_category',
            'original_device_id',
            'state',
            'shift_active',
            'void_id',
            'voided_at_unix',
            'refund_id',
            'refunded_at_unix',
        ];
        $keys = array_keys($sale);
        sort($keys);
        $expected = $required;
        sort($expected);
        if ($keys !== $expected) {
            throw new InvalidArgumentException('Sale correction row shape is invalid.');
        }

        if (! is_string($sale['sale_id']) || preg_match(self::SALE_ID_PATTERN, $sale['sale_id']) !== 1
            || ! is_int($sale['completed_at_unix']) || $sale['completed_at_unix'] <= 0
            || ! is_int($sale['amount_atomic']) || $sale['amount_atomic'] < 0
            || ! is_string($sale['currency']) || preg_match('/\A[A-Z]{3}\z/', $sale['currency']) !== 1
            || ! is_int($sale['scale']) || $sale['scale'] < 0 || $sale['scale'] > 6
            || ! is_string($sale['tender_category']) || ! in_array($sale['tender_category'], ['CASH', 'MANUAL_EXTERNAL'], true)
            || ! is_string($sale['original_device_id']) || trim($sale['original_device_id']) === ''
            || ! is_string($sale['state']) || ! in_array($sale['state'], ['COMPLETED', 'VOIDED', 'REFUNDED'], true)
            || ! is_bool($sale['shift_active'])) {
            throw new InvalidArgumentException('Sale correction row value is invalid.');
        }

        if ($sale['void_id'] !== null
            && (! is_string($sale['void_id']) || preg_match(self::VOID_ID_PATTERN, $sale['void_id']) !== 1)) {
            throw new InvalidArgumentException('Sale correction void identity is invalid.');
        }
        if ($sale['refund_id'] !== null
            && (! is_string($sale['refund_id']) || preg_match(self::REFUND_ID_PATTERN, $sale['refund_id']) !== 1)) {
            throw new InvalidArgumentException('Sale correction refund identity is invalid.');
        }
        foreach (['voided_at_unix', 'refunded_at_unix'] as $timeField) {
            if ($sale[$timeField] !== null && (! is_int($sale[$timeField]) || $sale[$timeField] <= 0)) {
                throw new InvalidArgumentException('Sale correction evidence timestamp is invalid.');
            }
        }

        if ($sale['state'] === 'COMPLETED'
            && ($sale['void_id'] !== null || $sale['refund_id'] !== null
                || $sale['voided_at_unix'] !== null || $sale['refunded_at_unix'] !== null)) {
            throw new InvalidArgumentException('Completed sale correction state is inconsistent.');
        }
        if ($sale['state'] === 'VOIDED'
            && ($sale['void_id'] === null || $sale['voided_at_unix'] === null
                || $sale['refund_id'] !== null || $sale['refunded_at_unix'] !== null)) {
            throw new InvalidArgumentException('Voided sale correction state is inconsistent.');
        }
        if ($sale['state'] === 'REFUNDED'
            && ($sale['void_id'] === null || $sale['voided_at_unix'] === null
                || $sale['refund_id'] === null || $sale['refunded_at_unix'] === null
                || $sale['tender_category'] !== 'CASH')) {
            throw new InvalidArgumentException('Refunded sale correction state is inconsistent.');
        }
    }
}
