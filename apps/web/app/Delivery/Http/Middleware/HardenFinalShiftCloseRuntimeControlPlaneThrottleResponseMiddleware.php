<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware
{
    private const MATERIALIZATION_ROUTE_NAME = 'internal.final-shift-close.runtime-binding-manifest.materialize';

    private const MATERIALIZATION_PATH = 'internal/final-shift-close/runtime-binding-manifest/materialize';

    private const DB_ATTESTATION_ROUTE_NAME = 'internal.final-shift-close.runtime-db-binding-attestation';

    private const DB_ATTESTATION_PATH = 'internal/final-shift-close/runtime-db-binding-attestation';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isOwnedControlPlaneRequest($request)
            || $response->getStatusCode() !== 429
            || ! $response->headers->has('Retry-After')
            || ! $response->headers->has('X-RateLimit-Limit')
        ) {
            return $response;
        }

        $response->setContent('');
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }

    private function isOwnedControlPlaneRequest(Request $request): bool
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return false;
        }

        $routeName = $route->getName();

        return ($routeName === self::MATERIALIZATION_ROUTE_NAME
                && $request->isMethod('POST')
                && $request->is(self::MATERIALIZATION_PATH))
            || ($routeName === self::DB_ATTESTATION_ROUTE_NAME
                && ($request->isMethod('GET') || $request->isMethod('HEAD'))
                && $request->is(self::DB_ATTESTATION_PATH));
    }
}
