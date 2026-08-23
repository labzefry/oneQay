<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

// Author by Lab | zefry
final class FirstPartySessionControlController
{
    public function __construct(
        private readonly FirstPartySessionAuthorityService $sessionAuthorities,
    ) {}

    public function inventory(Request $request): JsonResponse
    {
        $correlationId = $this->correlationId($request);
        try {
            [$tenantId, $identityId, $authorityId, $organizationId, $outletId, $deviceId, $credentialEpoch, $factorEpoch] = $this->sessionContext($request);
            $items = $this->sessionAuthorities->inventory(
                $tenantId,
                $identityId,
                $authorityId,
                $organizationId,
                $outletId,
                $deviceId,
                $credentialEpoch,
                $factorEpoch,
            );

            return response()->json([
                'sessions' => array_map(static fn ($item): array => $item->toArray(), $items),
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|FirstPartySessionAuthorityViolation) {
            return $this->denied($correlationId);
        }
    }

    public function revokeOne(Request $request, string $publicHandle): Response
    {
        $correlationId = $this->correlationId($request);
        try {
            $this->assertNoPayload($request);
            [$tenantId, $identityId, $authorityId, $organizationId, $outletId, $deviceId, $credentialEpoch, $factorEpoch] = $this->sessionContext($request);
            $this->sessionAuthorities->revokeOne(
                $tenantId,
                $identityId,
                $authorityId,
                $publicHandle,
                $organizationId,
                $outletId,
                $deviceId,
                $credentialEpoch,
                $factorEpoch,
                $correlationId,
            );

            return response()->noContent(204, ['Cache-Control' => 'no-store, private']);
        } catch (FirstPartySessionAuthorityViolation $exception) {
            if ($exception->errorCode === FirstPartySessionAuthorityViolation::CURRENT_SESSION_TARGET) {
                return response()->json(
                    SafeErrorEnvelope::make('CURRENT_SESSION_REQUIRES_LOGOUT', $correlationId),
                    409,
                    ['Cache-Control' => 'no-store, private'],
                );
            }
            return $this->denied($correlationId);
        } catch (InvalidArgumentException) {
            return $this->denied($correlationId);
        }
    }

    public function revokeOthers(Request $request): Response
    {
        $correlationId = $this->correlationId($request);
        try {
            $this->assertNoPayload($request);
            [$tenantId, $identityId, $authorityId, $organizationId, $outletId, $deviceId, $credentialEpoch, $factorEpoch] = $this->sessionContext($request);
            $this->sessionAuthorities->revokeOthers(
                $tenantId,
                $identityId,
                $authorityId,
                $organizationId,
                $outletId,
                $deviceId,
                $credentialEpoch,
                $factorEpoch,
                $correlationId,
            );

            return response()->noContent(204, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|FirstPartySessionAuthorityViolation) {
            return $this->denied($correlationId);
        }
    }

    public function revokeAll(Request $request): Response
    {
        $correlationId = $this->correlationId($request);
        try {
            $this->assertNoPayload($request);
            [$tenantId, $identityId, $authorityId, $organizationId, $outletId, $deviceId, $credentialEpoch, $factorEpoch] = $this->sessionContext($request);
            $this->sessionAuthorities->revokeAll(
                $tenantId,
                $identityId,
                $authorityId,
                $organizationId,
                $outletId,
                $deviceId,
                $credentialEpoch,
                $factorEpoch,
                $correlationId,
            );

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->noContent(204, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException|FirstPartySessionAuthorityViolation) {
            return $this->denied($correlationId);
        }
    }

    /** @return array{0:TenantId,1:PlatformIdentityId,2:string,3:string,4:?string,5:?string,6:int,7:?int} */
    private function sessionContext(Request $request): array
    {
        $session = $request->session();
        foreach (array_merge(FirstPartySessionKeys::pending(), FirstPartySessionKeys::recovery(), FirstPartySessionKeys::totpRecovery()) as $restrictedKey) {
            if ($session->has($restrictedKey)) {
                throw new InvalidArgumentException('Session-control context is invalid.');
            }
        }

        $tenant = $this->requiredSessionString($request, FirstPartySessionKeys::TENANT);
        $identity = $this->requiredSessionString($request, FirstPartySessionKeys::IDENTITY);
        $authority = $this->requiredSessionString($request, FirstPartySessionKeys::SESSION_AUTHORITY_ID);
        $organization = $this->requiredSessionString($request, FirstPartySessionKeys::ORGANIZATION);
        $outlet = $this->optionalSessionString($request, FirstPartySessionKeys::OUTLET);
        $device = $this->optionalSessionString($request, FirstPartySessionKeys::DEVICE);
        $credentialEpoch = $session->get(FirstPartySessionKeys::CREDENTIAL_EPOCH);
        $factorEpoch = $session->get(FirstPartySessionKeys::MFA_FACTOR_EPOCH);
        if (! is_int($credentialEpoch) || $credentialEpoch < 0
            || ($factorEpoch !== null && (! is_int($factorEpoch) || $factorEpoch < 0))) {
            throw new InvalidArgumentException('Session-control epoch evidence is invalid.');
        }

        return [
            TenantId::fromString($tenant),
            PlatformIdentityId::fromString($identity),
            $authority,
            $organization,
            $outlet,
            $device,
            $credentialEpoch,
            $factorEpoch,
        ];
    }

    private function requiredSessionString(Request $request, string $key): string
    {
        $value = $request->session()->get($key);
        if (! is_string($value) || $value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Session-control context is invalid.');
        }
        return $value;
    }

    private function optionalSessionString(Request $request, string $key): ?string
    {
        $value = $request->session()->get($key);
        if ($value === null) {
            return null;
        }
        if (! is_string($value) || $value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Session-control context is invalid.');
        }
        return $value;
    }

    private function assertNoPayload(Request $request): void
    {
        if ($request->except('_token') !== []) {
            throw new InvalidArgumentException('Session-control request payload is invalid.');
        }
    }

    private function correlationId(Request $request): string
    {
        return (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
    }

    private function denied(string $correlationId): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('SESSION_AUTHORITY_DENIED', $correlationId),
            401,
            ['Cache-Control' => 'no-store, private'],
        );
    }
}
