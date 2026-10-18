<?php
// Author by Lab | zefry

declare(strict_types=1);

namespace App\Delivery\Http\Errors;

use App\Application\Observability\CorrelationId;
use App\Infrastructure\Platform\LocalePreference;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Presentation only. Authentication, permissions and machine API status remain authoritative. */
final class BrowserErrorPresentation
{
    public static function navigation(Request $request): bool
    {
        return !$request->is('api/*') && !$request->expectsJson()
            && ($request->headers->has('X-Inertia') || str_contains((string) $request->header('Accept'), 'text/html'));
    }

    public function present(Request $request, Response $response): Response
    {
        $status = $response->getStatusCode();
        $retryAfter = $response->headers->get('Retry-After');
        if ($status < 400 || $response->headers->has('X-OneQay-Error-Code')) return $response;
        $correlation = (string) $request->attributes->get('oneqay.correlation_id', '');
        if ($correlation === '') {
            $correlation = CorrelationId::resolve($request->header('X-Correlation-ID'));
            $request->attributes->set('oneqay.correlation_id', $correlation);
        }
        $code = 'HTTP_'.$status;
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $candidate = is_array($data) ? ($data['error']['code'] ?? null) : null;
            if (is_string($candidate) && preg_match('/\A[A-Z][A-Z0-9_]{0,55}\z/D', $candidate)) $code = $candidate;
        }
        if (!self::navigation($request)) {
            // Framework exception documents must not expose exception details to API clients.
            if (!$response instanceof \Illuminate\Http\JsonResponse) {
                $response = response()->json(['error' => ['code' => $code, 'message' => 'The request could not be completed.', 'correlation_id' => $correlation]], $status);
            }
            $response->headers->set('X-Correlation-ID', $correlation);
            $response->headers->set('Cache-Control', 'no-store');
            return $response;
        }
        $code = 'PAGE_'.$code;
        $login = $request->is('platform', 'platform/*', 'owner-login') ? '/owner-login' : '/business-login';
        $expired = $status === 419 || ($status === 401 && ($request->isMethod('GET') || $code === 'PAGE_SESSION_AUTHORITY_DENIED'));
        if ($expired) {
            $url = $login.'?reason=session-expired';
            $response = $request->headers->has('X-Inertia')
                ? response('', 409, ['X-Inertia-Location' => $url])
                : redirect($url, 303);
        } else {
            $locale = $request->cookie('oneqay_locale');
            if (!in_array($locale, ['id', 'en'], true)) $locale = LocalePreference::initial($request);
            $response = response()->view('errors.friendly', compact('status', 'code', 'correlation', 'locale', 'login'), $status);
            // Inertia must leave its page renderer for standalone error HTML.
            if ($request->headers->has('X-Inertia')) {
                $response->headers->set('X-Inertia-Error', 'true');
            }
        }
        if ($retryAfter !== null) $response->headers->set('Retry-After', $retryAfter);
        $response->headers->set('X-OneQay-Error-Code', $code);
        $response->headers->set('X-OneQay-Original-Status', (string) $status);
        $response->headers->set('X-Correlation-ID', $correlation);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->setVary(['Accept', 'X-Inertia'], false);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
        return $response;
    }
}
