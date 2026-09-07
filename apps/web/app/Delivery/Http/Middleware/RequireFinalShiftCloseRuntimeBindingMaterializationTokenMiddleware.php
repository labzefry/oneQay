<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware
{
    public function __construct(private readonly string $expectedToken) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (strlen($this->expectedToken) < 32
            || strlen($this->expectedToken) > 512
            || preg_match('/\A[A-Za-z0-9._~+=\/-]{32,512}\z/D', $this->expectedToken) !== 1
        ) {
            abort(503);
        }

        $authorization = $request->headers->get('Authorization');
        if (! is_string($authorization)
            || preg_match('/\ABearer ([A-Za-z0-9._~+=\/-]{32,512})\z/D', $authorization, $matches) !== 1
            || ! hash_equals($this->expectedToken, $matches[1])
        ) {
            abort(401);
        }

        return $next($request);
    }
}
