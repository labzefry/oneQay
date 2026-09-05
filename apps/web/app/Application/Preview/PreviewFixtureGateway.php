<?php

declare(strict_types=1);

namespace App\Application\Preview;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\SyntheticPosStore;
use App\Domain\Pos\CatalogItem;

// Author by Lab | zefry
interface PreviewFixtureGateway extends SyntheticPosStore
{
    /** @return list<PreviewProfile> */
    public function profiles(): array;

    public function profile(string $principalId): ?PreviewProfile;

    public function verifiedIdentity(string $principalId): ?VerifiedPlatformIdentity;

    /** @return list<CatalogItem> */
    public function catalogFor(string $tenantId, string $outletId): array;

    /**
     * @return array{
     *   sale_id:string,
     *   status:string,
     *   void_operation_id:string,
     *   refund_operation_id:?string,
     *   refund_amount_atomic:int,
     *   tender_category:string,
     *   idempotent_replay:bool
     * }
     */
    public function voidSale(
        PreviewProfile $profile,
        SaleVoidCommand $command,
    ): array;

    /**
     * @return array{
     *   sale_id:string,
     *   status:string,
     *   void_operation_id:string,
     *   refund_operation_id:string,
     *   refund_amount_atomic:int,
     *   tender_category:string,
     *   idempotent_replay:bool
     * }
     */
    public function refundCashSale(
        PreviewProfile $profile,
        SaleCashRefundCommand $command,
    ): array;
}
