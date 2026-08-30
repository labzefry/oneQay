<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Delivery\Http\Middleware;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\TenantContextStore;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequirePosSessionContextMiddleware
{
    public function __construct(
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless(
            in_array($runtime, ['local', 'test', 'ci'], true)
            && (bool) config('database.oneqay_persistence_enabled', false)
            && (bool) config('oneqay.session_control.enabled', false)
            && (bool) config('oneqay.pos_sale_completion.enabled', false),
            404,
        );

        try {
            $identity = $this->requiredSessionString($request, FirstPartySessionKeys::IDENTITY);
            $tenant = $this->requiredSessionString($request, FirstPartySessionKeys::TENANT);
            $organization = $this->requiredSessionString($request, FirstPartySessionKeys::ORGANIZATION);
            $outlet = $this->requiredSessionString($request, FirstPartySessionKeys::OUTLET);
            $device = $this->requiredSessionString($request, FirstPartySessionKeys::DEVICE);

            $identityId = PlatformIdentityId::fromString($identity);
            $tenantId = TenantId::fromString($tenant);
            $verifiedIdentity = new ServerVerifiedPlatformIdentity($identityId);
            $verifiedTenant = new ServerVerifiedTenantContext($tenantId);
            $this->tenantContexts->setVerified($verifiedTenant);

            $this->enterOrganizationalContext->enter(
                $verifiedIdentity,
                $verifiedTenant,
                $organization,
                $outlet,
                $device,
            );
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            $this->clearContexts();
            abort(403, 'POS context denied.');
        }

        try {
            return $next($request);
        } finally {
            $this->clearContexts();
        }
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('POS session context is invalid.');
        }

        return trim($value);
    }

    private function clearContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
