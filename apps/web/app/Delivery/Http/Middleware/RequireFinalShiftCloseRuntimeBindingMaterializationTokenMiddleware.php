<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware
{
    public function __construct(private readonly string $expectedToken) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($this->expectedToken)) {
            return $this->rejectionResponse(503);
        }

        $providedToken = FinalShiftCloseRuntimeControlPlaneTokenPolicy::parseBearerCredential(
            $request->headers->get('Authorization'),
        );

        if ($providedToken === null || ! hash_equals($this->expectedToken, $providedToken)) {
            return $this->rejectionResponse(401);
        }

        return $next($request);
    }

    private function rejectionResponse(int $status): Response
    {
        return response('', $status, [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }
}
