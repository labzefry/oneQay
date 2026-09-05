<?php

declare(strict_types=1);

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Preview\DeterministicPreviewFixture;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request as HttpRequest;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        fwrite(STDERR, "M7.4A regression failed: {$case}\n");
        exit(1);
    }
};

$expect = static function (string $class, callable $callback, string $case) use ($assert): Throwable {
    try {
        $callback();
    } catch (Throwable $exception) {
        $assert($exception instanceof $class, $case.' throws '.$class);
        return $exception;
    }

    $assert(false, $case.' must throw');
    throw new RuntimeException('unreachable');
};

$assert(
    TechnicalPreviewRuntimePolicy::permits(
        enabled: true,
        runtimeClass: 'ci',
        sessionDriver: 'array',
        sessionLifetimeMinutes: 120,
        sessionEncrypted: false,
        sessionSecure: false,
        sessionHttpOnly: true,
        sessionSameSite: 'lax',
        sessionDomain: null,
        sessionPath: '/',
        sessionCookie: 'oneqay-session',
    ),
    'M74A-RUNTIME-001 qualification runtime remains intentionally available with in-memory session',
);

$deployedRuntime = static function (
    string $driver = 'file',
    int $lifetime = 60,
    bool $encrypted = true,
    bool $secure = true,
    bool $httpOnly = true,
    string $sameSite = 'lax',
    ?string $domain = null,
    string $path = '/',
    string $cookie = 'oneqay-preview-session',
    bool $enabled = true,
    string $runtimeClass = 'preview',
): bool {
    return TechnicalPreviewRuntimePolicy::permits(
        enabled: $enabled,
        runtimeClass: $runtimeClass,
        sessionDriver: $driver,
        sessionLifetimeMinutes: $lifetime,
        sessionEncrypted: $encrypted,
        sessionSecure: $secure,
        sessionHttpOnly: $httpOnly,
        sessionSameSite: $sameSite,
        sessionDomain: $domain,
        sessionPath: $path,
        sessionCookie: $cookie,
    );
};

$assert($deployedRuntime(), 'M74A-RUNTIME-002 exact deployed Preview session envelope is admitted');
$assert(! $deployedRuntime(driver: 'array'), 'M74A-RUNTIME-003 deployed Preview rejects non-file session');
$assert(! $deployedRuntime(lifetime: 61), 'M74A-RUNTIME-004 deployed Preview rejects session lifetime above 60 minutes');
$assert(! $deployedRuntime(lifetime: 0), 'M74A-RUNTIME-005 deployed Preview rejects non-positive session lifetime');
$assert(! $deployedRuntime(encrypted: false), 'M74A-RUNTIME-006 deployed Preview requires encrypted session payload');
$assert(! $deployedRuntime(secure: false), 'M74A-RUNTIME-007 deployed Preview requires Secure cookie');
$assert(! $deployedRuntime(httpOnly: false), 'M74A-RUNTIME-008 deployed Preview requires HttpOnly cookie');
$assert(! $deployedRuntime(sameSite: 'none'), 'M74A-RUNTIME-009 deployed Preview requires SameSite=Lax');
$assert(! $deployedRuntime(domain: '.example.test'), 'M74A-RUNTIME-010 deployed Preview requires host-only cookie');
$assert(! $deployedRuntime(path: '/technical-preview'), 'M74A-RUNTIME-011 deployed Preview requires root cookie path');
$assert(! $deployedRuntime(cookie: 'oneqay-session'), 'M74A-RUNTIME-012 deployed Preview requires dedicated cookie name');
$assert(! $deployedRuntime(enabled: false), 'M74A-RUNTIME-013 deployed Preview remains disabled unless explicitly armed');
$assert(! $deployedRuntime(runtimeClass: 'production'), 'M74A-RUNTIME-014 Production runtime is never admitted as Technical Preview');

$fixtures = new DeterministicPreviewFixture();
$memberships = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-a' => ['tenant-alpha'],
    'synthetic-principal-b' => ['tenant-beta'],
]);
$relationships = new SyntheticOrganizationalRelationshipVerifier([
    'synthetic-principal-a' => [[
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-alpha',
        'outlet' => 'outlet-alpha',
        'device' => 'device-alpha',
    ]],
    'synthetic-principal-b' => [[
        'tenant' => 'tenant-beta',
        'organization' => 'organization-beta',
        'outlet' => 'outlet-beta',
        'device' => 'device-beta',
    ]],
]);
$tenantContexts = new RequestTenantContextStore();
$organizationalContexts = new RequestOrganizationalContextStore();
$organizations = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new RequireVerifiedTenantContext(),
    $memberships,
    $relationships,
    $organizationalContexts,
);
$journey = new TechnicalPreviewJourney(
    $fixtures,
    $memberships,
    $tenantContexts,
    $organizations,
    new CompleteSyntheticSale($fixtures, $organizationalContexts),
);

$profiles = $journey->profiles();
$assert(count($profiles) === 2, 'M74A-AUTH-001 exactly two allowlisted demo identities');
$assert($journey->profile('synthetic-principal-a') !== null, 'M74A-AUTH-002 alpha identity allowlisted');
$assert($journey->profile('attacker-controlled') === null, 'M74A-AUTH-003 arbitrary identity denied');

$alpha = $journey->profile('synthetic-principal-a');
$beta = $journey->profile('synthetic-principal-b');
$assert($alpha instanceof PreviewProfile, 'M74A-CTX-001 alpha profile resolved');
$assert($beta instanceof PreviewProfile, 'M74A-CTX-002 beta profile resolved');

$alphaCatalog = $journey->catalog($alpha);
$betaCatalog = $journey->catalog($beta);
$assert(count($alphaCatalog) === 2, 'M74A-CAT-001 alpha catalog is deterministic');
$assert(count($betaCatalog) === 2, 'M74A-CAT-002 beta catalog is deterministic');
foreach ($alphaCatalog as $item) {
    $assert($item->tenantId()->value() === 'tenant-alpha', 'M74A-ISO-001 alpha catalog tenant isolated');
    $assert($item->outletId()->value() === 'outlet-alpha', 'M74A-ISO-002 alpha catalog outlet isolated');
}
foreach ($betaCatalog as $item) {
    $assert($item->tenantId()->value() === 'tenant-beta', 'M74A-ISO-003 beta catalog tenant isolated');
    $assert($item->outletId()->value() === 'outlet-beta', 'M74A-ISO-004 beta catalog outlet isolated');
}
$assert($tenantContexts->current() === null, 'M74A-CTX-003 tenant context cleared after catalog');
$assert($organizationalContexts->current() === null, 'M74A-CTX-004 org context cleared after catalog');

$forged = new PreviewProfile(
    'synthetic-principal-a',
    'Forged',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    'device-beta',
);
$expect(
    OrganizationalAccessViolation::class,
    static fn () => $journey->catalog($forged),
    'M74A-ISO-005 client-forged foreign context denied',
);

$cashReceipt = $journey->completeSale(
    $alpha,
    [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'CASH',
    6000,
    'preview-op-alpha-0001',
    'preview-correlation-alpha-0001',
);
$assert($cashReceipt->tenantId() === 'tenant-alpha', 'M74A-SALE-001 receipt tenant server verified');
$assert($cashReceipt->actorId() === 'synthetic-principal-a', 'M74A-SALE-002 receipt actor server verified');
$assert($cashReceipt->total()->atomicUnits() === 5000, 'M74A-SALE-003 authoritative server total');
$assert($cashReceipt->changeAmount()->atomicUnits() === 1000, 'M74A-SALE-004 exact cash change');
$assert($cashReceipt->evidenceMode() === 'CASH_COUNTED', 'M74A-SALE-005 cash evidence mode');
$assert($cashReceipt->correlationId() === 'preview-correlation-alpha-0001', 'M74A-OBS-001 correlation preserved');

$replay = $journey->completeSale(
    $alpha,
    [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'CASH',
    6000,
    'preview-op-alpha-0001',
    'preview-correlation-alpha-retry-0002',
);
$assert($replay->saleId() === $cashReceipt->saleId(), 'M74A-IDEM-001 stable operation replay returns same sale');

$manual = $journey->completeSale(
    $alpha,
    [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
    'MANUAL_EXTERNAL',
    1999,
    'preview-op-alpha-manual-0002',
    'preview-correlation-alpha-manual-0003',
);
$assert($manual->evidenceMode() === 'OPERATOR_RECORDED', 'M74A-PAY-001 manual tender stays operator recorded');
$assert($manual->evidenceMode() !== 'PROVIDER_VERIFIED', 'M74A-PAY-002 no invented provider verification');

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->completeSale(
        $beta,
        [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
        'CASH',
        5000,
        'preview-op-beta-foreign-0003',
        'preview-correlation-beta-foreign-0004',
    ),
    'M74A-ISO-006 beta cannot transact alpha catalog item',
);

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->completeSale(
        $beta,
        [['product_id' => 'synthetic-product-beta', 'quantity' => 1]],
        'CASH',
        1000,
        'preview-op-beta-underpay-0004',
        'preview-correlation-beta-underpay-0005',
    ),
    'M74A-PAY-003 insufficient tender safely rejected',
);

$variance = $journey->reconcileCash(
    $alpha,
    'preview-shift-alpha-0001',
    'preview-opening-alpha-0001',
    1000,
    5000,
    6100,
    7000,
    'preview-close-correlation-alpha-0001',
);
$assert($variance->tenantId() === 'tenant-alpha', 'M74A-CASH-001 reconciliation tenant server verified');
$assert($variance->outletId() === 'outlet-alpha', 'M74A-CASH-002 reconciliation outlet server verified');
$assert($variance->expectedCashAtomic() === 6000, 'M74A-CASH-003 opening plus cash sales derives expected cash');
$assert($variance->observedClosingAtomic() === 6100, 'M74A-CASH-004 observed closing cash preserved');
$assert($variance->varianceAtomic() === 100, 'M74A-CASH-005 exact positive variance');
$assert($variance->direction() === CashVarianceResult::DIRECTION_OVER, 'M74A-CASH-006 canonical OVER direction');
$assert($tenantContexts->current() === null, 'M74A-CASH-007 tenant context cleared after reconciliation');
$assert($organizationalContexts->current() === null, 'M74A-CASH-008 org context cleared after reconciliation');

$expect(
    OrganizationalAccessViolation::class,
    static fn () => $journey->reconcileCash(
        $forged,
        'preview-shift-forged-0001',
        'preview-opening-forged-0001',
        1000,
        5000,
        6000,
        7001,
        'preview-close-correlation-forged-0001',
    ),
    'M74A-ISO-007 forged foreign cash reconciliation denied',
);

$assert($tenantContexts->current() === null, 'M74A-CTX-005 tenant context cleared after sale/cash controls');
$assert($organizationalContexts->current() === null, 'M74A-CTX-006 org context cleared after sale/cash controls');

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var HttpKernel $kernel */
$kernel = $app->make(HttpKernel::class);

/** @var array<string, string> $cookies */
$cookies = [];
$csrfToken = null;

$sendHttp = static function (
    string $method,
    string $uri,
    array $parameters = [],
    array $server = [],
) use ($kernel, &$cookies, &$csrfToken): array {
    $method = strtoupper($method);
    if ($method !== 'GET' && $csrfToken !== null && ! array_key_exists('_token', $parameters)) {
        $parameters['_token'] = $csrfToken;
    }

    $request = HttpRequest::create(
        $uri,
        $method,
        $parameters,
        $cookies,
        [],
        array_replace([
            'HTTP_HOST' => 'oneqay.test',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        ], $server),
    );

    $response = $kernel->handle($request);

    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getExpiresTime() !== 0 && $cookie->getExpiresTime() < time()) {
            unset($cookies[$cookie->getName()]);
            continue;
        }
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    if ($request->hasSession()) {
        $csrfToken = $request->session()->token();
    }

    $kernel->terminate($request, $response);

    return [$response, $request];
};

$manifestPath = __DIR__.'/../public/build/manifest.json';
$assert(is_file($manifestPath), 'M74A-HTTP-000 Vite manifest exists for Inertia versioning');
$inertiaVersion = hash_file('xxh128', $manifestPath);
$assert(is_string($inertiaVersion) && $inertiaVersion !== '', 'M74A-HTTP-000A Inertia asset version is deterministic');
$inertiaServer = [
    'HTTP_X_INERTIA' => 'true',
    'HTTP_X_INERTIA_VERSION' => $inertiaVersion,
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_ACCEPT' => 'application/json',
];

[$signInPage] = $sendHttp('GET', '/technical-preview');
$assert($signInPage->getStatusCode() === 200, 'M74A-HTTP-001 sign-in page is reachable in explicit CI preview');
$assert(str_contains((string) $signInPage->getContent(), 'Synthetic Technical Preview'), 'M74A-HTTP-002 sign-in page labels preview');

[$invalidSignIn] = $sendHttp('POST', '/technical-preview/sign-in', [
    'principal' => 'attacker-controlled',
]);
$assert($invalidSignIn->getStatusCode() === 302, 'M74A-HTTP-003 invalid identity safely redirects');

[$validSignIn] = $sendHttp('POST', '/technical-preview/sign-in', [
    'principal' => 'synthetic-principal-a',
]);
$assert($validSignIn->getStatusCode() === 302, 'M74A-HTTP-004 allowlisted synthetic sign-in redirects');
$assert(str_ends_with((string) $validSignIn->headers->get('Location'), '/technical-preview/context'), 'M74A-HTTP-005 sign-in advances to context');

[$contextPage] = $sendHttp('GET', '/technical-preview/context');
$assert($contextPage->getStatusCode() === 200, 'M74A-HTTP-006 context page requires valid preview session');
$assert(str_contains((string) $contextPage->getContent(), 'tenant-alpha'), 'M74A-HTTP-007 context is server-derived alpha context');

[$selectContext] = $sendHttp('POST', '/technical-preview/context', [
    'selection' => 'primary',
    'tenant_id' => 'tenant-beta',
    'outlet_id' => 'outlet-beta',
]);
$assert($selectContext->getStatusCode() === 302, 'M74A-HTTP-008 context selection redirects');
$assert(str_ends_with((string) $selectContext->headers->get('Location'), '/technical-preview/pos'), 'M74A-HTTP-009 context advances to POS');

[$posPage] = $sendHttp('GET', '/technical-preview/pos');
$assert($posPage->getStatusCode() === 200, 'M74A-HTTP-010 POS page is reachable after verified context');
$assert(str_contains((string) $posPage->getContent(), 'Synthetic Alpha Product'), 'M74A-HTTP-011 alpha catalog appears');
$assert(! str_contains((string) $posPage->getContent(), 'Synthetic Beta Product'), 'M74A-HTTP-012 beta catalog is not disclosed');

[$posInertia] = $sendHttp('GET', '/technical-preview/pos', [], $inertiaServer);
$assert($posInertia->getStatusCode() === 200, 'M74A-HTTP-013 POS Inertia boundary is reachable');
$posPayload = json_decode((string) $posInertia->getContent(), true, 512, JSON_THROW_ON_ERROR);
$assert(
    ($posPayload['component'] ?? null) === 'Preview/Pos'
        && array_key_exists('shift', $posPayload['props'] ?? [])
        && $posPayload['props']['shift'] === null
        && ($posPayload['props']['productionReady'] ?? null) === false,
    'M74A-HTTP-013A POS contract exposes no active shift before opening and remains non-production',
);

[$saleBeforeShift] = $sendHttp('POST', '/technical-preview/sale', [
    'lines' => [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
    'tender_category' => 'CASH',
    'tendered_atomic_units' => 1999,
    'operation_id' => 'preview-http-before-shift-0001',
]);
$assert($saleBeforeShift->getStatusCode() === 302, 'M74A-HTTP-014 sale before shift safely redirects');
$assert(str_ends_with((string) $saleBeforeShift->headers->get('Location'), '/technical-preview/pos'), 'M74A-HTTP-015 sale before shift stays at POS');

[$openShift] = $sendHttp('POST', '/technical-preview/shift/open', [
    'opening_cash_atomic' => 1000,
]);
$assert($openShift->getStatusCode() === 302, 'M74A-HTTP-016 shift opening redirects');
$assert(str_ends_with((string) $openShift->headers->get('Location'), '/technical-preview/pos'), 'M74A-HTTP-017 shift opening returns to POS');

[$openShiftPos] = $sendHttp('GET', '/technical-preview/pos', [], $inertiaServer);
$assert($openShiftPos->getStatusCode() === 200, 'M74A-HTTP-018 POS remains reachable with open shift');
$openShiftPayload = json_decode((string) $openShiftPos->getContent(), true, 512, JSON_THROW_ON_ERROR);
$openShiftState = $openShiftPayload['props']['shift'] ?? null;
$assert(
    ($openShiftPayload['component'] ?? null) === 'Preview/Pos'
        && is_array($openShiftState)
        && ($openShiftState['status'] ?? null) === 'OPEN',
    'M74A-HTTP-019 server-owned open shift is projected through POS contract',
);
$assert(
    ($openShiftState['opening_cash_atomic'] ?? null) === 1000
        && ($openShiftState['cash_sales_atomic'] ?? null) === 0
        && ($openShiftState['sale_count'] ?? null) === 0,
    'M74A-HTTP-020 opening cash and empty cash ledger are projected exactly',
);

[$sale] = $sendHttp('POST', '/technical-preview/sale', [
    'lines' => [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'tender_category' => 'CASH',
    'tendered_atomic_units' => 6000,
    'operation_id' => 'preview-http-alpha-0001',
]);
$assert($sale->getStatusCode() === 302, 'M74A-HTTP-021 synthetic sale redirects');
$assert(str_ends_with((string) $sale->headers->get('Location'), '/technical-preview/receipt'), 'M74A-HTTP-022 sale advances to receipt');

[$receiptPage] = $sendHttp('GET', '/technical-preview/receipt');
$assert($receiptPage->getStatusCode() === 200, 'M74A-HTTP-023 receipt preview is reachable');
$assert(str_contains((string) $receiptPage->getContent(), 'CASH_COUNTED'), 'M74A-HTTP-024 receipt preserves cash evidence mode');

[$receiptInertia] = $sendHttp('GET', '/technical-preview/receipt', [], $inertiaServer);
$assert($receiptInertia->getStatusCode() === 200, 'M74A-HTTP-025 receipt Inertia boundary is reachable');
$receiptPayload = json_decode((string) $receiptInertia->getContent(), true, 512, JSON_THROW_ON_ERROR);
$assert(($receiptPayload['component'] ?? null) === 'Preview/Receipt', 'M74A-HTTP-026 receipt component is exact');
$assert(
    ($receiptPayload['props']['receipt']['total_atomic'] ?? null) === 5000
        && ($receiptPayload['props']['receipt']['change_atomic'] ?? null) === 1000
        && ($receiptPayload['props']['receipt']['evidence_mode'] ?? null) === 'CASH_COUNTED'
        && ($receiptPayload['props']['productionReady'] ?? null) === false,
    'M74A-HTTP-027 receipt contract preserves authoritative total, change, evidence mode, and non-production boundary',
);

[$posAfterSale] = $sendHttp('GET', '/technical-preview/pos', [], $inertiaServer);
$assert($posAfterSale->getStatusCode() === 200, 'M74A-HTTP-028 POS returns with same open shift after receipt');
$posAfterSalePayload = json_decode((string) $posAfterSale->getContent(), true, 512, JSON_THROW_ON_ERROR);
$posAfterSaleShift = $posAfterSalePayload['props']['shift'] ?? null;
$assert(
    is_array($posAfterSaleShift)
        && ($posAfterSaleShift['status'] ?? null) === 'OPEN'
        && ($posAfterSaleShift['opening_cash_atomic'] ?? null) === 1000,
    'M74A-HTTP-029 same server-owned shift remains open after receipt',
);
$assert(
    ($posAfterSaleShift['cash_sales_atomic'] ?? null) === 5000
        && ($posAfterSaleShift['sale_count'] ?? null) === 1,
    'M74A-HTTP-030 cash ledger uses authoritative receipt total exactly once and excludes change',
);

[$closeShift] = $sendHttp('POST', '/technical-preview/shift/close', [
    'observed_closing_atomic' => 6100,
]);
$assert($closeShift->getStatusCode() === 302, 'M74A-HTTP-031 closing cash redirects');
$assert(str_ends_with((string) $closeShift->headers->get('Location'), '/technical-preview/reconciliation'), 'M74A-HTTP-032 closing cash advances to reconciliation');

[$reconciliationPage] = $sendHttp('GET', '/technical-preview/reconciliation', [], $inertiaServer);
$assert($reconciliationPage->getStatusCode() === 200, 'M74A-HTTP-033 reconciliation Inertia boundary is reachable');
$reconciliationPayload = json_decode((string) $reconciliationPage->getContent(), true, 512, JSON_THROW_ON_ERROR);
$reconciliation = $reconciliationPayload['props']['reconciliation'] ?? null;
$assert(
    ($reconciliationPayload['component'] ?? null) === 'Preview/Reconciliation'
        && is_array($reconciliation),
    'M74A-HTTP-034 reconciliation component and server contract are exact',
);
$assert(
    ($reconciliation['expected_cash_atomic'] ?? null) === 6000
        && ($reconciliation['observed_closing_atomic'] ?? null) === 6100
        && ($reconciliation['variance_atomic'] ?? null) === 100
        && ($reconciliation['variance_direction'] ?? null) === 'OVER',
    'M74A-HTTP-035 canonical expected, observed, variance, and OVER outcome are projected',
);
$assert(
    ($reconciliation['opening_cash_atomic'] ?? null) === 1000
        && ($reconciliation['cash_sales_atomic'] ?? null) === 5000
        && ($reconciliation['sale_count'] ?? null) === 1,
    'M74A-HTTP-036 reconciliation preserves opening cash and exactly-once cash ledger evidence',
);
$assert(
    is_string($reconciliation['opening_cash_evidence_id'] ?? null)
        && $reconciliation['opening_cash_evidence_id'] !== ''
        && is_string($reconciliation['closing_cash_evidence_id'] ?? null)
        && $reconciliation['closing_cash_evidence_id'] !== '',
    'M74A-HTTP-037 reconciliation exposes server-owned opening and closing evidence identifiers',
);
$assert(
    ($reconciliationPayload['props']['productionReady'] ?? null) === false,
    'M74A-HTTP-038 reconciliation preserves explicit non-production boundary',
);

[$postClosePos] = $sendHttp('GET', '/technical-preview/pos', [], $inertiaServer);
$assert($postClosePos->getStatusCode() === 200, 'M74A-HTTP-039 POS remains usable after reconciliation');
$postClosePayload = json_decode((string) $postClosePos->getContent(), true, 512, JSON_THROW_ON_ERROR);
$assert(
    ($postClosePayload['component'] ?? null) === 'Preview/Pos'
        && array_key_exists('shift', $postClosePayload['props'] ?? [])
        && $postClosePayload['props']['shift'] === null,
    'M74A-HTTP-040 closed shift cannot leak as active server state',
);

[$logout] = $sendHttp('POST', '/technical-preview/logout');
$assert($logout->getStatusCode() === 302, 'M74A-HTTP-041 logout redirects');

[$postLogoutPos] = $sendHttp('GET', '/technical-preview/pos');
$assert($postLogoutPos->getStatusCode() === 302, 'M74A-HTTP-042 post-logout POS access is denied');
$assert(str_ends_with((string) $postLogoutPos->headers->get('Location'), '/technical-preview'), 'M74A-HTTP-043 post-logout returns to sign-in');

echo "M7.4A Technical Preview interaction regression passed.\n";
