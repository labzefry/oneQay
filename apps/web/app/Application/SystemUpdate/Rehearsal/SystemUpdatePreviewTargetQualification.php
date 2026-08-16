<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewTargetQualification
{
    public function __construct(
        private string $targetId,
        private string $evidenceId,
        private string $fingerprint,
        private bool $qualified,
        private bool $syntheticOnly,
        private string $runtimeClass = 'preview',
    ) {
        if (preg_match('/\Apreview-target-[a-z0-9-]{1,48}\z/', $targetId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_target_id');
        }

        if (preg_match('/\Am75-evidence-[0-9a-f]{16}\z/', $evidenceId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_qualification_evidence_id');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/', $fingerprint) !== 1) {
            throw new SystemUpdateControlPlaneViolation('m76_invalid_target_fingerprint');
        }

        if ($runtimeClass !== 'preview') {
            throw new SystemUpdateControlPlaneViolation('m76_preview_runtime_required');
        }
    }

    public function targetId(): string { return $this->targetId; }
    public function evidenceId(): string { return $this->evidenceId; }
    public function fingerprint(): string { return $this->fingerprint; }
    public function qualified(): bool { return $this->qualified; }
    public function syntheticOnly(): bool { return $this->syntheticOnly; }
}
