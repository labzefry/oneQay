<?php

namespace App\Delivery\Http\Middleware;

use App\Application\Observability\CorrelationId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CorrelationIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = CorrelationId::resolve($request->headers->get('X-Correlation-ID'));
        $request->attributes->set('oneqay.correlation_id', $correlationId);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
