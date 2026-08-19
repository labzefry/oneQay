<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\PrivilegedStepUpClock;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Application\Identity\PrivilegedTotpMfaViolation;
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
final class RequirePolicyAdministrationSessionContextMiddleware
{
    private const STEP_UP_SCOPE = 'policy_administration';
    private const STEP_UP_FRESHNESS_SECONDS = 300;

    public const IDENTITY_SESSION = FirstPartySessionKeys::IDENTITY;
    public const TENANT_SESSION = FirstPartySessionKeys::TENANT;
    public const ORGANIZATION_SESSION = FirstPartySessionKeys::ORGANIZATION;
    public const OUTLET_SESSION = FirstPartySessionKeys::OUTLET;
    public const DEVICE_SESSION = FirstPartySessionKeys::DEVICE;

    public function __construct(
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
        private readonly PrivilegedTotpMfaService $mfa,
        private readonly PrivilegedStepUpClock $stepUpClock,
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

            $mfaEnabled = (bool) config('oneqay.privileged_totp_mfa.enabled', false);
            if ($mfaEnabled) {
                $mfaVerifiedAt = $request->session()->get(FirstPartySessionKeys::MFA_VERIFIED_AT);
                if (! is_int($mfaVerifiedAt) || $mfaVerifiedAt <= 0) {
                    throw new InvalidArgumentException('Policy administration security evidence is invalid.');
                }
            }

            $identityId = PlatformIdentityId::fromString($identityValue);
            $tenantId = TenantId::fromString($tenantValue);
            $identity = new ServerVerifiedPlatformIdentity($identityId);
            $tenant = new ServerVerifiedTenantContext($tenantId);
            $this->tenantContexts->setVerified($tenant);

            $this->enterOrganizationalContext->enter(
                $identity,
                $tenant,
                $organizationValue,
                $outletValue,
                $deviceValue,
            );

            if ((bool) config('oneqay.privileged_step_up.enabled', false)) {
                if (! $mfaEnabled || (int) config('oneqay.privileged_step_up.freshness_seconds', 0) !== self::STEP_UP_FRESHNESS_SECONDS) {
                    throw new InvalidArgumentException('Policy administration security evidence is invalid.');
                }

                $this->assertFreshStepUp($request, $tenantId, $identityId, [
                    'identity_id' => $identityValue,
                    'tenant_id' => $tenantValue,
                    'organization_id' => $organizationValue,
                    'outlet_id' => $outletValue,
                    'device_id' => $deviceValue,
                ]);
            }
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedTotpMfaViolation) {
            $this->clearContexts();
            abort(403, 'Policy administration context denied.');
        }

        try {
            return $next($request);
        } finally {
            $this->clearContexts();
        }
    }

    /** @param array{identity_id:string,tenant_id:string,organization_id:string,outlet_id:?string,device_id:?string} $expectedContext */
    private function assertFreshStepUp(
        Request $request,
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        array $expectedContext,
    ): void {
        $state = $this->mfa->requiredState($tenantId, $identityId);
        if ($state === null || ! $state->is(PrivilegedTotpMfaState::CONFIRMED)) {
            throw new InvalidArgumentException('Policy administration security evidence is invalid.');
        }

        $scope = $request->session()->get(FirstPartySessionKeys::STEP_UP_SCOPE);
        $context = $request->session()->get(FirstPartySessionKeys::STEP_UP_CONTEXT);
        $verifiedAt = $request->session()->get(FirstPartySessionKeys::STEP_UP_VERIFIED_AT);
        if (! is_string($scope) || ! hash_equals(self::STEP_UP_SCOPE, $scope)) {
            throw new InvalidArgumentException('Policy administration security evidence is invalid.');
        }
        if (! is_array($context) || $context !== $expectedContext) {
            throw new InvalidArgumentException('Policy administration security evidence is invalid.');
        }
        if (! is_int($verifiedAt) || $verifiedAt <= 0) {
            throw new InvalidArgumentException('Policy administration security evidence is invalid.');
        }

        $now = $this->stepUpClock->nowUnix();
        if ($now <= 0 || $now < $verifiedAt || ($now - $verifiedAt) > self::STEP_UP_FRESHNESS_SECONDS) {
            throw new InvalidArgumentException('Policy administration security evidence is invalid.');
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
