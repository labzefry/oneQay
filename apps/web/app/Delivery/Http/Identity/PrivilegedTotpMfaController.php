<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Application\Identity\PrivilegedTotpRecoveryViolation;
use App\Application\Identity\VerifyFirstPartyCredentialEpoch;
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
final class PrivilegedTotpMfaController
{
    public function __construct(
        private readonly PrivilegedTotpMfaService $mfa,
        private readonly VerifyFirstPartyCredentialEpoch $credentialEpochs,
        private readonly PrivilegedTotpFactorEpochRepository $factorEpochs,
        private readonly FirstPartySessionAuthorityService $sessionAuthorities,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function startEnrollment(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);

        try {
            $this->assertNoPayload($request);
            $context = $this->pendingContext($request, FirstPartySessionKeys::MFA_ENROLLMENT_REQUIRED);
            $issued = $this->mfa->startEnrollment($context->tenantId(), $context->identityId());

            return response()->json([
                'status' => 'enrollment_pending',
                'secret' => $issued->secret(),
                'provisioning_uri' => $issued->provisioningUri(),
                'correlation_id' => $correlationId,
            ])->withHeaders([
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
            ]);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedTotpMfaViolation) {
            return $this->mfaFailed($correlationId, 403);
        } finally {
            $this->clearRequestContexts();
        }
    }

    public function confirmEnrollment(Request $request): Response
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);

        try {
            $code = $this->exactCode($request);
            $context = $this->pendingContext($request, FirstPartySessionKeys::MFA_ENROLLMENT_REQUIRED);
            $this->mfa->confirmEnrollment($context->tenantId(), $context->identityId(), $code);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->noContent(204, [
                'Cache-Control' => 'no-store, private',
            ]);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedTotpMfaViolation) {
            return $this->mfaFailed($correlationId, 401);
        } finally {
            $this->clearRequestContexts();
        }
    }

    public function challenge(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);

        try {
            $code = $this->exactCode($request);
            $context = $this->pendingContext($request, FirstPartySessionKeys::MFA_CHALLENGE_REQUIRED);
            $verifiedAt = $this->mfa->challenge($context->tenantId(), $context->identityId(), $code);

            $credentialEpoch = null;
            $factorEpoch = null;
            $authorityId = null;
            if ($this->sessionControlEnabled()) {
                $credentialEpoch = $this->credentialEpochs->capture($context->tenantId(), $context->identityId());
                $factorEpoch = $this->factorEpochs->currentEpoch($context->tenantId(), $context->identityId());
                $issued = $this->sessionAuthorities->issue(
                    $context->tenantId(),
                    $context->identityId(),
                    $context->organizationId()->value(),
                    $context->outletId()?->value(),
                    $context->deviceId()?->value(),
                    $credentialEpoch,
                    $factorEpoch,
                    $correlationId,
                );
                $authorityId = $issued->authorityId();
            }

            $this->establishVerifiedFullSession(
                $request,
                $context,
                $verifiedAt,
                $credentialEpoch,
                $factorEpoch,
                $authorityId,
            );

            return response()->json([
                'status' => 'ok',
                'correlation_id' => $correlationId,
            ], 200, [
                'Cache-Control' => 'no-store, private',
            ]);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedTotpMfaViolation|PrivilegedTotpRecoveryViolation|FirstPartySessionAuthorityViolation) {
            return $this->mfaFailed($correlationId, 401);
        } finally {
            $this->clearRequestContexts();
        }
    }

    private function pendingContext(Request $request, string $expectedState): VerifiedOrganizationalContext
    {
        $session = $request->session();
        $state = $this->requiredSessionString($request, FirstPartySessionKeys::PENDING_MFA_STATE);
        if (! hash_equals($expectedState, $state)) {
            throw new InvalidArgumentException('Privileged TOTP pending session state is invalid.');
        }

        $tenantId = TenantId::fromString(
            $this->requiredSessionString($request, FirstPartySessionKeys::PENDING_TENANT),
        );
        $identityId = PlatformIdentityId::fromString(
            $this->requiredSessionString($request, FirstPartySessionKeys::PENDING_IDENTITY),
        );
        $organizationId = $this->requiredSessionString($request, FirstPartySessionKeys::PENDING_ORGANIZATION);
        $outletId = $this->optionalSessionString($request, FirstPartySessionKeys::PENDING_OUTLET);
        $deviceId = $this->optionalSessionString($request, FirstPartySessionKeys::PENDING_DEVICE);

        foreach (FirstPartySessionKeys::all() as $fullKey) {
            if ($session->has($fullKey)) {
                throw new InvalidArgumentException('Privileged TOTP pending session contains full authentication state.');
            }
        }
        if ($session->has(FirstPartySessionKeys::MFA_VERIFIED_AT)
            || $session->has(FirstPartySessionKeys::SESSION_AUTHORITY_ID)) {
            throw new InvalidArgumentException('Privileged TOTP pending session contains full authority evidence.');
        }

        $identity = new ServerVerifiedPlatformIdentity($identityId);
        $tenant = new ServerVerifiedTenantContext($tenantId);
        $this->tenantContexts->setVerified($tenant);

        return $this->enterOrganizationalContext->enter(
            $identity,
            $tenant,
            $organizationId,
            $outletId,
            $deviceId,
        );
    }

    private function establishVerifiedFullSession(
        Request $request,
        VerifiedOrganizationalContext $context,
        int $verifiedAt,
        ?int $credentialEpoch,
        ?int $factorEpoch,
        ?string $authorityId,
    ): void {
        if ($verifiedAt <= 0) {
            throw new InvalidArgumentException('Privileged TOTP verification timestamp is invalid.');
        }

        if ($authorityId !== null
            && ($credentialEpoch === null || $credentialEpoch < 0 || $factorEpoch === null || $factorEpoch < 0)) {
            throw new InvalidArgumentException('Privileged session authority evidence is invalid.');
        }

        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::IDENTITY, $context->identityId()->value());
        $session->put(FirstPartySessionKeys::TENANT, $context->tenantId()->value());
        $session->put(FirstPartySessionKeys::ORGANIZATION, $context->organizationId()->value());
        $session->put(FirstPartySessionKeys::MFA_VERIFIED_AT, $verifiedAt);

        if ($authorityId !== null) {
            $session->put(FirstPartySessionKeys::CREDENTIAL_EPOCH, $credentialEpoch);
            $session->put(FirstPartySessionKeys::MFA_FACTOR_EPOCH, $factorEpoch);
            $session->put(FirstPartySessionKeys::SESSION_AUTHORITY_ID, $authorityId);
        }

        if ($context->outletId() !== null) {
            $session->put(FirstPartySessionKeys::OUTLET, $context->outletId()->value());
        }

        if ($context->deviceId() !== null) {
            $session->put(FirstPartySessionKeys::DEVICE, $context->deviceId()->value());
        }
    }

    private function exactCode(Request $request): string
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->except('_token');
        if (array_keys($payload) !== ['code']) {
            throw new InvalidArgumentException('Privileged TOTP verification request is invalid.');
        }

        $code = $payload['code'] ?? null;
        if (! is_string($code) || preg_match('/\A[0-9]{6}\z/D', $code) !== 1) {
            throw new InvalidArgumentException('Privileged TOTP verification request is invalid.');
        }

        return $code;
    }

    private function assertNoPayload(Request $request): void
    {
        if ($request->except('_token') !== []) {
            throw new InvalidArgumentException('Privileged TOTP enrollment request is invalid.');
        }
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Privileged TOTP pending session is invalid.');
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
            throw new InvalidArgumentException('Privileged TOTP pending session is invalid.');
        }

        return trim($value);
    }

    private function correlationId(Request $request): string
    {
        return (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
    }

    private function mfaFailed(string $correlationId, int $status): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('MFA_VERIFICATION_FAILED', $correlationId),
            $status,
            ['Cache-Control' => 'no-store, private'],
        );
    }

    private function requireAllowedRuntimeAndFeature(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $enabled = (bool) config('oneqay.privileged_totp_mfa.enabled', false);
        abort_unless($enabled && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function sessionControlEnabled(): bool
    {
        return (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200;
    }

    private function clearRequestContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
