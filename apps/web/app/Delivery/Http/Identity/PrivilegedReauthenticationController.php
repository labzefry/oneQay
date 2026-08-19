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
    private const SCOPE = 'policy_administration';

    public function __construct(
        private readonly PrivilegedStepUpService $stepUp,
        private readonly TenantContextStore $tenantContexts,
        private readonly OrganizationalContextStore $organizationalContexts,
        private readonly EnterOrganizationalContext $enterOrganizationalContext,
    ) {}

    public function reauthenticate(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeatures();
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

            [$context, $mfaVerifiedAt] = $this->fullContext($request);
            $verifiedAt = $this->stepUp->verify(
                $context->tenantId(),
                $context->identityId(),
                $password,
                $code,
            );
            $this->establishStepUpSession($request, $context, $mfaVerifiedAt, $verifiedAt);

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

    /** @return array{0: VerifiedOrganizationalContext, 1: int} */
    private function fullContext(Request $request): array
    {
        $session = $request->session();
        foreach (FirstPartySessionKeys::pending() as $pendingKey) {
            if ($session->has($pendingKey)) {
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

        return [$context, $mfaVerifiedAt];
    }

    private function establishStepUpSession(
        Request $request,
        VerifiedOrganizationalContext $context,
        int $mfaVerifiedAt,
        int $verifiedAt,
    ): void {
        if ($mfaVerifiedAt <= 0 || $verifiedAt <= 0) {
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
        $session->put(FirstPartySessionKeys::STEP_UP_SCOPE, self::SCOPE);
        $session->put(FirstPartySessionKeys::STEP_UP_CONTEXT, [
            'identity_id' => $context->identityId()->value(),
            'tenant_id' => $context->tenantId()->value(),
            'organization_id' => $context->organizationId()->value(),
            'outlet_id' => $context->outletId()?->value(),
            'device_id' => $context->deviceId()?->value(),
        ]);

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

    private function requireAllowedRuntimeAndFeatures(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $stepUp = (bool) config('oneqay.privileged_step_up.enabled', false);
        $mfa = (bool) config('oneqay.privileged_totp_mfa.enabled', false);
        $freshness = (int) config('oneqay.privileged_step_up.freshness_seconds', 0);
        abort_unless($stepUp && $mfa && $freshness === 300 && in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function clearContexts(): void
    {
        $this->organizationalContexts->clear();
        $this->tenantContexts->clear();
    }
}
