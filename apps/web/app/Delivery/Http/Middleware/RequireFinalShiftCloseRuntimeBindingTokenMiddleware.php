<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequireFinalShiftCloseRuntimeBindingTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('oneqay.final_shift_close_runtime_db_binding_attestation.token', '');
        $authorization = $request->headers->get('Authorization');

        if (! is_string($configured)
            || strlen($configured) < 32
            || strlen($configured) > 512
            || ! is_string($authorization)
            || preg_match('/\ABearer ([A-Za-z0-9._~+\/-=]{32,512})\z/D', $authorization, $matches) !== 1
            || ! hash_equals($configured, $matches[1])
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
