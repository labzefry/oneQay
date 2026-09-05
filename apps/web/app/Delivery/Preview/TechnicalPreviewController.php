<?php

declare(strict_types=1);

namespace App\Delivery\Preview;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use App\Application\Tenancy\MissingTenantContext;
use App\Domain\Pos\CatalogItem;
use App\Domain\Pos\SaleReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

// Author by Lab | zefry
final class TechnicalPreviewController
{
    private const PRINCIPAL_SESSION = 'oneqay.preview.principal';
    private const CONTEXT_SESSION = 'oneqay.preview.context_selected';
    private const RECEIPT_SESSION = 'oneqay.preview.receipt';
    private const SHIFT_SESSION = 'oneqay.preview.cash_shift';
    private const RECONCILIATION_SESSION = 'oneqay.preview.cash_reconciliation';
    private const MAX_PREVIEW_CASH_ATOMIC = 1_000_000_000;

    public function index(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();

        if ($this->profileFromSession($request, $journey) !== null) {
            return redirect()->route(
                $request->session()->get(self::CONTEXT_SESSION) === true ? 'preview.pos' : 'preview.context',
            );
        }

        return Inertia::render('Preview/SignIn', [
            'profiles' => array_map(
                static fn (PreviewProfile $profile): array => [
                    'principal_id' => $profile->principalId(),
                    'label' => $profile->label(),
                ],
                $journey->profiles(),
            ),
            'previewLabel' => 'Synthetic Technical Preview',
            'productionReady' => false,
        ]);
    }

    public function signIn(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $journey->profile(trim((string) $request->input('principal')));

        if ($profile === null) {
            return redirect()->route('preview.index')
                ->withErrors(['principal' => 'Synthetic preview identity is not allowed.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::PRINCIPAL_SESSION, $profile->principalId());
        $request->session()->forget([
            self::CONTEXT_SESSION,
            self::RECEIPT_SESSION,
            self::SHIFT_SESSION,
            self::RECONCILIATION_SESSION,
        ]);

        return redirect()->route('preview.context');
    }

    public function context(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->profileFromSession($request, $journey);

        if ($profile === null) {
            return redirect()->route('preview.index');
        }

        return Inertia::render('Preview/Context', [
            'profile' => $this->projectProfile($profile),
            'previewLabel' => 'Synthetic Technical Preview',
        ]);
    }

    public function selectContext(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->profileFromSession($request, $journey);

        if ($profile === null) {
            return redirect()->route('preview.index');
        }

        if ((string) $request->input('selection') !== 'primary') {
            return redirect()->route('preview.context')
                ->withErrors(['selection' => 'Technical Preview context selection is invalid.']);
        }

        try {
            $journey->catalog($profile);
        } catch (IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            return redirect()->route('preview.context')
                ->withErrors(['selection' => 'Technical Preview context could not be verified.']);
        }

        $request->session()->put(self::CONTEXT_SESSION, true);
        $request->session()->forget([
            self::RECEIPT_SESSION,
            self::SHIFT_SESSION,
            self::RECONCILIATION_SESSION,
        ]);

        return redirect()->route('preview.pos');
    }

    public function pos(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        try {
            $catalog = $journey->catalog($profile);
        } catch (IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            $request->session()->forget(self::CONTEXT_SESSION);
            return redirect()->route('preview.context')
                ->withErrors(['selection' => 'Technical Preview context requires re-verification.']);
        }

        return Inertia::render('Preview/Pos', [
            'profile' => $this->projectProfile($profile),
            'catalog' => array_map([$this, 'projectCatalogItem'], $catalog),
            'shift' => $this->shiftFromSession($request, $profile),
            'previewLabel' => 'Synthetic Technical Preview',
            'productionReady' => false,
        ]);
    }

    public function openShift(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        if ($this->shiftFromSession($request, $profile) !== null) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'Synthetic cash shift is already open.']);
        }

        $openingCashAtomic = $this->boundedAtomicInput($request, 'opening_cash_atomic');
        if ($openingCashAtomic === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'Opening cash must be a bounded non-negative IDR amount.']);
        }

        try {
            $journey->catalog($profile);
        } catch (IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            return redirect()->route('preview.context')
                ->withErrors(['selection' => 'Technical Preview context requires re-verification.']);
        }

        $shiftId = 'preview-shift-'.bin2hex(random_bytes(16));
        $openingCashEvidenceId = 'preview-opening-'.bin2hex(random_bytes(16));

        $request->session()->put(self::SHIFT_SESSION, [
            'status' => 'OPEN',
            'shift_id' => $shiftId,
            'opening_cash_evidence_id' => $openingCashEvidenceId,
            'tenant_id' => $profile->tenantId(),
            'organization_id' => $profile->organizationId(),
            'outlet_id' => $profile->outletId(),
            'device_id' => $profile->deviceId(),
            'opening_cash_atomic' => $openingCashAtomic,
            'cash_sales_atomic' => 0,
            'cash_refunds_atomic' => 0,
            'sale_count' => 0,
            'recorded_sale_ids' => [],
            'voided_sale_ids' => [],
            'refunded_sale_ids' => [],
            'opened_at_unix' => time(),
        ]);
        $request->session()->forget([self::RECEIPT_SESSION, self::RECONCILIATION_SESSION]);

        return redirect()->route('preview.pos');
    }

    public function sale(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $shift = $this->shiftFromSession($request, $profile);
        if ($shift === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'Open a synthetic cash shift before recording a sale.']);
        }

        $rawLines = $request->input('lines', []);
        if (! is_array($rawLines)) {
            return redirect()->route('preview.pos')->withErrors(['sale' => 'Cart payload is invalid.']);
        }

        $lines = [];
        foreach ($rawLines as $line) {
            if (! is_array($line)) {
                return redirect()->route('preview.pos')->withErrors(['sale' => 'Cart payload is invalid.']);
            }
            $lines[] = [
                'product_id' => (string) ($line['product_id'] ?? ''),
                'quantity' => (int) ($line['quantity'] ?? 0),
            ];
        }

        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'preview-correlation-missing');

        try {
            $receipt = $journey->completeSale(
                $profile,
                $lines,
                (string) $request->input('tender_category'),
                (int) $request->input('tendered_atomic_units', -1),
                (string) $request->input('operation_id'),
                $correlationId,
            );
            $projectedReceipt = $this->projectReceipt($receipt, $journey->catalog($profile));
            $request->session()->put(self::RECEIPT_SESSION, $projectedReceipt);

            $recordedSaleIds = $shift['recorded_sale_ids'];
            if (! in_array($receipt->saleId(), $recordedSaleIds, true)) {
                $recordedSaleIds[] = $receipt->saleId();
                $shift['recorded_sale_ids'] = $recordedSaleIds;
                $shift['sale_count']++;
                if ($receipt->tenderCategory()->value === 'CASH') {
                    $shift['cash_sales_atomic'] += $receipt->total()->atomicUnits();
                }
                if ($shift['cash_sales_atomic'] > self::MAX_PREVIEW_CASH_ATOMIC) {
                    throw new PosTransactionViolation();
                }
                $request->session()->put(self::SHIFT_SESSION, $shift);
            }
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.pos')
                ->withErrors(['sale' => 'Synthetic sale was rejected safely. Check cart, context, stock, and tender.']);
        }

        return redirect()->route('preview.receipt');
    }

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
        $operationId = 'preview-void-'.substr(hash('sha256', implode('|', [
            $profile->tenantId(),
            $profile->outletId(),
            $saleId,
        ])), 0, 32);

        try {
            $adjustment = $journey->voidSale($profile, $saleId, $operationId);
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
        $operationId = 'preview-refund-'.substr(hash('sha256', implode('|', [
            $profile->tenantId(),
            $profile->outletId(),
            $saleId,
        ])), 0, 32);

        try {
            $adjustment = $journey->refundCashSale($profile, $saleId, $operationId);
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

    public function closeShift(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $shift = $this->shiftFromSession($request, $profile);
        if ($shift === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'No synthetic cash shift is open.']);
        }

        $observedClosingAtomic = $this->boundedAtomicInput($request, 'observed_closing_atomic');
        if ($observedClosingAtomic === null) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'Closing cash must be a bounded non-negative IDR amount.']);
        }

        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'preview-close-correlation-missing');

        try {
            $variance = $journey->reconcileCash(
                $profile,
                $shift['shift_id'],
                $shift['opening_cash_evidence_id'],
                $shift['opening_cash_atomic'],
                $shift['cash_sales_atomic'],
                $observedClosingAtomic,
                time(),
                $correlationId,
                $shift['cash_refunds_atomic'],
            );
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.pos')
                ->withErrors(['shift' => 'Synthetic cash reconciliation was rejected safely.']);
        }

        $request->session()->put(self::RECONCILIATION_SESSION, $this->projectReconciliation($shift, $variance));
        $request->session()->forget([self::SHIFT_SESSION, self::RECEIPT_SESSION]);

        return redirect()->route('preview.reconciliation');
    }

    public function receipt(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $shift = $this->shiftFromSession($request, $profile);
        $receipt = $shift === null ? null : $this->receiptFromSession($request, $profile, $shift);
        if ($receipt === null) {
            return redirect()->route('preview.pos');
        }

        return Inertia::render('Preview/Receipt', [
            'profile' => $this->projectProfile($profile),
            'receipt' => $receipt,
            'previewLabel' => 'Synthetic Technical Preview',
            'productionReady' => false,
        ]);
    }

    public function reconciliation(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $reconciliation = $request->session()->get(self::RECONCILIATION_SESSION);
        if (! is_array($reconciliation) || ! $this->reconciliationMatchesProfile($reconciliation, $profile)) {
            $request->session()->forget(self::RECONCILIATION_SESSION);
            return redirect()->route('preview.pos');
        }

        return Inertia::render('Preview/Reconciliation', [
            'profile' => $this->projectProfile($profile),
            'reconciliation' => $reconciliation,
            'previewLabel' => 'Synthetic Technical Preview',
            'productionReady' => false,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->assertEnabled();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('preview.index');
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

    private function profileFromSession(Request $request, TechnicalPreviewJourney $journey): ?PreviewProfile
    {
        $principalId = $request->session()->get(self::PRINCIPAL_SESSION);
        return is_string($principalId) ? $journey->profile($principalId) : null;
    }

    private function verifiedSessionProfile(Request $request, TechnicalPreviewJourney $journey): PreviewProfile|RedirectResponse
    {
        $profile = $this->profileFromSession($request, $journey);
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
        if (! is_array($shift)) {
            return null;
        }

        if (
            ($shift['status'] ?? null) !== 'OPEN'
            || ($shift['tenant_id'] ?? null) !== $profile->tenantId()
            || ($shift['organization_id'] ?? null) !== $profile->organizationId()
            || ($shift['outlet_id'] ?? null) !== $profile->outletId()
            || ($shift['device_id'] ?? null) !== $profile->deviceId()
            || ! is_string($shift['shift_id'] ?? null)
            || ! is_string($shift['opening_cash_evidence_id'] ?? null)
            || ! is_int($shift['opening_cash_atomic'] ?? null)
            || ! is_int($shift['cash_sales_atomic'] ?? null)
            || ! is_int($shift['cash_refunds_atomic'] ?? null)
            || ! is_int($shift['sale_count'] ?? null)
            || ! is_array($shift['recorded_sale_ids'] ?? null)
            || ! is_array($shift['voided_sale_ids'] ?? null)
            || ! is_array($shift['refunded_sale_ids'] ?? null)
            || ! is_int($shift['opened_at_unix'] ?? null)
            || $shift['opening_cash_atomic'] < 0
            || $shift['cash_sales_atomic'] < 0
            || $shift['cash_refunds_atomic'] < 0
            || $shift['cash_refunds_atomic'] > $shift['cash_sales_atomic']
            || $shift['sale_count'] < 0
            || count($shift['recorded_sale_ids']) !== $shift['sale_count']
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
            || ! is_array($receipt['adjustment'] ?? null)
        ) {
            $request->session()->forget(self::RECEIPT_SESSION);
            return null;
        }

        return $receipt;
    }

    private function boundedAtomicInput(Request $request, string $field): ?int
    {
        $value = filter_var($request->input($field), FILTER_VALIDATE_INT);
        if ($value === false || $value < 0 || $value > self::MAX_PREVIEW_CASH_ATOMIC) {
            return null;
        }

        return $value;
    }

    private function reconciliationMatchesProfile(array $reconciliation, PreviewProfile $profile): bool
    {
        return ($reconciliation['tenant_id'] ?? null) === $profile->tenantId()
            && ($reconciliation['organization_id'] ?? null) === $profile->organizationId()
            && ($reconciliation['outlet_id'] ?? null) === $profile->outletId()
            && ($reconciliation['device_id'] ?? null) === $profile->deviceId();
    }

    private function projectProfile(PreviewProfile $profile): array
    {
        return [
            'label' => $profile->label(),
            'tenant_id' => $profile->tenantId(),
            'organization_id' => $profile->organizationId(),
            'outlet_id' => $profile->outletId(),
            'device_id' => $profile->deviceId(),
        ];
    }

    private function projectCatalogItem(CatalogItem $item): array
    {
        return [
            'product_id' => $item->productId()->value(),
            'name' => $item->displayName(),
            'unit_price_atomic' => $item->unitPrice()->atomicUnits(),
            'currency' => $item->unitPrice()->currency(),
        ];
    }

    private function projectReceipt(SaleReceipt $receipt, array $catalog): array
    {
        $names = [];
        foreach ($catalog as $item) {
            $names[$item->productId()->value()] = $item->displayName();
        }

        return [
            'sale_id' => $receipt->saleId(),
            'operation_id' => $receipt->operationId(),
            'tenant_id' => $receipt->tenantId(),
            'actor_id' => $receipt->actorId(),
            'outlet_id' => $receipt->outletId(),
            'device_id' => $receipt->deviceId(),
            'lines' => array_map(static fn ($line): array => [
                'product_id' => $line->productId()->value(),
                'name' => $names[$line->productId()->value()] ?? 'Synthetic Product',
                'quantity' => $line->quantity(),
                'unit_price_atomic' => $line->unitPrice()->atomicUnits(),
                'line_total_atomic' => $line->lineTotal()->atomicUnits(),
            ], $receipt->lines()),
            'total_atomic' => $receipt->total()->atomicUnits(),
            'currency' => $receipt->total()->currency(),
            'tender_category' => $receipt->tenderCategory()->value,
            'evidence_mode' => $receipt->evidenceMode(),
            'change_atomic' => $receipt->changeAmount()->atomicUnits(),
            'correlation_id' => $receipt->correlationId(),
            'adjustment' => [
                'sale_id' => $receipt->saleId(),
                'status' => 'COMPLETED',
                'void_operation_id' => null,
                'refund_operation_id' => null,
                'refund_amount_atomic' => 0,
                'tender_category' => $receipt->tenderCategory()->value,
                'idempotent_replay' => false,
            ],
        ];
    }

    private function projectReconciliation(array $shift, CashVarianceResult $variance): array
    {
        return [
            'tenant_id' => $variance->tenantId(),
            'organization_id' => $variance->organizationId(),
            'outlet_id' => $variance->outletId(),
            'device_id' => $shift['device_id'],
            'shift_id' => $variance->shiftId(),
            'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
            'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
            'opening_cash_atomic' => $shift['opening_cash_atomic'],
            'cash_sales_atomic' => $shift['cash_sales_atomic'],
            'cash_refunds_atomic' => $shift['cash_refunds_atomic'],
            'sale_count' => $shift['sale_count'],
            'void_count' => count($shift['voided_sale_ids']),
            'refund_count' => count($shift['refunded_sale_ids']),
            'expected_cash_atomic' => $variance->expectedCashAtomic(),
            'observed_closing_atomic' => $variance->observedClosingAtomic(),
            'variance_atomic' => $variance->varianceAtomic(),
            'variance_direction' => $variance->direction(),
            'currency' => $variance->currency(),
            'cutoff_at_unix' => $variance->cutoffAtUnix(),
        ];
    }
}
