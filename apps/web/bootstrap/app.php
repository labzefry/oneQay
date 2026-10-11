<?php

use App\Delivery\Http\Middleware\CorrelationIdMiddleware;
use App\Delivery\Http\Middleware\EnforceActiveFirstPartySessionAuthorityMiddleware;
use App\Delivery\Http\Middleware\HandleInertiaRequests;
use App\Delivery\Http\Middleware\RequireFirstPartySessionControlMutationContextMiddleware;
use App\Delivery\Http\Middleware\RequireVerifiedTenantContextMiddleware;
use App\Delivery\Http\Middleware\SafeRequestObservationMiddleware;
use App\Delivery\Http\Middleware\SecurityHeadersMiddleware;
use App\Providers\FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider;
use App\Providers\FinalShiftCloseRuntimeDbBindingAttestationServiceProvider;
use App\Providers\FinalShiftCloseServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        FinalShiftCloseServiceProvider::class,
        FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider::class,
        FinalShiftCloseRuntimeDbBindingAttestationServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.verified' => RequireVerifiedTenantContextMiddleware::class,
            'session.control-mutation' => RequireFirstPartySessionControlMutationContextMiddleware::class,
        ]);
        // Author by Lab | zefry. Provider-registered POS routes also need the
        // complete web pipeline before evaluating or invalidating a session.
        // Laravel removes duplicates when a route already belongs to web.
        $middleware->group('session.active', [
            'web',
            EnforceActiveFirstPartySessionAuthorityMiddleware::class,
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
