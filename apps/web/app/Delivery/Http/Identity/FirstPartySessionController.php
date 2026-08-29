<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Application\Identity\VerifyFirstPartyCredentialEpoch;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
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
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class FirstPartySessionController
{
    /** @var list<string> */
    private const ALLOWED_LOGIN_FIELDS = [
        'tenant_id',
        'identity_id',
        'password',
        'organization_id',
        'outlet_id',
        'device_id',
    ];

    public function __construct(
        private readonly VerifyFirstPartyIdentityCredential $credentials,
        private readonly FirstPartyIdentityEligibilityVerifier $identityEligibility,
        private readonly PrivilegedTotpMfaService $mfa,
        private readonly VerifyFirstPartyCredentialEpoch $credentialEpochs,
        private readonly FirstPartySessionAuthorityService $sessionAuthorities,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $this->requireAllowedRuntime();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            $this->assertClosedLoginPayload($payload);

            $tenantValue = $this->requiredTrimmedString($payload, 'tenant_id');
            $identityValue = $this->requiredTrimmedString($payload, 'identity_id');
            $organizationValue = $this->requiredTrimmedString($payload, 'organization_id');
            $outletValue = $this->optionalTrimmedString($payload, 'outlet_id');
            $deviceValue = $this->optionalTrimmedString($payload, 'device_id');
            $password = $payload['password'] ?? null;

            if (! is_string($password)) {
                throw new InvalidArgumentException('Authentication request is invalid.');
            }

            $tenantId = TenantId::fromString($tenantValue);
            $identityId = PlatformIdentityId::fromString($identityValue);

            if (! $this->credentials->verify($tenantId, $identityId, $password)) {
                return $this->authenticationFailed($correlationId);
            }

            if ($this->sessionControlEnabled() && ! $this->identityEligibility->isEligible($tenantId, $identityId)) {
                return $this->authenticationFailed($correlationId);
            }

            $identity = new ServerVerifiedPlatformIdentity($identityId);
            $tenant = new ServerVerifiedTenantContext($tenantId);
            $this->tenantContexts->setVerified($tenant);

            $context = $this->enterOrganizationalContext->enter(
                $identity,
                $tenant,
                $organizationValue,
                $outletValue,
                $deviceValue,
            );

            if ($this->mfaEnabled()) {
                try {
                    $mfaState = $this->mfa->requiredState($tenantId, $identityId);
                } catch (PrivilegedTotpMfaViolation) {
                    return $this->authenticationFailed($correlationId);
                }

                if ($mfaState !== null) {
                    $pendingState = $mfaState->is(PrivilegedTotpMfaState::CONFIRMED)
                        ? FirstPartySessionKeys::MFA_CHALLENGE_REQUIRED
                        : FirstPartySessionKeys::MFA_ENROLLMENT_REQUIRED;

                    $this->establishPendingMfaSession($request, $context, $pendingState);

                    return response()->json([
                        'status' => 'mfa_required',
                        'code' => $pendingState === FirstPartySessionKeys::MFA_CHALLENGE_REQUIRED
                            ? 'MFA_CHALLENGE_REQUIRED'
                            : 'MFA_ENROLLMENT_REQUIRED',
                        'correlation_id' => $correlationId,
                    ], 202);
                }
            }

            $credentialEpoch = $this->credentialEpochs->capture($tenantId, $identityId);
            $authorityId = null;
            if ($this->sessionControlEnabled()) {
                $issued = $this->sessionAuthorities->issue(
                    $tenantId,
                    $identityId,
                    $context->organizationId()->value(),
                    $context->outletId()?->value(),
                    $context->deviceId()?->value(),
                    $credentialEpoch,
                    null,
                    $correlationId,
                );
                $authorityId = $issued->authorityId();
            }
            $this->establishFullSession($request, $context, $credentialEpoch, $authorityId);

            return response()->json([
                'status' => 'ok',
                'correlation_id' => $correlationId,
            ]);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|FirstPartySessionAuthorityViolation) {
            return $this->authenticationFailed($correlationId);
        } finally {
            $this->clearRequestContexts();
        }
    }

    public function logout(Request $request): Response
    {
        $this->requireAllowedRuntime();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            if ($this->sessionControlEnabled()) {
                try {
                    $tenant = $request->session()->get(FirstPartySessionKeys::TENANT);
                    $identity = $request->session()->get(FirstPartySessionKeys::IDENTITY);
                    $authority = $request->session()->get(FirstPartySessionKeys::SESSION_AUTHORITY_ID);
                    if (is_string($tenant) && is_string($identity) && is_string($authority)) {
                        $this->sessionAuthorities->logoutCurrent(
                            TenantId::fromString($tenant),
                            PlatformIdentityId::fromString($identity),
                            $authority,
                            $correlationId,
                        );
                    }
                } catch (InvalidArgumentException|FirstPartySessionAuthorityViolation) {
                    // Logout remains safe for stale or malformed local session state.
                }
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->noContent();
        } finally {
            $this->clearRequestContexts();
        }
    }

    private function establishFullSession(
        Request $request,
        VerifiedOrganizationalContext $context,
        int $credentialEpoch,
        ?string $authorityId,
    ): void {
        if ($credentialEpoch < 0) {
            throw new InvalidArgumentException('Authentication request is invalid.');
        }

        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::IDENTITY, $context->identityId()->value());
        $session->put(FirstPartySessionKeys::TENANT, $context->tenantId()->value());
        $session->put(FirstPartySessionKeys::ORGANIZATION, $context->organizationId()->value());
        $session->put(FirstPartySessionKeys::CREDENTIAL_EPOCH, $credentialEpoch);
        if ($authorityId !== null) {
            $session->put(FirstPartySessionKeys::SESSION_AUTHORITY_ID, $authorityId);
        }

        if ($context->outletId() !== null) {
            $session->put(FirstPartySessionKeys::OUTLET, $context->outletId()->value());
        }

        if ($context->deviceId() !== null) {
            $session->put(FirstPartySessionKeys::DEVICE, $context->deviceId()->value());
        }
    }

    private function establishPendingMfaSession(
        Request $request,
        VerifiedOrganizationalContext $context,
        string $pendingState,
    ): void {
        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::PENDING_IDENTITY, $context->identityId()->value());
        $session->put(FirstPartySessionKeys::PENDING_TENANT, $context->tenantId()->value());
        $session->put(FirstPartySessionKeys::PENDING_ORGANIZATION, $context->organizationId()->value());
        $session->put(FirstPartySessionKeys::PENDING_MFA_STATE, $pendingState);

        if ($context->outletId() !== null) {
            $session->put(FirstPartySessionKeys::PENDING_OUTLET, $context->outletId()->value());
        }

        if ($context->deviceId() !== null) {
            $session->put(FirstPartySessionKeys::PENDING_DEVICE, $context->deviceId()->value());
        }
    }

    /** @param array<string, mixed> $payload */
    private function assertClosedLoginPayload(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (! is_string($key) || ! in_array($key, self::ALLOWED_LOGIN_FIELDS, true)) {
                throw new InvalidArgumentException('Authentication request is invalid.');
            }
        }

        foreach (['tenant_id', 'identity_id', 'password', 'organization_id'] as $required) {
            if (! array_key_exists($required, $payload)) {
                throw new InvalidArgumentException('Authentication request is invalid.');
            }
        }
    }

    /** @param array<string, mixed> $payload */
    private function requiredTrimmedString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Authentication request is invalid.');
        }

        return trim($value);
    }

    /** @param array<string, mixed> $payload */
    private function optionalTrimmedString(array $payload, string $key): ?string
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = $payload[$key];
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Authentication request is invalid.');
        }

        return trim($value);
    }

    private function authenticationFailed(string $correlationId): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('AUTHENTICATION_FAILED', $correlationId),
            401,
        );
    }

    private function mfaEnabled(): bool
    {
        return (bool) config('oneqay.privileged_totp_mfa.enabled', false);
    }

    private function sessionControlEnabled(): bool
    {
        return (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200;
    }

    private function requireAllowedRuntime(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless(in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function clearRequestContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
