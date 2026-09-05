<?php

declare(strict_types=1);

use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Session\EncryptedStore;
use Illuminate\Session\FileSessionHandler;
use Symfony\Component\HttpFoundation\Cookie;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        fwrite(STDERR, "Technical Preview file-session continuity regression failed: {$case}\n");
        exit(1);
    }
};

$sessionDirectory = __DIR__.'/../storage/framework/sessions';
if (! is_dir($sessionDirectory)) {
    mkdir($sessionDirectory, 0770, true);
}

$baselineSessionFiles = [];
foreach (glob($sessionDirectory.'/*') ?: [] as $path) {
    if (is_file($path)) {
        $baselineSessionFiles[$path] = true;
    }
}

register_shutdown_function(static function () use ($sessionDirectory, $baselineSessionFiles): void {
    foreach (glob($sessionDirectory.'/*') ?: [] as $path) {
        if (is_file($path) && ! isset($baselineSessionFiles[$path])) {
            @unlink($path);
        }
    }
});

$assert(
    TechnicalPreviewRuntimePolicy::permits(
        enabled: true,
        runtimeClass: 'preview',
        sessionDriver: 'file',
        sessionLifetimeMinutes: 60,
        sessionEncrypted: true,
        sessionSecure: true,
        sessionHttpOnly: true,
        sessionSameSite: 'lax',
        sessionDomain: null,
        sessionPath: '/',
        sessionCookie: 'oneqay-preview-session',
    ),
    'TPFS-RUNTIME-001 exact deployed Preview envelope is admitted by the fail-closed policy',
);
$assert(
    ! TechnicalPreviewRuntimePolicy::permits(
        enabled: true,
        runtimeClass: 'production',
        sessionDriver: 'file',
        sessionLifetimeMinutes: 60,
        sessionEncrypted: true,
        sessionSecure: true,
        sessionHttpOnly: true,
        sessionSameSite: 'lax',
        sessionDomain: null,
        sessionPath: '/',
        sessionCookie: 'oneqay-preview-session',
    ),
    'TPFS-RUNTIME-002 Production remains denied even with a Preview-shaped session envelope',
);

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var HttpKernel $kernel */
$kernel = $app->make(HttpKernel::class);

/** @var array<string, string> $cookies */
$cookies = [];
$csrfToken = null;
$lastSessionCookie = null;

$sendHttp = static function (
    string $method,
    string $uri,
    array $parameters = [],
    array $server = [],
) use ($kernel, &$cookies, &$csrfToken, &$lastSessionCookie): array {
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
            'HTTP_HOST' => 'preview.oneqay.test',
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        ], $server),
    );

    $response = $kernel->handle($request);

    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getName() === 'oneqay-preview-session') {
            $lastSessionCookie = $cookie;
        }

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

[$indexResponse, $indexRequest] = $sendHttp('GET', '/technical-preview');
$assert($indexResponse->getStatusCode() === 200, 'TPFS-HTTP-001 explicit deployed Preview sign-in surface is reachable');
$assert($indexRequest->hasSession(), 'TPFS-SESSION-001 web middleware attaches a server session');
$assert($indexRequest->session() instanceof EncryptedStore, 'TPFS-SESSION-002 deployed Preview uses encrypted server session store');
$assert($indexRequest->session()->getHandler() instanceof FileSessionHandler, 'TPFS-SESSION-003 deployed Preview resolves the file session handler');

$assert((string) config('technical-preview.runtime_class') === 'preview', 'TPFS-CONFIG-001 selected Preview runtime is config-backed');
$assert((bool) config('technical-preview.enabled') === true, 'TPFS-CONFIG-002 explicit Preview enablement is true only for this qualification');
$assert((string) config('session.driver') === 'file', 'TPFS-CONFIG-003 shared session runtime is file-backed after policy admission');
$assert((int) config('session.lifetime') === 60, 'TPFS-CONFIG-004 deployed Preview session lifetime is exactly 60 minutes');
$assert((bool) config('session.encrypt') === true, 'TPFS-CONFIG-005 deployed Preview session payload encryption is enabled');
$assert((bool) config('session.secure') === true, 'TPFS-CONFIG-006 deployed Preview cookie requires Secure transport');
$assert((bool) config('session.http_only') === true, 'TPFS-CONFIG-007 deployed Preview cookie is HttpOnly');
$assert(strtolower((string) config('session.same_site')) === 'lax', 'TPFS-CONFIG-008 deployed Preview cookie is SameSite=Lax');
$assert(config('session.domain') === null, 'TPFS-CONFIG-009 deployed Preview cookie remains host-only');
$assert((string) config('session.path') === '/', 'TPFS-CONFIG-010 deployed Preview cookie path remains root');
$assert((string) config('session.cookie') === 'oneqay-preview-session', 'TPFS-CONFIG-011 deployed Preview uses a dedicated cookie namespace');

$assert($lastSessionCookie instanceof Cookie, 'TPFS-COOKIE-001 dedicated Preview session cookie is emitted');
$assert($lastSessionCookie->isSecure(), 'TPFS-COOKIE-002 emitted Preview session cookie is Secure');
$assert($lastSessionCookie->isHttpOnly(), 'TPFS-COOKIE-003 emitted Preview session cookie is HttpOnly');
$assert(strtolower((string) $lastSessionCookie->getSameSite()) === 'lax', 'TPFS-COOKIE-004 emitted Preview session cookie is SameSite=Lax');
$assert($lastSessionCookie->getDomain() === null, 'TPFS-COOKIE-005 emitted Preview session cookie is host-only');
$assert($lastSessionCookie->getPath() === '/', 'TPFS-COOKIE-006 emitted Preview session cookie uses root path');

$initialSessionId = $indexRequest->session()->getId();
$assert($initialSessionId !== '', 'TPFS-SESSION-004 initial server session has an identifier');
$assert(is_string($csrfToken) && $csrfToken !== '', 'TPFS-CSRF-001 initial Preview session owns a CSRF token');

[$signInResponse, $signInRequest] = $sendHttp('POST', '/technical-preview/sign-in', [
    'principal' => 'synthetic-principal-a',
]);
$assert($signInResponse->getStatusCode() === 302, 'TPFS-HTTP-002 allowlisted Preview sign-in redirects');
$assert(str_ends_with((string) $signInResponse->headers->get('Location'), '/technical-preview/context'), 'TPFS-HTTP-003 sign-in advances to context');

$signedInSessionId = $signInRequest->session()->getId();
$assert($signedInSessionId !== '', 'TPFS-SESSION-005 signed-in Preview session has an identifier');
$assert(! hash_equals($initialSessionId, $signedInSessionId), 'TPFS-SESSION-006 sign-in regenerates the server session identifier');
$assert(isset($cookies['oneqay-preview-session']) && $cookies['oneqay-preview-session'] !== '', 'TPFS-COOKIE-007 dedicated Preview cookie survives sign-in rotation');

$signedInSessionPath = $sessionDirectory.'/'.$signedInSessionId;
$assert(is_file($signedInSessionPath), 'TPFS-FILE-001 regenerated signed-in session is persisted to the file backend');
$sessionPayload = file_get_contents($signedInSessionPath);
$assert(is_string($sessionPayload) && $sessionPayload !== '', 'TPFS-FILE-002 persisted file-session payload is non-empty');
$assert(! str_contains($sessionPayload, 'synthetic-principal-a'), 'TPFS-FILE-003 persisted file session does not expose principal plaintext');
$assert(! str_contains($sessionPayload, 'tenant-alpha'), 'TPFS-FILE-004 persisted file session does not expose tenant plaintext');
$assert(! str_contains($sessionPayload, 'oneqay.preview.principal'), 'TPFS-FILE-005 persisted file session does not expose Preview authority key plaintext');

[$contextResponse, $contextRequest] = $sendHttp('GET', '/technical-preview/context');
$assert($contextResponse->getStatusCode() === 200, 'TPFS-HTTP-004 a separate HTTP request resumes the signed-in Preview session');
$assert(hash_equals($signedInSessionId, $contextRequest->session()->getId()), 'TPFS-SESSION-007 separate request resumes the same file-backed session identifier');
$assert(str_contains((string) $contextResponse->getContent(), 'tenant-alpha'), 'TPFS-SESSION-008 resumed session derives the server-owned Alpha context');

[$badCsrfResponse] = $sendHttp('POST', '/technical-preview/context', [
    '_token' => str_repeat('0', 40),
    'selection' => 'primary',
]);
$assert($badCsrfResponse->getStatusCode() === 419, 'TPFS-CSRF-002 forged CSRF token is rejected on a Preview mutation');

[$selectContextResponse, $selectContextRequest] = $sendHttp('POST', '/technical-preview/context', [
    'selection' => 'primary',
    'tenant_id' => 'tenant-beta',
    'organization_id' => 'organization-beta',
    'outlet_id' => 'outlet-beta',
    'device_id' => 'device-beta',
]);
$assert($selectContextResponse->getStatusCode() === 302, 'TPFS-HTTP-005 current CSRF token permits verified context selection');
$assert(str_ends_with((string) $selectContextResponse->headers->get('Location'), '/technical-preview/pos'), 'TPFS-HTTP-006 verified context advances to POS');
$assert(hash_equals($signedInSessionId, $selectContextRequest->session()->getId()), 'TPFS-SESSION-009 context mutation remains on the persisted signed-in session');

[$posResponse, $posRequest] = $sendHttp('GET', '/technical-preview/pos');
$assert($posResponse->getStatusCode() === 200, 'TPFS-HTTP-007 POS is reachable from another request using file-session continuity');
$assert(hash_equals($signedInSessionId, $posRequest->session()->getId()), 'TPFS-SESSION-010 POS request resumes the same file-backed authority');
$assert(str_contains((string) $posResponse->getContent(), 'Synthetic Alpha Product'), 'TPFS-ISO-001 forged Beta fields did not replace the server-verified Alpha scope');
$assert(! str_contains((string) $posResponse->getContent(), 'Synthetic Beta Product'), 'TPFS-ISO-002 foreign Beta catalog remains undisclosed');

[$openShiftResponse] = $sendHttp('POST', '/technical-preview/shift/open', [
    'opening_cash_atomic' => 1000,
]);
$assert($openShiftResponse->getStatusCode() === 302, 'TPFS-HTTP-008 dedicated cash-control route is registered in exact deployed Preview runtime');
$assert(str_ends_with((string) $openShiftResponse->headers->get('Location'), '/technical-preview/pos'), 'TPFS-HTTP-009 shift opening returns to POS');
$assert(is_file($signedInSessionPath), 'TPFS-FILE-006 file-backed authority persists across the multi-request journey');

$preLogoutCookies = $cookies;
[$logoutResponse] = $sendHttp('POST', '/technical-preview/logout');
$assert($logoutResponse->getStatusCode() === 302, 'TPFS-HTTP-010 logout redirects');
$assert(! is_file($signedInSessionPath), 'TPFS-SESSION-011 logout invalidates and removes the prior signed-in file session');

[$postLogoutPos] = $sendHttp('GET', '/technical-preview/pos');
$assert($postLogoutPos->getStatusCode() === 302, 'TPFS-SESSION-012 current post-logout cookie has no Preview POS authority');
$assert(str_ends_with((string) $postLogoutPos->headers->get('Location'), '/technical-preview'), 'TPFS-SESSION-013 post-logout access returns to Preview sign-in');

$cookies = $preLogoutCookies;
$csrfToken = null;
[$staleCookiePos] = $sendHttp('GET', '/technical-preview/pos');
$assert($staleCookiePos->getStatusCode() === 302, 'TPFS-SESSION-014 stale pre-logout cookie cannot revive burned Preview authority');
$assert(str_ends_with((string) $staleCookiePos->headers->get('Location'), '/technical-preview'), 'TPFS-SESSION-015 stale cookie fails closed to Preview sign-in');

echo "Technical Preview file-session continuity regression passed.\n";
