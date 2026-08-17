<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewRehearsalPlan
{
    public function __construct(
        private string $operationId,
        private SystemUpdatePreviewTargetQualification $target,
        private SystemUpdateReleaseIdentity $baselineRelease,
        private SystemUpdateReleaseIdentity $candidateRelease,
        private string $migrationClassification = SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
    ) {
        if (preg_match('/\Am76-op-[0-9a-f]{16}\z/', $operationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_operation_id');
        }

        if ($baselineRelease->equals($candidateRelease)) {
            throw new SystemUpdateControlPlaneViolation('m76_candidate_must_differ_from_baseline');
        }

        if ($migrationClassification !== SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION) {
            throw new SystemUpdateControlPlaneViolation('schema_change_not_supported');
        }
    }

    public function operationId(): string { return $this->operationId; }
    public function target(): SystemUpdatePreviewTargetQualification { return $this->target; }
    public function baselineRelease(): SystemUpdateReleaseIdentity { return $this->baselineRelease; }
    public function candidateRelease(): SystemUpdateReleaseIdentity { return $this->candidateRelease; }
    public function migrationClassification(): string { return $this->migrationClassification; }
}
