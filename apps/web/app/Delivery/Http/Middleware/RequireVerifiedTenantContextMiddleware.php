<?php

namespace App\Delivery\Http\Middleware;

use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\TenantContextStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireVerifiedTenantContextMiddleware
{
    public function __construct(
        private readonly TenantContextStore $contexts,
        private readonly RequireVerifiedTenantContext $requireContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->requireContext->require($this->contexts->current());

            return $next($request);
        } finally {
            $this->contexts->clear();
        }
    }
}
