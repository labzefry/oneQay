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
        if ($this->expectedToken === '') {
            abort(503);
        }

        $authorization = $request->headers->get('Authorization');
        if (! is_string($authorization)
            || preg_match('/\ABearer ([^\s]+)\z/D', $authorization, $matches) !== 1
            || ! hash_equals($this->expectedToken, $matches[1])
        ) {
            abort(401);
        }

        return $next($request);
    }
}
