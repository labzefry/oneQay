<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\AuthenticatedPasswordChangeService;
use App\Application\Identity\AuthenticatedPasswordChangeViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class AuthenticatedPasswordChangeController
{
    /** @var list<string> */
    private const ALLOWED_FIELDS = ['current_password', 'new_password', 'totp_code'];

    public function __construct(
        private readonly AuthenticatedPasswordChangeService $changes,
    ) {}

    public function change(Request $request): JsonResponse
    {
        $this->requireAllowedRuntime();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            $currentPassword = $payload['current_password'] ?? null;
            $newPassword = $payload['new_password'] ?? null;
            $totpCode = $payload['totp_code'] ?? null;
            if (! is_string($currentPassword)
                || ! is_string($newPassword)
                || ($totpCode !== null && ! is_string($totpCode))) {
                throw new InvalidArgumentException('Password change request is invalid.');
            }

            [$tenantId, $identityId, $credentialEpoch] = $this->fullSessionContext($request);

            $this->changes->change(
                $tenantId,
                $identityId,
                $credentialEpoch,
                $currentPassword,
                $newPassword,
                $totpCode,
            );

            $session = $request->session();
            $session->invalidate();
            $session->regenerateToken();

            return response()->json([
                'status' => 'ok',
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|AuthenticatedPasswordChangeViolation) {
            return $this->failed($correlationId);
        }
    }

    /** @param array<string, mixed> $payload */
    private function assertClosedPayload(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (! is_string($key) || ! in_array($key, self::ALLOWED_FIELDS, true)) {
                throw new InvalidArgumentException('Password change request is invalid.');
            }
        }

        foreach (['current_password', 'new_password'] as $required) {
            if (! array_key_exists($required, $payload)) {
                throw new InvalidArgumentException('Password change request is invalid.');
            }
        }
    }

    /** @return array{0:TenantId,1:PlatformIdentityId,2:int} */
    private function fullSessionContext(Request $request): array
    {
        $session = $request->session();

        foreach (array_merge(FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery()) as $key) {
            if ($session->has($key)) {
                throw new InvalidArgumentException('Password change session is invalid.');
            }
        }

        $tenant = $session->get(FirstPartySessionKeys::TENANT);
        $identity = $session->get(FirstPartySessionKeys::IDENTITY);
        $organization = $session->get(FirstPartySessionKeys::ORGANIZATION);
        $credentialEpoch = $session->get(FirstPartySessionKeys::CREDENTIAL_EPOCH);

        if (! is_string($tenant) || trim($tenant) === ''
            || ! is_string($identity) || trim($identity) === ''
            || ! is_string($organization) || trim($organization) === ''
            || ! is_int($credentialEpoch) || $credentialEpoch < 0) {
            throw new InvalidArgumentException('Password change session is invalid.');
        }

        foreach ([FirstPartySessionKeys::OUTLET, FirstPartySessionKeys::DEVICE] as $optional) {
            if ($session->has($optional)) {
                $value = $session->get($optional);
                if (! is_string($value) || trim($value) === '') {
                    throw new InvalidArgumentException('Password change session is invalid.');
                }
            }
        }

        return [
            TenantId::fromString(trim($tenant)),
            PlatformIdentityId::fromString(trim($identity)),
            $credentialEpoch,
        ];
    }

    private function requireAllowedRuntime(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless(in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }

    private function failed(string $correlationId): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('PASSWORD_CHANGE_FAILED', $correlationId),
            401,
            ['Cache-Control' => 'no-store, private'],
        );
    }
}
