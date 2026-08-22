<?php

declare(strict_types=1);

namespace App\Delivery\Http\Middleware;

use App\Application\Identity\PrivilegedStepUpClock;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Author by Lab | zefry
final class RequireFirstPartySessionControlMutationContextMiddleware
{
    private const SCOPE = 'session_control';
    private const FRESHNESS_SECONDS = 300;

    public function __construct(
        private readonly PrivilegedTotpMfaService $mfa,
        private readonly PrivilegedStepUpClock $stepUpClock,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->assertFeatureAvailable();

        try {
            $identityValue = $this->requiredString($request, FirstPartySessionKeys::IDENTITY);
            $tenantValue = $this->requiredString($request, FirstPartySessionKeys::TENANT);
            $organizationValue = $this->requiredString($request, FirstPartySessionKeys::ORGANIZATION);
            $outletValue = $this->optionalString($request, FirstPartySessionKeys::OUTLET);
            $deviceValue = $this->optionalString($request, FirstPartySessionKeys::DEVICE);

            $identityId = PlatformIdentityId::fromString($identityValue);
            $tenantId = TenantId::fromString($tenantValue);
            $state = $this->mfa->requiredState($tenantId, $identityId);
            if ($state !== null) {
                if (! $state->is(PrivilegedTotpMfaState::CONFIRMED)) {
                    throw new InvalidArgumentException('Session-control privilege evidence is invalid.');
                }
                $this->assertFreshStepUp($request, [
                    'identity_id' => $identityValue,
                    'tenant_id' => $tenantValue,
                    'organization_id' => $organizationValue,
                    'outlet_id' => $outletValue,
                    'device_id' => $deviceValue,
                ]);
            }
        } catch (Throwable) {
            abort(403, 'Session-control mutation context denied.');
        }

        return $next($request);
    }

    /** @param array{identity_id:string,tenant_id:string,organization_id:string,outlet_id:?string,device_id:?string} $expectedContext */
    private function assertFreshStepUp(Request $request, array $expectedContext): void
    {
        $session = $request->session();
        $scope = $session->get(FirstPartySessionKeys::STEP_UP_SCOPE);
        $context = $session->get(FirstPartySessionKeys::STEP_UP_CONTEXT);
        $verifiedAt = $session->get(FirstPartySessionKeys::STEP_UP_VERIFIED_AT);
        if (! is_string($scope) || ! hash_equals(self::SCOPE, $scope)
            || ! is_array($context) || $context !== $expectedContext
            || ! is_int($verifiedAt) || $verifiedAt <= 0) {
            throw new InvalidArgumentException('Session-control step-up evidence is invalid.');
        }

        $now = $this->stepUpClock->nowUnix();
        if ($now <= 0 || $now < $verifiedAt || ($now - $verifiedAt) > self::FRESHNESS_SECONDS) {
            throw new InvalidArgumentException('Session-control step-up evidence is stale.');
        }
    }

    private function requiredString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || $value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Session-control mutation session is invalid.');
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
            throw new InvalidArgumentException('Session-control mutation session is invalid.');
        }
        return $value;
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
