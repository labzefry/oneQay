<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Author by Lab | zefry
final class SafeRequestObservationMiddleware
{
    private const SAFE_TOKEN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{0,127}\z/';
    private const RETENTION_DAYS = 14;
    private const LOG_LEVEL = 'info';

    public function __construct(private readonly ?string $logPathOverride = null)
    {
    }

    /** @return array{path:string,level:string,days:int} */
    public static function policy(): array
    {
        return [
            'path' => storage_path('logs/oneqay-observation.log'),
            'level' => self::LOG_LEVEL,
            'days' => self::RETENTION_DAYS,
        ];
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (config('oneqay.runtime_class') !== 'preview') {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

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
        $policy = self::policy();
        $logPath = $this->logPathOverride ?? $policy['path'];

        if ($logPath === '' || str_starts_with($logPath, public_path())) {
            return;
        }

        $correlationId = $request->attributes->get('oneqay.correlation_id');
        $route = $request->route();
        $routeName = is_object($route) && method_exists($route, 'getName') ? $route->getName() : null;
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

        $context = [
            'event' => 'http.request',
            'correlation_id' => self::safeToken(is_string($correlationId) ? $correlationId : '', 'unavailable'),
            'method' => self::safeMethod($request->getMethod()),
            'route' => self::safeToken(is_string($routeName) ? $routeName : '', 'unnamed'),
            'status' => max(100, min(599, $status)),
            'duration_ms' => max(0.0, round($durationMs, 3)),
            'exception_class' => self::safeExceptionClass($exceptionClass),
        ];

        try {
            Log::build([
                'driver' => 'daily',
                'path' => $logPath,
                'level' => $policy['level'],
                'days' => $policy['days'],
                'replace_placeholders' => true,
            ])->info('oneqay.http.request', $context);
        } catch (Throwable) {
            // Observability failure must not change request semantics.
        }
    }

    private static function safeToken(string $value, string $fallback): string
    {
        return preg_match(self::SAFE_TOKEN, $value) === 1 ? $value : $fallback;
    }

    private static function safeMethod(string $method): string
    {
        $normalized = strtoupper($method);

        return preg_match('/\A[A-Z]{3,16}\z/', $normalized) === 1 ? $normalized : 'UNKNOWN';
    }

    private static function safeExceptionClass(?string $exceptionClass): ?string
    {
        if ($exceptionClass === null) {
            return null;
        }

        return preg_match('/\A[A-Za-z_][A-Za-z0-9_\\\\]{0,190}\z/', $exceptionClass) === 1
            ? $exceptionClass
            : 'Throwable';
    }
}
