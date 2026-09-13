<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ThrottleRequestsException $exception) {
            return response('', $exception->getStatusCode(), array_merge(
                $exception->getHeaders(),
                [
                    'Cache-Control' => 'no-store, private',
                    'Pragma' => 'no-cache',
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Robots-Tag' => 'noindex, nofollow, noarchive',
                ],
            ));
        }
    }
}
