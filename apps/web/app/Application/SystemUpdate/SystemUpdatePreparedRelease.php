<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdatePreparedRelease
{
    public const MIGRATION_CLASSIFICATION = 'NO_SCHEMA_CHANGE';
    public const ROLLBACK_COMPATIBILITY = 'APPLICATION_POINTER_ROLLBACK_COMPATIBLE';

    public function __construct(
        private string $operationId,
        private SystemUpdateReleaseIdentity $identity,
        private string $migrationClassification,
        private string $rollbackCompatibility,
        private bool $activationEligible,
    ) {
        if (preg_match('/\Aop-[0-9a-f]{16}\z/', $operationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_operation_id');
        }

        if ($migrationClassification !== self::MIGRATION_CLASSIFICATION) {
            throw new SystemUpdateControlPlaneViolation('schema_change_not_supported');
        }

        if ($rollbackCompatibility !== self::ROLLBACK_COMPATIBILITY) {
            throw new SystemUpdateControlPlaneViolation('rollback_not_compatible');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function identity(): SystemUpdateReleaseIdentity
    {
        return $this->identity;
    }

    public function migrationClassification(): string
    {
        return $this->migrationClassification;
    }

    public function rollbackCompatibility(): string
    {
        return $this->rollbackCompatibility;
    }

    public function activationEligible(): bool
    {
        return $this->activationEligible;
    }
}
