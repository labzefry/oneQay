<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequireFinalShiftCloseRuntimeBindingTokenMiddleware
{
    public function __construct(private readonly string $expectedToken) {}

    public function handle(Request $request, Closure $next): Response
    {
        $providedToken = FinalShiftCloseRuntimeControlPlaneTokenPolicy::parseBearerCredential(
            $request->headers->get('Authorization'),
        );

        if (! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($this->expectedToken)
            || $providedToken === null
            || ! hash_equals($this->expectedToken, $providedToken)
        ) {
            return response('', 404, [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return $next($request);
    }
}
