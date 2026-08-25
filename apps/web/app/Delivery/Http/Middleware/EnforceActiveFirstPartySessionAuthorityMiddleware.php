<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Author by Lab | zefry
final class EnforceActiveFirstPartySessionAuthorityMiddleware
{
    public function __construct(
        private readonly FirstPartySessionAuthorityService $sessionAuthorities,
        private readonly FirstPartyIdentityEligibilityVerifier $identityEligibility,
        private readonly TenantMembershipVerifier $tenantMemberships,
        private readonly OrganizationalRelationshipVerifier $organizationalRelationships,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->assertFeatureAvailable();

        try {
            $session = $request->session();
            foreach (array_merge(FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery(), FirstPartySessionKeys::totpRecovery()) as $restrictedKey) {
                if ($session->has($restrictedKey)) {
                    throw new InvalidArgumentException('Full-session authority state is invalid.');
                }
            }

            $tenant = $this->requiredString($request, FirstPartySessionKeys::TENANT);
            $identity = $this->requiredString($request, FirstPartySessionKeys::IDENTITY);
            $authority = $this->requiredString($request, FirstPartySessionKeys::SESSION_AUTHORITY_ID);
            $organization = $this->requiredString($request, FirstPartySessionKeys::ORGANIZATION);
            $outlet = $this->optionalString($request, FirstPartySessionKeys::OUTLET);
            $device = $this->optionalString($request, FirstPartySessionKeys::DEVICE);

            $tenantId = TenantId::fromString($tenant);
            $identityId = PlatformIdentityId::fromString($identity);
            $this->assertCanonicalIdentifier($tenant, $tenantId->value());
            $this->assertCanonicalIdentifier($identity, $identityId->value());

            $this->sessionAuthorities->assertActiveCurrent(
                $tenantId,
                $identityId,
                $authority,
                $organization,
                $outlet,
                $device,
                $session->get(FirstPartySessionKeys::CREDENTIAL_EPOCH),
                $session->get(FirstPartySessionKeys::MFA_FACTOR_EPOCH),
            );

            if (! $this->identityEligibility->isEligible($tenantId, $identityId)) {
                throw new InvalidArgumentException('Current first-party identity eligibility is not authorized.');
            }

            $organizationId = OrganizationId::fromString($organization);
            $outletId = $outlet === null ? null : OutletId::fromString($outlet);
            $deviceId = $device === null ? null : DeviceId::fromString($device);
            $this->assertCanonicalIdentifier($organization, $organizationId->value());
            if ($outletId !== null) {
                $this->assertCanonicalIdentifier($outlet, $outletId->value());
            }
            if ($deviceId !== null) {
                $this->assertCanonicalIdentifier($device, $deviceId->value());
                if ($outletId === null) {
                    throw new InvalidArgumentException('Device-bound session authority requires an outlet.');
                }
            }

            $verifiedTenant = $this->tenantMemberships->verify($identityId->value(), $tenantId->value());
            if ($verifiedTenant === null || ! hash_equals($tenantId->value(), $verifiedTenant->tenantId())) {
                throw new InvalidArgumentException('Current tenant membership is not authorized.');
            }

            if (! $this->organizationalRelationships->verify(
                $identityId,
                $tenantId,
                $organizationId,
                $outletId,
                $deviceId,
            )) {
                throw new InvalidArgumentException('Current organizational relationship is not authorized.');
            }
        } catch (Throwable) {
            return $this->deny($request);
        }

        return $next($request);
    }

    private function deny(Request $request): Response
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
        $routeName = $request->route()?->getName();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($routeName === 'auth.first-party.logout') {
            return response()->noContent(204, ['Cache-Control' => 'no-store, private']);
        }

        return response()->json(
            SafeErrorEnvelope::make('SESSION_AUTHORITY_DENIED', $correlationId),
            401,
            ['Cache-Control' => 'no-store, private'],
        );
    }

    private function requiredString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || $value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Full-session authority state is invalid.');
        }
        return $value;
    }

    private function optionalString(Request $request, string $key): ?string
    {
        $value = $request->session()->get($key);
        if ($value === null) {
            return null;
        }
        if (! is_string($value) || $value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Full-session authority state is invalid.');
        }
        return $value;
    }

    private function assertCanonicalIdentifier(string $raw, string $canonical): void
    {
        if (! hash_equals($raw, $canonical)) {
            throw new InvalidArgumentException('Full-session authority identifier is not canonical.');
        }
    }

    private function assertFeatureAvailable(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $enabled = (bool) config('oneqay.session_control.enabled', false);
        $ttl = (int) config('oneqay.session_control.idle_ttl_seconds', 0);
        $persistence = (bool) config('database.oneqay_persistence_enabled', false);
        abort_unless($enabled && $persistence && $ttl === 7200 && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }
}
