<?php

declare(strict_types=1);

namespace App\Application\Observability;

// Author by Lab | zefry
final class SafeLogContext
{
    private const SAFE_TOKEN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{0,127}\z/';

    /** @return array{event:string,correlation_id:string,method:string,route:string,status:int,duration_ms:float,exception_class:?string} */
    public static function httpRequest(
        string $correlationId,
        string $method,
        ?string $routeName,
        int $status,
        float $durationMs,
        ?string $exceptionClass = null,
    ): array {
        return [
            'event' => 'http.request',
            'correlation_id' => self::safeToken($correlationId, 'unavailable'),
            'method' => self::safeMethod($method),
            'route' => self::safeToken($routeName ?? '', 'unnamed'),
            'status' => max(100, min(599, $status)),
            'duration_ms' => max(0.0, round($durationMs, 3)),
            'exception_class' => self::safeExceptionClass($exceptionClass),
        ];
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

    private function __construct()
    {
    }
}
