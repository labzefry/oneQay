<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\InitialPasswordEnrollmentId;
use App\Application\Identity\InitialPasswordEnrollmentService;
use App\Application\Identity\InitialPasswordEnrollmentViolation;
use App\Application\Organization\OrganizationalContextStore;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class InitialPasswordEnrollmentController
{
    private const ISSUE_FIELDS = ['enrollment_id', 'target_identity_id'];
    private const REDEEM_FIELDS = ['tenant_id', 'identity_id', 'enrollment_id', 'enrollment_token', 'password'];

    public function __construct(
        private readonly OrganizationalContextStore $contexts,
        private readonly InitialPasswordEnrollmentService $service,
    ) {}

    public function issue(Request $request): JsonResponse
    {
        $this->requireAllowedRuntime();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');
        $actor = $this->contexts->current();

        if ($actor === null) {
            return $this->issueRejected($correlationId, 403);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload, self::ISSUE_FIELDS);

            $enrollmentId = InitialPasswordEnrollmentId::fromString(
                $this->requiredTrimmedString($payload, 'enrollment_id'),
            );
            $targetIdentityId = PlatformIdentityId::fromString(
                $this->requiredTrimmedString($payload, 'target_identity_id'),
            );

            $issued = $this->service->issue($actor, $targetIdentityId, $enrollmentId);

            return response()->json([
                'status' => 'ok',
                'enrollment_id' => $issued->enrollmentId()->value(),
                'target_identity_id' => $issued->targetIdentityId()->value(),
                'expires_at_unix' => $issued->expiresAtUnix(),
                'enrollment_token' => $issued->enrollmentToken(),
                'correlation_id' => $correlationId,
            ], 201, $this->noStoreHeaders());
        } catch (InvalidArgumentException) {
            return $this->issueRejected($correlationId, 422);
        } catch (InitialPasswordEnrollmentViolation $exception) {
            $status = match ($exception->errorCode) {
                InitialPasswordEnrollmentViolation::AUTHORIZATION_DENIED,
                InitialPasswordEnrollmentViolation::SELF_ENROLLMENT_DENIED => 403,
                InitialPasswordEnrollmentViolation::PERSISTENCE_DISABLED,
                InitialPasswordEnrollmentViolation::RUNTIME_DENIED,
                InitialPasswordEnrollmentViolation::STORAGE_FAILURE,
                InitialPasswordEnrollmentViolation::TRANSACTION_FAILURE => 503,
                default => 409,
            };

            return $this->issueRejected($correlationId, $status);
        }
    }

    public function redeem(Request $request): JsonResponse
    {
        $this->requireAllowedRuntime();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload, self::REDEEM_FIELDS);

            $tenantId = TenantId::fromString($this->requiredTrimmedString($payload, 'tenant_id'));
            $identityId = PlatformIdentityId::fromString($this->requiredTrimmedString($payload, 'identity_id'));
            $enrollmentId = InitialPasswordEnrollmentId::fromString(
                $this->requiredTrimmedString($payload, 'enrollment_id'),
            );
            $enrollmentToken = $payload['enrollment_token'] ?? null;
            $password = $payload['password'] ?? null;

            if (! is_string($enrollmentToken) || ! is_string($password)) {
                throw new InvalidArgumentException('Initial password enrollment request is invalid.');
            }

            $outcome = $this->service->redeem(
                $tenantId,
                $identityId,
                $enrollmentId,
                $enrollmentToken,
                $password,
            );

            return response()->json([
                'status' => 'ok',
                'outcome' => $outcome,
                'correlation_id' => $correlationId,
            ], 200, $this->noStoreHeaders());
        } catch (InvalidArgumentException|InitialPasswordEnrollmentViolation) {
            return response()->json(
                SafeErrorEnvelope::make('INITIAL_PASSWORD_ENROLLMENT_FAILED', $correlationId),
                401,
                $this->noStoreHeaders(),
            );
        }
    }

    /** @param array<string, mixed> $payload @param list<string> $allowed */
    private function assertClosedPayload(array $payload, array $allowed): void
    {
        foreach (array_keys($payload) as $key) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Initial password enrollment request is invalid.');
            }
        }

        foreach ($allowed as $required) {
            if (! array_key_exists($required, $payload)) {
                throw new InvalidArgumentException('Initial password enrollment request is invalid.');
            }
        }
    }

    /** @param array<string, mixed> $payload */
    private function requiredTrimmedString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Initial password enrollment request is invalid.');
        }

        return trim($value);
    }

    /** @return array<string, string> */
    private function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ];
    }

    private function issueRejected(string $correlationId, int $status): JsonResponse
    {
        return response()->json(
            SafeErrorEnvelope::make('INITIAL_PASSWORD_ENROLLMENT_ISSUE_REJECTED', $correlationId),
            $status,
            $this->noStoreHeaders(),
        );
    }

    private function requireAllowedRuntime(): void
    {
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        abort_unless(in_array($runtime, ['local', 'test', 'ci'], true), 404);
    }
}
