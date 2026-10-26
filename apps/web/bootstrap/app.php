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
            'session.active' => EnforceActiveFirstPartySessionAuthorityMiddleware::class,
            'session.control-mutation' => RequireFirstPartySessionControlMutationContextMiddleware::class,
        ]);
        $middleware->append(CorrelationIdMiddleware::class);
        $middleware->append(SafeRequestObservationMiddleware::class);
        $middleware->append(\App\Delivery\Http\Middleware\ControlPlaneObservationMiddleware::class);
        $middleware->append(SecurityHeadersMiddleware::class);
        $middleware->append(\App\Delivery\Http\Middleware\PresentBrowserErrorsMiddleware::class);
        $middleware->web(append: [
            \App\Delivery\Http\Middleware\BusinessHostMiddleware::class,
            \App\Delivery\Http\Middleware\BusinessWorkflowMiddleware::class,
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Use the same safe presentation for framework exceptions and controller rejections.
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            return app(\App\Delivery\Http\Errors\BrowserErrorPresentation::class)->present(request(), $response);
        });
    })
    ->create();
