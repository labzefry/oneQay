<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\TenantContextStore;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class RequirePolicyAdministrationSessionContextMiddleware
{
    public const IDENTITY_SESSION = 'oneqay.auth.identity_id';
    public const TENANT_SESSION = 'oneqay.auth.tenant_id';
    public const ORGANIZATION_SESSION = 'oneqay.auth.organization_id';
    public const OUTLET_SESSION = 'oneqay.auth.outlet_id';
    public const DEVICE_SESSION = 'oneqay.auth.device_id';

    public function __construct(
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless(in_array($runtime, ['local', 'test', 'ci'], true), 404);

        try {
            $identityValue = $this->requiredSessionString($request, self::IDENTITY_SESSION);
            $tenantValue = $this->requiredSessionString($request, self::TENANT_SESSION);
            $organizationValue = $this->requiredSessionString($request, self::ORGANIZATION_SESSION);
            $outletValue = $this->optionalSessionString($request, self::OUTLET_SESSION);
            $deviceValue = $this->optionalSessionString($request, self::DEVICE_SESSION);

            $identity = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString($identityValue));
            $tenant = new ServerVerifiedTenantContext(TenantId::fromString($tenantValue));
            $this->tenantContexts->setVerified($tenant);

            $this->enterOrganizationalContext->enter(
                $identity,
                $tenant,
                $organizationValue,
                $outletValue,
                $deviceValue,
            );
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation) {
            $this->clearContexts();
            abort(403, 'Policy administration context denied.');
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
            throw new InvalidArgumentException('Policy administration session context is invalid.');
        }
        return trim($value);
    }

    private function optionalSessionString(Request $request, string $key): ?string
    {
        $value = $request->session()->get($key);
        if ($value === null) {
            return null;
        }
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Policy administration session context is invalid.');
        }
        return trim($value);
    }

    private function clearContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
