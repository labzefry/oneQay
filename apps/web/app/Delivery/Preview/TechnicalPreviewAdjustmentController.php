<?php

declare(strict_types=1);

namespace App\Delivery\Preview;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use App\Application\Tenancy\MissingTenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class TechnicalPreviewAdjustmentController
{
    private const PRINCIPAL_SESSION = 'oneqay.preview.principal';
    private const CONTEXT_SESSION = 'oneqay.preview.context_selected';
    private const RECEIPT_SESSION = 'oneqay.preview.receipt';
    private const SHIFT_SESSION = 'oneqay.preview.cash_shift';
    private const MAX_PREVIEW_CASH_ATOMIC = 1_000_000_000;

    public function voidSale(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $shift = $this->shiftFromSession($request, $profile);
        $receipt = $shift === null ? null : $this->receiptFromSession($request, $profile, $shift);
        if ($shift === null || $receipt === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['sale' => 'A current synthetic completed sale is required before void.']);
        }

        $saleId = $receipt['sale_id'];
        $operationId = $this->adjustmentOperationId('void', $profile, $saleId);
        $receiptSnapshot = array_replace($receipt, [
            'organization_id' => $profile->organizationId(),
        ]);

        try {
            $adjustment = $journey->voidSale(
                $profile,
                $operationId,
                $receiptSnapshot,
            );
            $receipt['adjustment'] = $adjustment;
            if (! in_array($saleId, $shift['voided_sale_ids'], true)) {
                $shift['voided_sale_ids'][] = $saleId;
            }
            $request->session()->put(self::RECEIPT_SESSION, $receipt);
            $request->session()->put(self::SHIFT_SESSION, $shift);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.receipt')
                ->withErrors(['sale' => 'Synthetic sale void was rejected safely.']);
        }

        return redirect()->route('preview.receipt');
    }

    public function refundCashSale(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $shift = $this->shiftFromSession($request, $profile);
        $receipt = $shift === null ? null : $this->receiptFromSession($request, $profile, $shift);
        if ($shift === null || $receipt === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['sale' => 'A current synthetic voided cash sale is required before refund.']);
        }

        $saleId = $receipt['sale_id'];
        $operationId = $this->adjustmentOperationId('refund', $profile, $saleId);
        $receiptSnapshot = array_replace($receipt, [
            'organization_id' => $profile->organizationId(),
        ]);

        try {
            $adjustment = $journey->refundCashSale(
                $profile,
                $operationId,
                $receiptSnapshot,
            );
            $receipt['adjustment'] = $adjustment;
            if (! in_array($saleId, $shift['refunded_sale_ids'], true)) {
                $refundAmount = $adjustment['refund_amount_atomic'];
                $nextRefundTotal = $shift['cash_refunds_atomic'] + $refundAmount;
                if ($refundAmount <= 0 || $nextRefundTotal > $shift['cash_sales_atomic']) {
                    throw new PosTransactionViolation();
                }
                $shift['cash_refunds_atomic'] = $nextRefundTotal;
                $shift['refunded_sale_ids'][] = $saleId;
            }
            $request->session()->put(self::RECEIPT_SESSION, $receipt);
            $request->session()->put(self::SHIFT_SESSION, $shift);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.receipt')
                ->withErrors(['sale' => 'Synthetic cash refund was rejected safely.']);
        }

        return redirect()->route('preview.receipt');
    }

    private function verifiedSessionProfile(Request $request, TechnicalPreviewJourney $journey): PreviewProfile|RedirectResponse
    {
        $principalId = $request->session()->get(self::PRINCIPAL_SESSION);
        $profile = is_string($principalId) ? $journey->profile($principalId) : null;
        if ($profile === null) {
            return redirect()->route('preview.index');
        }
        if ($request->session()->get(self::CONTEXT_SESSION) !== true) {
            return redirect()->route('preview.context');
        }

        return $profile;
    }

    /** @return array<string, mixed>|null */
    private function shiftFromSession(Request $request, PreviewProfile $profile): ?array
    {
        $shift = $request->session()->get(self::SHIFT_SESSION);
        if (
            ! is_array($shift)
            || ($shift['status'] ?? null) !== 'OPEN'
            || ($shift['tenant_id'] ?? null) !== $profile->tenantId()
            || ($shift['organization_id'] ?? null) !== $profile->organizationId()
            || ($shift['outlet_id'] ?? null) !== $profile->outletId()
            || ($shift['device_id'] ?? null) !== $profile->deviceId()
            || ! is_int($shift['cash_sales_atomic'] ?? null)
            || ! is_int($shift['cash_refunds_atomic'] ?? null)
            || $shift['cash_sales_atomic'] < 0
            || $shift['cash_sales_atomic'] > self::MAX_PREVIEW_CASH_ATOMIC
            || $shift['cash_refunds_atomic'] < 0
            || $shift['cash_refunds_atomic'] > $shift['cash_sales_atomic']
            || ! is_array($shift['recorded_sale_ids'] ?? null)
            || ! is_array($shift['voided_sale_ids'] ?? null)
            || ! is_array($shift['refunded_sale_ids'] ?? null)
            || array_diff($shift['voided_sale_ids'], $shift['recorded_sale_ids']) !== []
            || array_diff($shift['refunded_sale_ids'], $shift['voided_sale_ids']) !== []
        ) {
            $request->session()->forget(self::SHIFT_SESSION);
            return null;
        }

        return $shift;
    }

    /** @param array<string, mixed> $shift @return array<string, mixed>|null */
    private function receiptFromSession(Request $request, PreviewProfile $profile, array $shift): ?array
    {
        $receipt = $request->session()->get(self::RECEIPT_SESSION);
        if (
            ! is_array($receipt)
            || ($receipt['tenant_id'] ?? null) !== $profile->tenantId()
            || ($receipt['actor_id'] ?? null) !== $profile->principalId()
            || ($receipt['outlet_id'] ?? null) !== $profile->outletId()
            || ($receipt['device_id'] ?? null) !== $profile->deviceId()
            || ! is_string($receipt['sale_id'] ?? null)
            || ! in_array($receipt['sale_id'], $shift['recorded_sale_ids'], true)
            || ! is_int($receipt['total_atomic'] ?? null)
            || $receipt['total_atomic'] <= 0
            || $receipt['total_atomic'] > self::MAX_PREVIEW_CASH_ATOMIC
            || ! is_string($receipt['tender_category'] ?? null)
            || ! is_array($receipt['adjustment'] ?? null)
            || ! $this->adjustmentMatchesReceipt($receipt['adjustment'], $receipt, $profile)
        ) {
            $request->session()->forget(self::RECEIPT_SESSION);
            return null;
        }

        return $receipt;
    }

    /** @param array<string, mixed> $adjustment @param array<string, mixed> $receipt */
    private function adjustmentMatchesReceipt(array $adjustment, array $receipt, PreviewProfile $profile): bool
    {
        if (
            ($adjustment['sale_id'] ?? null) !== $receipt['sale_id']
            || ($adjustment['tender_category'] ?? null) !== $receipt['tender_category']
            || ! is_string($adjustment['status'] ?? null)
            || ! in_array($adjustment['status'], ['COMPLETED', 'VOIDED', 'REFUNDED'], true)
            || ! array_key_exists('void_operation_id', $adjustment)
            || ! array_key_exists('refund_operation_id', $adjustment)
            || ! is_int($adjustment['refund_amount_atomic'] ?? null)
            || ! is_bool($adjustment['idempotent_replay'] ?? null)
        ) {
            return false;
        }

        $voidOperationId = $this->adjustmentOperationId('void', $profile, $receipt['sale_id']);
        $refundOperationId = $this->adjustmentOperationId('refund', $profile, $receipt['sale_id']);

        return match ($adjustment['status']) {
            'COMPLETED' => $adjustment['void_operation_id'] === null
                && $adjustment['refund_operation_id'] === null
                && $adjustment['refund_amount_atomic'] === 0,
            'VOIDED' => $adjustment['void_operation_id'] === $voidOperationId
                && $adjustment['refund_operation_id'] === null
                && $adjustment['refund_amount_atomic'] === 0,
            'REFUNDED' => $receipt['tender_category'] === 'CASH'
                && $adjustment['void_operation_id'] === $voidOperationId
                && $adjustment['refund_operation_id'] === $refundOperationId
                && $adjustment['refund_amount_atomic'] === $receipt['total_atomic'],
            default => false,
        };
    }

    private function adjustmentOperationId(string $kind, PreviewProfile $profile, string $saleId): string
    {
        return 'preview-'.$kind.'-'.substr(hash('sha256', implode('|', [
            $profile->tenantId(),
            $profile->outletId(),
            $saleId,
        ])), 0, 32);
    }

    private function assertEnabled(): void
    {
        abort_unless(
            TechnicalPreviewRuntimePolicy::permits(
                enabled: (bool) config('oneqay.technical_preview.enabled', false),
                runtimeClass: (string) config('oneqay.runtime_class', ''),
                sessionDriver: (string) config('session.driver', ''),
                sessionLifetimeMinutes: (int) config('session.lifetime', 0),
                sessionEncrypted: (bool) config('session.encrypt', false),
                sessionSecure: (bool) config('session.secure', false),
                sessionHttpOnly: (bool) config('session.http_only', false),
                sessionSameSite: (string) config('session.same_site', ''),
                sessionDomain: config('session.domain'),
                sessionPath: (string) config('session.path', ''),
                sessionCookie: (string) config('session.cookie', ''),
            ),
            404,
        );
    }
}
