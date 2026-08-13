<?php

declare(strict_types=1);

namespace App\Delivery\Preview;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
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
        $request->session()->forget([self::CONTEXT_SESSION, self::RECEIPT_SESSION]);

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
        $request->session()->forget(self::RECEIPT_SESSION);

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
            'previewLabel' => 'Synthetic Technical Preview',
            'productionReady' => false,
        ]);
    }

    public function sale(Request $request, TechnicalPreviewJourney $journey): RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
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
            $request->session()->put(self::RECEIPT_SESSION, $this->projectReceipt($receipt, $journey->catalog($profile)));
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PosAccessViolation|PosTransactionViolation) {
            return redirect()->route('preview.pos')
                ->withErrors(['sale' => 'Synthetic sale was rejected safely. Check cart, context, stock, and tender.']);
        }

        return redirect()->route('preview.receipt');
    }

    public function receipt(Request $request, TechnicalPreviewJourney $journey): Response|RedirectResponse
    {
        $this->assertEnabled();
        $profile = $this->verifiedSessionProfile($request, $journey);
        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $receipt = $request->session()->get(self::RECEIPT_SESSION);
        if (! is_array($receipt)) {
            return redirect()->route('preview.pos');
        }

        return Inertia::render('Preview/Receipt', [
            'profile' => $this->projectProfile($profile),
            'receipt' => $receipt,
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
        $runtimeClass = strtolower((string) env('ONEQAY_RUNTIME_CLASS', ''));
        $enabled = filter_var(env('ONEQAY_TECHNICAL_PREVIEW_ENABLED', false), FILTER_VALIDATE_BOOL);

        abort_unless($enabled && in_array($runtimeClass, ['local', 'test', 'testing', 'ci', 'preview'], true), 404);
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
        ];
    }
}
