<?php
// Author by Lab | zefry

declare(strict_types=1);
namespace App\Delivery\Http\Middleware;
use App\Delivery\Http\Errors\BrowserErrorPresentation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
final class PresentBrowserErrorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return app(BrowserErrorPresentation::class)->present($request, $next($request));
    }
}
