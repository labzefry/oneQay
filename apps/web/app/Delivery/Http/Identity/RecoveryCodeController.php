<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\RecoveryCodeService;
use App\Application\Identity\RecoveryCodeViolation;
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
final class RecoveryCodeController
{
    private const RECOVERY_STATE = 'password_reset_required';
    private const RESTRICTED_SESSION_TTL_SECONDS = 600;

    public function __construct(
        private readonly RecoveryCodeService $recovery,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function rotate(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['password'] || ! is_string($payload['password'] ?? null)) {
                throw new InvalidArgumentException('Authentication recovery request is invalid.');
            }

            $context = $this->fullContext($request);
            $issued = $this->recovery->rotate(
                $context->tenantId(),
                $context->identityId(),
                $payload['password'],
                $correlationId,
            );

            return response()->json([
                'status' => 'ok',
                'recovery_codes' => $issued->codes(),
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|IdentityContextViolation|MissingTenantContext|OrganizationalAccessViolation|RecoveryCodeViolation) {
            return $this->failed($correlationId);
        } finally {
            $this->clearContexts();
        }
    }

    public function proof(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $this->assertCleanAnonymousSession($request);

            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['recovery_code'] || ! is_string($payload['recovery_code'] ?? null)) {
                throw new InvalidArgumentException('Authentication recovery request is invalid.');
            }

            $verified = $this->recovery->prove($payload['recovery_code'], $correlationId);
            $this->establishRestrictedRecoverySession($request, $verified);

            return response()->json([
                'status' => 'ok',
                'state' => self::RECOVERY_STATE,
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|RecoveryCodeViolation) {
            return $this->failed($correlationId);
        } finally {
            $this->clearContexts();
        }
    }

    private function fullContext(Request $request): VerifiedOrganizationalContext
    {
        $session = $request->session();
        foreach (FirstPartySessionKeys::pending() as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Authentication recovery session is invalid.');
            }
        }
        foreach (FirstPartySessionKeys::recovery() as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Authentication recovery session is invalid.');
            }
        }

        $identityId = PlatformIdentityId::fromString(
            $this->requiredSessionString($request, FirstPartySessionKeys::IDENTITY),
        );
        $tenantId = TenantId::fromString(
            $this->requiredSessionString($request, FirstPartySessionKeys::TENANT),
        );
        $organizationId = $this->requiredSessionString($request, FirstPartySessionKeys::ORGANIZATION);
        $outletId = $this->optionalSessionString($request, FirstPartySessionKeys::OUTLET);
        $deviceId = $this->optionalSessionString($request, FirstPartySessionKeys::DEVICE);

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

    private function assertCleanAnonymousSession(Request $request): void
    {
        $session = $request->session();
        $keys = array_merge(
            FirstPartySessionKeys::all(),
            FirstPartySessionKeys::pending(),
            FirstPartySessionKeys::recovery(),
            [
                FirstPartySessionKeys::MFA_VERIFIED_AT,
                FirstPartySessionKeys::STEP_UP_VERIFIED_AT,
                FirstPartySessionKeys::STEP_UP_SCOPE,
                FirstPartySessionKeys::STEP_UP_CONTEXT,
            ],
        );

        foreach (array_unique($keys) as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Authentication recovery session is invalid.');
            }
        }
    }

    private function establishRestrictedRecoverySession(
        Request $request,
        \App\Application\Identity\VerifiedRecoveryProof $verified,
    ): void {
        $provedAt = $verified->provedAtUnix();
        if ($provedAt <= 0) {
            throw new InvalidArgumentException('Authentication recovery proof is invalid.');
        }

        $session = $request->session();
        $session->invalidate();
        $session->regenerateToken();
        $session->put(FirstPartySessionKeys::RECOVERY_TENANT, $verified->tenantId()->value());
        $session->put(FirstPartySessionKeys::RECOVERY_IDENTITY, $verified->identityId()->value());
        $session->put(FirstPartySessionKeys::RECOVERY_STATE, self::RECOVERY_STATE);
        $session->put(FirstPartySessionKeys::RECOVERY_PROVED_AT, $provedAt);
        $session->put(FirstPartySessionKeys::RECOVERY_EXPIRES_AT, $provedAt + self::RESTRICTED_SESSION_TTL_SECONDS);
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Authentication recovery session is invalid.');
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
            throw new InvalidArgumentException('Authentication recovery session is invalid.');
        }

        return trim($value);
    }

    private function requireAllowedRuntimeAndFeature(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $enabled = (bool) config('oneqay.authentication_recovery.enabled', false);
        $ttl = (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0);
        abort_unless($enabled && $ttl === self::RESTRICTED_SESSION_TTL_SECONDS
            && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function failed(string $correlationId): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('AUTHENTICATION_RECOVERY_FAILED', $correlationId),
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
