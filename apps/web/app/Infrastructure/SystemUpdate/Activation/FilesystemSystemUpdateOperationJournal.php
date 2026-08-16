<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Activation;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateHealthResult;
use App\Application\SystemUpdate\SystemUpdateOperationJournal;
use App\Application\SystemUpdate\SystemUpdateOperationState;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;

// Author by Lab | zefry
final class FilesystemSystemUpdateOperationJournal implements SystemUpdateOperationJournal
{
    public function __construct(
        private readonly string $privateRoot,
        private readonly SystemUpdateAtomicJsonFile $json,
    ) {
    }

    public function begin(
        SystemUpdatePreparedRelease $release,
        string $actorIdentityRef,
        SystemUpdateReleaseIdentity $previousStable,
        int $nowUnix,
    ): void {
        $path = $this->operationPath($release->operationId());

        if (is_file($path)) {
            throw new SystemUpdateControlPlaneViolation('operation_already_exists');
        }

        $this->json->write($path, [
            'journal_version' => 1,
            'operation_id' => $release->operationId(),
            'actor_identity_ref' => $actorIdentityRef,
            'release' => $release->identity()->toSafeArray(),
            'previous_stable' => $previousStable->toSafeArray(),
            'migration_classification' => $release->migrationClassification(),
            'rollback_compatibility' => $release->rollbackCompatibility(),
            'state' => SystemUpdateOperationState::STAGED->value,
            'started_at_unix' => $nowUnix,
            'updated_at_unix' => $nowUnix,
            'safe_failure_code' => null,
            'health_checks' => [],
            'transitions' => [],
            'attribution' => 'Lab | zefry',
        ]);
    }

    public function transition(
        string $operationId,
        SystemUpdateOperationState $from,
        SystemUpdateOperationState $to,
        int $nowUnix,
        ?string $safeFailureCode = null,
    ): void {
        $payload = $this->requireOperation($operationId);

        if (($payload['state'] ?? null) !== $from->value) {
            throw new SystemUpdateControlPlaneViolation('operation_state_compare_failed');
        }

        if ($safeFailureCode !== null && preg_match('/\A[a-z0-9_]{3,64}\z/', $safeFailureCode) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_operation_failure_code');
        }

        $transitions = $payload['transitions'] ?? [];
        if (! is_array($transitions)) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_malformed');
        }

        $transitions[] = [
            'from' => $from->value,
            'to' => $to->value,
            'at_unix' => $nowUnix,
            'safe_code' => $safeFailureCode,
        ];

        $payload['state'] = $to->value;
        $payload['updated_at_unix'] = $nowUnix;
        $payload['safe_failure_code'] = $to === SystemUpdateOperationState::FAILED ? $safeFailureCode : null;
        $payload['transitions'] = $transitions;

        $this->json->write($this->operationPath($operationId), $payload);
    }

    public function recordHealth(
        string $operationId,
        SystemUpdateReleaseIdentity $release,
        SystemUpdateHealthResult $result,
        int $nowUnix,
    ): void {
        $payload = $this->requireOperation($operationId);
        $healthChecks = $payload['health_checks'] ?? [];

        if (! is_array($healthChecks)) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_malformed');
        }

        $healthChecks[] = [
            'release_id' => $release->releaseId(),
            'healthy_for_expected_release' => $result->healthyFor($release),
            'observed_release_id' => $result->observedReleaseId(),
            'safe_code' => $result->safeCode(),
            'checked_at_unix' => $nowUnix,
        ];

        $payload['health_checks'] = $healthChecks;
        $payload['updated_at_unix'] = $nowUnix;

        $this->json->write($this->operationPath($operationId), $payload);
    }

    public function currentState(string $operationId): SystemUpdateOperationState
    {
        $payload = $this->requireOperation($operationId);
        $state = $payload['state'] ?? null;

        if (! is_string($state)) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_malformed');
        }

        $resolved = SystemUpdateOperationState::tryFrom($state);
        if ($resolved === null) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_malformed');
        }

        return $resolved;
    }

    /** @return array<string, mixed> */
    private function requireOperation(string $operationId): array
    {
        if (preg_match('/\Aop-[0-9a-f]{16}\z/', $operationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_operation_id');
        }

        $payload = $this->json->read($this->operationPath($operationId));
        if ($payload === null || ($payload['journal_version'] ?? null) !== 1) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_missing');
        }

        if (($payload['operation_id'] ?? null) !== $operationId) {
            throw new SystemUpdateControlPlaneViolation('operation_journal_malformed');
        }

        return $payload;
    }

    private function operationPath(string $operationId): string
    {
        return rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/deployment-state/operations/'.$operationId.'.json';
    }
}
