<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request as HttpRequest;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        fwrite(STDERR, "Technical Preview session failure containment regression failed: {$case}\n");
        exit(1);
    }
};

$sessionDirectory = __DIR__.'/../storage/framework/sessions';
if (! is_dir($sessionDirectory)) {
    mkdir($sessionDirectory, 0700, true);
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

/** @var array<string, string> $cookies */
$cookies = [];
$csrfToken = null;

$sendHttp = static function (
    string $method,
    string $uri,
    array $parameters = [],
) use (&$cookies, &$csrfToken): array {
    /** @var Illuminate\Foundation\Application $app */
    $app = require __DIR__.'/../bootstrap/app.php';
    /** @var HttpKernel $kernel */
    $kernel = $app->make(HttpKernel::class);

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
        [
            'HTTP_HOST' => 'preview.oneqay.test',
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        ],
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

$resetBrowser = static function () use (&$cookies, &$csrfToken): void {
    $cookies = [];
    $csrfToken = null;
};

$signIn = static function () use ($sendHttp, $assert, $sessionDirectory): array {
    [$indexResponse] = $sendHttp('GET', '/technical-preview');
    $assert($indexResponse->getStatusCode() === 200, 'TPSF-HTTP-001 Preview sign-in surface is reachable');

    [$signInResponse, $signInRequest] = $sendHttp('POST', '/technical-preview/sign-in', [
        'principal' => 'synthetic-principal-a',
    ]);
    $assert($signInResponse->getStatusCode() === 302, 'TPSF-HTTP-002 allowlisted synthetic principal signs in');
    $assert(str_ends_with((string) $signInResponse->headers->get('Location'), '/technical-preview/context'), 'TPSF-HTTP-003 sign-in advances to context');

    $sessionId = $signInRequest->session()->getId();
    $assert($sessionId !== '', 'TPSF-SESSION-001 signed-in session has a server identifier');
    $sessionPath = $sessionDirectory.'/'.$sessionId;
    $assert(is_file($sessionPath), 'TPSF-SESSION-002 signed-in session is persisted by the file backend');

    return [$sessionId, $sessionPath];
};

// Bootstrap one independent request before inspecting the config repository.
[$configProbeResponse] = $sendHttp('GET', '/technical-preview');
$assert($configProbeResponse->getStatusCode() === 200, 'TPSF-CONFIG-000 deployed Preview request bootstraps successfully');
$assert((string) config('technical-preview.runtime_class') === 'preview', 'TPSF-CONFIG-001 exact deployed Preview runtime is selected');
$assert((bool) config('technical-preview.enabled') === true, 'TPSF-CONFIG-002 Preview is explicitly enabled only inside this qualification');
$assert((string) config('session.driver') === 'file', 'TPSF-CONFIG-003 admitted Preview runtime uses file sessions');
$assert((bool) config('session.encrypt') === true, 'TPSF-CONFIG-004 admitted Preview session payload is encrypted');
$resetBrowser();

// Missing backend state must never preserve previously authenticated Preview authority.
[, $missingSessionPath] = $signIn();
$assert(@unlink($missingSessionPath), 'TPSF-MISSING-001 qualification can remove the signed-in backend session state');
[$missingResponse] = $sendHttp('GET', '/technical-preview/pos');
$assert($missingResponse->getStatusCode() === 302, 'TPSF-MISSING-002 missing backend state fails closed instead of serving Preview POS');
$assert(str_ends_with((string) $missingResponse->headers->get('Location'), '/technical-preview'), 'TPSF-MISSING-003 missing backend state returns to Preview sign-in');

// Corrupt encrypted backend state must deserialize to an empty authority boundary.
$resetBrowser();
[, $corruptSessionPath] = $signIn();
$assert(file_put_contents($corruptSessionPath, 'not-a-valid-oneqay-encrypted-session-payload') !== false, 'TPSF-CORRUPT-001 qualification can replace session payload with corrupt ciphertext');
[$corruptResponse] = $sendHttp('GET', '/technical-preview/pos');
$assert($corruptResponse->getStatusCode() === 302, 'TPSF-CORRUPT-002 corrupt encrypted state fails closed instead of serving Preview POS');
$assert(str_ends_with((string) $corruptResponse->headers->get('Location'), '/technical-preview'), 'TPSF-CORRUPT-003 corrupt encrypted state returns to Preview sign-in');

// Expired backend state must be ignored by the 60-minute file-session lifetime boundary.
$resetBrowser();
[, $expiredSessionPath] = $signIn();
$assert(@touch($expiredSessionPath, time() - 3700), 'TPSF-EXPIRED-001 qualification can age the session beyond the selected lifetime');
clearstatcache(true, $expiredSessionPath);
[$expiredResponse] = $sendHttp('GET', '/technical-preview/pos');
$assert($expiredResponse->getStatusCode() === 302, 'TPSF-EXPIRED-002 expired backend state fails closed instead of serving Preview POS');
$assert(str_ends_with((string) $expiredResponse->headers->get('Location'), '/technical-preview'), 'TPSF-EXPIRED-003 expired backend state returns to Preview sign-in');

echo "Technical Preview session failure containment regression passed.\n";
