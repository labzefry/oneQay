<?php

use App\Delivery\Http\Middleware\CorrelationIdMiddleware;
use App\Delivery\Http\Middleware\HandleInertiaRequests;
use App\Delivery\Http\Middleware\RequireVerifiedTenantContextMiddleware;
use App\Delivery\Http\Middleware\SafeRequestObservationMiddleware;
use App\Delivery\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.verified' => RequireVerifiedTenantContextMiddleware::class,
        ]);
        $middleware->append(CorrelationIdMiddleware::class);
        $middleware->append(SafeRequestObservationMiddleware::class);
        $middleware->append(SecurityHeadersMiddleware::class);
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Exception detail remains framework-internal; safe request logging records class only.
    })
    ->create();
