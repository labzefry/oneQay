<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Observability\SafeLogContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Author by Lab | zefry
final class SafeRequestObservationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);

        try {
            /** @var Response $response */
            $response = $next($request);
            $this->record($request, $response->getStatusCode(), $startedAt);

            return $response;
        } catch (Throwable $exception) {
            $this->record($request, 500, $startedAt, $exception::class);

            throw $exception;
        }
    }

    private function record(Request $request, int $status, int $startedAt, ?string $exceptionClass = null): void
    {
        $correlationId = $request->attributes->get('oneqay.correlation_id');
        $route = $request->route();
        $routeName = is_object($route) && method_exists($route, 'getName') ? $route->getName() : null;
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

        $context = SafeLogContext::httpRequest(
            is_string($correlationId) ? $correlationId : 'unavailable',
            $request->getMethod(),
            is_string($routeName) ? $routeName : null,
            $status,
            $durationMs,
            $exceptionClass,
        );

        try {
            Log::channel('oneqay_observation')->info('oneqay.http.request', $context);
        } catch (Throwable) {
            // Observability failure must not change request semantics.
        }
    }
}
