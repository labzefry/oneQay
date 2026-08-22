<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\PrivilegedStepUpService;
use App\Application\Identity\PrivilegedStepUpViolation;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\TenantContextStore;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PrivilegedReauthenticationController
{
    private const POLICY_SCOPE = 'policy_administration';
    private const SESSION_CONTROL_SCOPE = 'session_control';

    public function __construct(
        private readonly PrivilegedStepUpService $stepUp,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function reauthenticate(Request $request): JsonResponse
    {
        return $this->reauthenticateForScope($request, self::POLICY_SCOPE, false);
    }

    public function sessionControl(Request $request): JsonResponse
    {
        return $this->reauthenticateForScope($request, self::SESSION_CONTROL_SCOPE, true);
    }

    private function reauthenticateForScope(Request $request, string $scope, bool $requiresSessionControl): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeatures($requiresSessionControl);
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            $keys = array_keys($payload);
            sort($keys);
            if ($keys !== ['code', 'password']) {
                throw new InvalidArgumentException('Privileged reauthentication request is invalid.');
            }

            $password = $payload['password'] ?? null;
            $code = $payload['code'] ?? null;
            if (! is_string($password) || ! is_string($code) || preg_match('/\A[0-9]{6}\z/D', $code) !== 1) {
                throw new InvalidArgumentException('Privileged reauthentication request is invalid.');
            }

            [$context, $mfaVerifiedAt, $preserved] = $this->fullContext($request);
            $verifiedAt = $this->stepUp->verify(
                $context->tenantId(),
                $context->identityId(),
                $password,
                $code,
            );
            $this->establishStepUpSession($request, $context, $mfaVerifiedAt, $verifiedAt, $scope, $preserved);

            return response()->json([
                'status' => 'ok',
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedStepUpViolation) {
            return response()->json(
                SafeErrorEnvelope::make('PRIVILEGED_REAUTHENTICATION_FAILED', $correlationId),
                401,
                ['Cache-Control' => 'no-store, private'],
            );
        } finally {
            $this->clearContexts();
        }
    }

    /** @return array{0: VerifiedOrganizationalContext, 1: int, 2: array<string,mixed>} */
    private function fullContext(Request $request): array
    {
        $session = $request->session();
        foreach (array_merge(FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery(), FirstPartySessionKeys::totpRecovery()) as $restrictedKey) {
            if ($session->has($restrictedKey)) {
                throw new InvalidArgumentException('Privileged reauthentication session is invalid.');
            }
        }

        $identityId = PlatformIdentityId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::IDENTITY));
        $tenantId = TenantId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::TENANT));
        $organizationId = $this->requiredSessionString($request, FirstPartySessionKeys::ORGANIZATION);
        $outletId = $this->optionalSessionString($request, FirstPartySessionKeys::OUTLET);
        $deviceId = $this->optionalSessionString($request, FirstPartySessionKeys::DEVICE);
        $mfaVerifiedAt = $session->get(FirstPartySessionKeys::MFA_VERIFIED_AT);
        if (! is_int($mfaVerifiedAt) || $mfaVerifiedAt <= 0) {
            throw new InvalidArgumentException('Privileged reauthentication session is invalid.');
        }

        $identity = new ServerVerifiedPlatformIdentity($identityId);
        $tenant = new ServerVerifiedTenantContext($tenantId);
        $this->tenantContexts->setVerified($tenant);
        $context = $this->enterOrganizationalContext->enter(
            $identity,
            $tenant,
            $organizationId,
            $outletId,
            $deviceId,
        );

        $preserved = [];
        foreach ([
            FirstPartySessionKeys::CREDENTIAL_EPOCH,
            FirstPartySessionKeys::MFA_FACTOR_EPOCH,
            FirstPartySessionKeys::SESSION_AUTHORITY_ID,
        ] as $key) {
            if ($session->has($key)) {
                $preserved[$key] = $session->get($key);
            }
        }

        return [$context, $mfaVerifiedAt, $preserved];
    }

    /** @param array<string,mixed> $preserved */
    private function establishStepUpSession(
        Request $request,
        VerifiedOrganizationalContext $context,
        int $mfaVerifiedAt,
        int $verifiedAt,
        string $scope,
        array $preserved,
    ): void {
        if ($mfaVerifiedAt <= 0 || $verifiedAt <= 0 || ! in_array($scope, [self::POLICY_SCOPE, self::SESSION_CONTROL_SCOPE], true)) {
            throw new InvalidArgumentException('Privileged reauthentication evidence is invalid.');
        }

        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::IDENTITY, $context->identityId()->value());
        $session->put(FirstPartySessionKeys::TENANT, $context->tenantId()->value());
        $session->put(FirstPartySessionKeys::ORGANIZATION, $context->organizationId()->value());
        $session->put(FirstPartySessionKeys::MFA_VERIFIED_AT, $mfaVerifiedAt);
        $session->put(FirstPartySessionKeys::STEP_UP_VERIFIED_AT, $verifiedAt);
        $session->put(FirstPartySessionKeys::STEP_UP_SCOPE, $scope);
        $session->put(FirstPartySessionKeys::STEP_UP_CONTEXT, [
            'identity_id' => $context->identityId()->value(),
            'tenant_id' => $context->tenantId()->value(),
            'organization_id' => $context->organizationId()->value(),
            'outlet_id' => $context->outletId()?->value(),
            'device_id' => $context->deviceId()?->value(),
        ]);

        foreach ($preserved as $key => $value) {
            if (in_array($key, [FirstPartySessionKeys::CREDENTIAL_EPOCH, FirstPartySessionKeys::MFA_FACTOR_EPOCH], true)) {
                if (! is_int($value) || $value < 0) {
                    throw new InvalidArgumentException('Privileged reauthentication epoch evidence is invalid.');
                }
            } elseif ($key === FirstPartySessionKeys::SESSION_AUTHORITY_ID) {
                if (! is_string($value) || preg_match('/\A[0-9a-f]{32}\z/D', $value) !== 1) {
                    throw new InvalidArgumentException('Privileged reauthentication authority evidence is invalid.');
                }
            } else {
                throw new InvalidArgumentException('Privileged reauthentication preservation set is invalid.');
            }
            $session->put($key, $value);
        }

        if ($context->outletId() !== null) {
            $session->put(FirstPartySessionKeys::OUTLET, $context->outletId()->value());
        }
        if ($context->deviceId() !== null) {
            $session->put(FirstPartySessionKeys::DEVICE, $context->deviceId()->value());
        }
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Privileged reauthentication session is invalid.');
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
            throw new InvalidArgumentException('Privileged reauthentication session is invalid.');
        }
        return trim($value);
    }

    private function requireAllowedRuntimeAndFeatures(bool $requiresSessionControl): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $stepUp = (bool) config('oneqay.privileged_step_up.enabled', false);
        $mfa = (bool) config('oneqay.privileged_totp_mfa.enabled', false);
        $freshness = (int) config('oneqay.privileged_step_up.freshness_seconds', 0);
        $sessionControl = ! $requiresSessionControl || (
            (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
        );
        abort_unless($stepUp && $mfa && $sessionControl && $freshness === 300 && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function clearContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
