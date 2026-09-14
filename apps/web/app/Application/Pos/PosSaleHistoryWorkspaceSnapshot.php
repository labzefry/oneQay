<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosSaleHistoryWorkspaceSnapshot
{
    private const SALE_ID_PATTERN = '/\Asale-[a-f0-9]{24}\z/';

    /**
     * @param list<array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   total_atomic:string,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   state:string,
     *   shift_id:string,
     *   device_id:string,
     *   voided_at_unix:?int,
     *   refunded_at_unix:?int
     * }> $recentSales
     * @param null|array{
     *   sale_id:string,
     *   completed_at_unix:int,
     *   total_atomic:string,
     *   currency:string,
     *   scale:int,
     *   tender_category:string,
     *   evidence_mode:string,
     *   applied_atomic:string,
     *   change_atomic:string,
     *   state:string,
     *   shift_id:string,
     *   device_id:string,
     *   void_id:?string,
     *   voided_at_unix:?int,
     *   refund_id:?string,
     *   refunded_at_unix:?int,
     *   lines:list<array{line_no:int,product_id:string,quantity:int,unit_price_atomic:string,line_total_atomic:string,currency:string,scale:int}>
     * } $selectedReceipt
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private ?string $selectedSaleId,
        private array $recentSales,
        private ?array $selectedReceipt,
    ) {
        if ($tenantId === '' || $organizationId === '' || $outletId === '') {
            throw new InvalidArgumentException('POS sale history scope is invalid.');
        }
        if (count($recentSales) > 50) {
            throw new InvalidArgumentException('POS sale history exceeds the bounded result limit.');
        }
        if ($selectedSaleId !== null && preg_match(self::SALE_ID_PATTERN, $selectedSaleId) !== 1) {
            throw new InvalidArgumentException('POS selected sale identifier is invalid.');
        }
        if ($selectedReceipt !== null
            && ($selectedSaleId === null || ! hash_equals($selectedSaleId, (string) ($selectedReceipt['sale_id'] ?? '')))) {
            throw new InvalidArgumentException('POS selected receipt does not match the requested sale.');
        }

        $seen = [];
        foreach ($recentSales as $sale) {
            $saleId = (string) ($sale['sale_id'] ?? '');
            if (preg_match(self::SALE_ID_PATTERN, $saleId) !== 1 || isset($seen[$saleId])) {
                throw new InvalidArgumentException('POS sale history contains an invalid or duplicate sale.');
            }
            $seen[$saleId] = true;
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function selectedSaleId(): ?string { return $this->selectedSaleId; }

    /** @return list<array<string, mixed>> */
    public function recentSales(): array { return $this->recentSales; }

    /** @return null|array<string, mixed> */
    public function selectedReceipt(): ?array { return $this->selectedReceipt; }
}
