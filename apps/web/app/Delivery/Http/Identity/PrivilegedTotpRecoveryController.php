<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\PrivilegedTotpRecoveryService;
use App\Application\Identity\PrivilegedTotpRecoveryViolation;
use App\Application\Identity\VerifiedPrivilegedTotpRecoveryProof;
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

// Author by Lab | zefry
final class PrivilegedTotpRecoveryController
{
    private const STATE = 'totp_factor_replacement_required';
    private const TTL = 600;

    public function __construct(
        private readonly PrivilegedTotpRecoveryService $recovery,
        private readonly VerifyFirstPartyCredentialEpoch $credentialEpochs,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function rotate(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);
        try {
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['password', 'totp_code']
                || ! is_string($payload['password'] ?? null)
                || ! is_string($payload['totp_code'] ?? null)) {
                throw new InvalidArgumentException('Invalid request.');
            }
            $context = $this->fullContext($request);
            $this->credentialEpochs->assertCurrent(
                $context->tenantId(),
                $context->identityId(),
                $request->session()->get(FirstPartySessionKeys::CREDENTIAL_EPOCH),
            );
            $issued = $this->recovery->rotate(
                $context->tenantId(),
                $context->identityId(),
                $payload['password'],
                $payload['totp_code'],
                $correlationId,
            );
            return response()->json([
                'status' => 'ok',
                'recovery_codes' => $issued->codes(),
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|PrivilegedTotpRecoveryViolation) {
            return $this->failed($correlationId);
        } finally {
            $this->clearContexts();
        }
    }

    public function proof(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);
        try {
            $this->assertCleanAnonymousSession($request);
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['recovery_code'] || ! is_string($payload['recovery_code'] ?? null)) {
                throw new InvalidArgumentException('Invalid request.');
            }
            $proof = $this->recovery->prove($payload['recovery_code'], $correlationId);
            $this->establishRestrictedSession($request, $proof);
            return response()->json([
                'status' => 'ok',
                'state' => self::STATE,
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|PrivilegedTotpRecoveryViolation) {
            return $this->failed($correlationId);
        } finally {
            $this->clearContexts();
        }
    }

    public function startReplacement(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);
        try {
            $payload = $request->except('_token');
            if ($payload !== []) {
                throw new InvalidArgumentException('Invalid request.');
            }
            $proof = $this->restrictedProof($request);
            $issued = $this->recovery->startReplacement($proof);
            $request->session()->put(FirstPartySessionKeys::TOTP_RECOVERY_REPLACEMENT, $issued['sealed_replacement']);
            return response()->json([
                'status' => 'ok',
                'secret' => $issued['secret'],
                'provisioning_uri' => $issued['provisioning_uri'],
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|PrivilegedTotpRecoveryViolation) {
            return $this->failed($correlationId);
        }
    }

    public function confirmReplacement(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = $this->correlationId($request);
        try {
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['totp_code'] || ! is_string($payload['totp_code'] ?? null)) {
                throw new InvalidArgumentException('Invalid request.');
            }
            $proof = $this->restrictedProof($request);
            $sealed = $request->session()->get(FirstPartySessionKeys::TOTP_RECOVERY_REPLACEMENT);
            if (! is_string($sealed) || $sealed === '') {
                throw new InvalidArgumentException('Invalid recovery state.');
            }
            $newEpoch = $this->recovery->confirmReplacement($proof, $sealed, $payload['totp_code'], $correlationId);
            $session = $request->session();
            $session->invalidate();
            $session->regenerateToken();
            return response()->json([
                'status' => 'ok',
                'factor_epoch' => $newEpoch,
                'requires_fresh_login' => true,
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|PrivilegedTotpRecoveryViolation) {
            return $this->failed($correlationId);
        }
    }

    private function fullContext(Request $request): VerifiedOrganizationalContext
    {
        $session = $request->session();
        foreach (array_merge(FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery(), FirstPartySessionKeys::totpRecovery()) as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Invalid session.');
            }
        }
        $identityId = PlatformIdentityId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::IDENTITY));
        $tenantId = TenantId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::TENANT));
        $identity = new ServerVerifiedPlatformIdentity($identityId);
        $tenant = new ServerVerifiedTenantContext($tenantId);
        $this->tenantContexts->setVerified($tenant);
        return $this->enterOrganizationalContext->enter(
            $identity,
            $tenant,
            $this->requiredSessionString($request, FirstPartySessionKeys::ORGANIZATION),
            $this->optionalSessionString($request, FirstPartySessionKeys::OUTLET),
            $this->optionalSessionString($request, FirstPartySessionKeys::DEVICE),
        );
    }

    private function assertCleanAnonymousSession(Request $request): void
    {
        $keys = array_merge(
            FirstPartySessionKeys::all(),
            FirstPartySessionKeys::pending(),
            FirstPartySessionKeys::recovery(),
            FirstPartySessionKeys::totpRecovery(),
            [
                FirstPartySessionKeys::CREDENTIAL_EPOCH,
                FirstPartySessionKeys::MFA_VERIFIED_AT,
                FirstPartySessionKeys::MFA_FACTOR_EPOCH,
                FirstPartySessionKeys::STEP_UP_VERIFIED_AT,
                FirstPartySessionKeys::STEP_UP_SCOPE,
                FirstPartySessionKeys::STEP_UP_CONTEXT,
            ],
        );
        foreach (array_unique($keys) as $key) {
            if ($request->session()->has($key)) {
                throw new InvalidArgumentException('Invalid anonymous recovery session.');
            }
        }
    }

    private function establishRestrictedSession(Request $request, VerifiedPrivilegedTotpRecoveryProof $proof): void
    {
        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_TENANT, $proof->tenantId()->value());
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_IDENTITY, $proof->identityId()->value());
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_CODE_ID, $proof->codeId());
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_FACTOR_EPOCH, $proof->factorEpoch());
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_STATE, self::STATE);
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_PROVED_AT, $proof->provedAtUnix());
        $session->put(FirstPartySessionKeys::TOTP_RECOVERY_EXPIRES_AT, $proof->provedAtUnix() + self::TTL);
    }

    private function restrictedProof(Request $request): VerifiedPrivilegedTotpRecoveryProof
    {
        $session = $request->session();
        foreach (array_merge(FirstPartySessionKeys::all(), FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery()) as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Invalid restricted recovery session.');
            }
        }
        foreach ([FirstPartySessionKeys::MFA_VERIFIED_AT, FirstPartySessionKeys::MFA_FACTOR_EPOCH, FirstPartySessionKeys::STEP_UP_VERIFIED_AT, FirstPartySessionKeys::STEP_UP_SCOPE, FirstPartySessionKeys::STEP_UP_CONTEXT, FirstPartySessionKeys::CREDENTIAL_EPOCH] as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Invalid restricted recovery session.');
            }
        }
        $state = $session->get(FirstPartySessionKeys::TOTP_RECOVERY_STATE);
        $provedAt = $session->get(FirstPartySessionKeys::TOTP_RECOVERY_PROVED_AT);
        $expiresAt = $session->get(FirstPartySessionKeys::TOTP_RECOVERY_EXPIRES_AT);
        $epoch = $session->get(FirstPartySessionKeys::TOTP_RECOVERY_FACTOR_EPOCH);
        if (! is_string($state) || ! hash_equals(self::STATE, $state)
            || ! is_int($provedAt) || $provedAt <= 0
            || ! is_int($expiresAt) || $expiresAt !== $provedAt + self::TTL
            || time() > $expiresAt
            || ! is_int($epoch) || $epoch < 0) {
            throw new InvalidArgumentException('Expired or invalid restricted recovery session.');
        }
        return new VerifiedPrivilegedTotpRecoveryProof(
            TenantId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::TOTP_RECOVERY_TENANT)),
            PlatformIdentityId::fromString($this->requiredSessionString($request, FirstPartySessionKeys::TOTP_RECOVERY_IDENTITY)),
            $this->requiredSessionString($request, FirstPartySessionKeys::TOTP_RECOVERY_CODE_ID),
            $epoch,
            $provedAt,
        );
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Invalid session.');
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
            throw new InvalidArgumentException('Invalid session.');
        }
        return trim($value);
    }

    private function requireAllowedRuntimeAndFeature(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $enabled = (bool) config('oneqay.authentication_recovery.enabled', false)
            && (bool) config('oneqay.privileged_totp_mfa.enabled', false);
        abort_unless($enabled && (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0) === self::TTL
            && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function correlationId(Request $request): string
    {
        return (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
    }

    private function failed(string $correlationId): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('PRIVILEGED_TOTP_RECOVERY_FAILED', $correlationId),
            401,
            ['Cache-Control' => 'no-store, private'],
        );
    }

    private function clearContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
