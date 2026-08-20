<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\RecoveryCodeClock;
use App\Application\Identity\RecoveryPasswordResetService;
use App\Application\Identity\RecoveryPasswordResetViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class RecoveryPasswordResetController
{
    private const RECOVERY_STATE = 'password_reset_required';
    private const RESTRICTED_SESSION_TTL_SECONDS = 600;
    private const CODE_ID_PATTERN = '/\A[0-9a-f]{32}\z/D';

    public function __construct(
        private readonly RecoveryPasswordResetService $resets,
        private readonly RecoveryCodeClock $clock,
    ) {}

    public function reset(Request $request): JsonResponse
    {
        $this->requireAllowedRuntimeAndFeature();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            if (array_keys($payload) !== ['password'] || ! is_string($payload['password'] ?? null)) {
                throw new InvalidArgumentException('Authentication recovery request is invalid.');
            }

            [$tenantId, $identityId, $codeId] = $this->restrictedContext($request);
            $this->resets->reset(
                $tenantId,
                $identityId,
                $codeId,
                $payload['password'],
                $correlationId,
            );

            $session = $request->session();
            $session->invalidate();
            $session->regenerateToken();

            return response()->json([
                'status' => 'ok',
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|RecoveryPasswordResetViolation) {
            return $this->failed($correlationId);
        }
    }

    /** @return array{0:TenantId,1:PlatformIdentityId,2:string} */
    private function restrictedContext(Request $request): array
    {
        $session = $request->session();

        foreach (array_merge(
            FirstPartySessionKeys::all(),
            FirstPartySessionKeys::pending(),
            [
                FirstPartySessionKeys::CREDENTIAL_EPOCH,
                FirstPartySessionKeys::MFA_VERIFIED_AT,
                FirstPartySessionKeys::STEP_UP_VERIFIED_AT,
                FirstPartySessionKeys::STEP_UP_SCOPE,
                FirstPartySessionKeys::STEP_UP_CONTEXT,
            ],
        ) as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Authentication recovery session is invalid.');
            }
        }

        $tenant = $session->get(FirstPartySessionKeys::RECOVERY_TENANT);
        $identity = $session->get(FirstPartySessionKeys::RECOVERY_IDENTITY);
        $codeId = $session->get(FirstPartySessionKeys::RECOVERY_CODE_ID);
        $state = $session->get(FirstPartySessionKeys::RECOVERY_STATE);
        $provedAt = $session->get(FirstPartySessionKeys::RECOVERY_PROVED_AT);
        $expiresAt = $session->get(FirstPartySessionKeys::RECOVERY_EXPIRES_AT);

        if (! is_string($tenant) || trim($tenant) === ''
            || ! is_string($identity) || trim($identity) === ''
            || ! is_string($codeId) || preg_match(self::CODE_ID_PATTERN, $codeId) !== 1
            || $state !== self::RECOVERY_STATE
            || ! is_int($provedAt) || $provedAt <= 0
            || ! is_int($expiresAt) || $expiresAt <= 0
            || $expiresAt !== $provedAt + self::RESTRICTED_SESSION_TTL_SECONDS) {
            throw new InvalidArgumentException('Authentication recovery session is invalid.');
        }

        $now = $this->clock->nowUnix();
        if ($now <= 0 || $now < $provedAt || $now > $expiresAt) {
            throw new InvalidArgumentException('Authentication recovery session is invalid.');
        }

        return [
            TenantId::fromString(trim($tenant)),
            PlatformIdentityId::fromString(trim($identity)),
            $codeId,
        ];
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
}
