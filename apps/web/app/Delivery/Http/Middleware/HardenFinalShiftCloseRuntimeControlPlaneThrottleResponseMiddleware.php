<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware
{
    private const MATERIALIZATION_ROUTE_NAME = 'internal.final-shift-close.runtime-binding-manifest.materialize';

    private const MATERIALIZATION_PATH = 'internal/final-shift-close/runtime-binding-manifest/materialize';

    private const MATERIALIZATION_THROTTLE_LIMIT = '1';

    private const DB_ATTESTATION_ROUTE_NAME = 'internal.final-shift-close.runtime-db-binding-attestation';

    private const DB_ATTESTATION_PATH = 'internal/final-shift-close/runtime-db-binding-attestation';

    private const DB_ATTESTATION_THROTTLE_LIMIT = '2';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $expectedThrottleLimit = $this->canonicalThrottleLimit($request);

        if ($expectedThrottleLimit === null
            || $response->getStatusCode() !== 429
            || ! $this->hasCanonicalThrottleRejectionMetadata($response, $expectedThrottleLimit)
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

    private function hasCanonicalThrottleRejectionMetadata(Response $response, string $expectedThrottleLimit): bool
    {
        if (! $response->headers->has('Retry-After')
            || ! $response->headers->has('X-RateLimit-Limit')
            || ! $response->headers->has('X-RateLimit-Remaining')
            || ! $response->headers->has('X-RateLimit-Reset')
            || $response->headers->get('X-RateLimit-Limit') !== $expectedThrottleLimit
            || $response->headers->get('X-RateLimit-Remaining') !== '0'
        ) {
            return false;
        }

        $retryAfter = $response->headers->get('Retry-After');
        $reset = $response->headers->get('X-RateLimit-Reset');

        return is_string($retryAfter)
            && $retryAfter !== ''
            && ctype_digit($retryAfter)
            && is_string($reset)
            && $reset !== ''
            && ctype_digit($reset);
    }

    private function canonicalThrottleLimit(Request $request): ?string
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return null;
        }

        $routeName = $route->getName();
        $routeActionName = $route->getActionName();

        if ($routeName === self::MATERIALIZATION_ROUTE_NAME
            && $routeActionName === FinalShiftCloseRuntimeBindingManifestMaterializationController::class.'@__invoke'
            && $request->isMethod('POST')
            && $request->is(self::MATERIALIZATION_PATH)
        ) {
            return self::MATERIALIZATION_THROTTLE_LIMIT;
        }

        if ($routeName === self::DB_ATTESTATION_ROUTE_NAME
            && $routeActionName === FinalShiftCloseRuntimeDbBindingAttestationController::class.'@__invoke'
            && ($request->isMethod('GET') || $request->isMethod('HEAD'))
            && $request->is(self::DB_ATTESTATION_PATH)
        ) {
            return self::DB_ATTESTATION_THROTTLE_LIMIT;
        }

        return null;
    }
}
