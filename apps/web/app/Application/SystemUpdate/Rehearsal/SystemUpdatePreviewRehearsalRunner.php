<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use Throwable;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewRehearsalRunner
{
    public function __construct(
        private SystemUpdatePreviewRehearsalDriver $driver,
        private SystemUpdatePreviewRehearsalEvidenceStore $evidenceStore,
    ) {
    }

    public function run(
        SystemUpdatePreviewRehearsalPlan $plan,
        SystemUpdatePreviewRehearsalAuthorization $authorization,
        int $nowUnix,
    ): SystemUpdatePreviewRehearsalOutcome {
        $startedAtUnix = $nowUnix;
        $phases = [];

        try {
            $this->assertExecutionGate($plan, $authorization, $nowUnix);

            $this->driver->preflight($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::PREFLIGHT;

            $this->driver->verifyRecoveryCheckpoint($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::RECOVERY_CHECKPOINT_VERIFIED;

            $this->driver->stageCandidate($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::CANDIDATE_STAGED;

            $this->driver->activateCandidate($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::CANDIDATE_ACTIVATED;

            $candidateHealth = $this->driver->verifyCandidateHealth($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::CANDIDATE_HEALTH_VERIFIED;

            $this->driver->rollbackToBaseline($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::ROLLBACK_EXERCISED;

            $baselineHealth = $this->driver->verifyBaselineHealth($plan);
            $phases[] = SystemUpdatePreviewRehearsalPhase::BASELINE_HEALTH_VERIFIED;

            if (! $baselineHealth->healthyFor($plan->baselineRelease())) {
                throw new SystemUpdateControlPlaneViolation('m76_rollback_health_failed');
            }

            $safeCode = $candidateHealth->healthyFor($plan->candidateRelease())
                ? 'deployment_and_rollback_rehearsed'
                : 'candidate_unhealthy_recovered';

            $phases[] = SystemUpdatePreviewRehearsalPhase::COMPLETED;
            $this->persist($plan, $phases, $startedAtUnix, $nowUnix, $safeCode);

            return new SystemUpdatePreviewRehearsalOutcome(
                SystemUpdatePreviewRehearsalPhase::COMPLETED,
                $plan->baselineRelease(),
                $safeCode,
            );
        } catch (SystemUpdateControlPlaneViolation $violation) {
            $phases[] = SystemUpdatePreviewRehearsalPhase::FAILED;
            $this->persist($plan, $phases, $startedAtUnix, $nowUnix, $violation->safeCode());
            throw $violation;
        } catch (Throwable) {
            $phases[] = SystemUpdatePreviewRehearsalPhase::FAILED;
            $this->persist($plan, $phases, $startedAtUnix, $nowUnix, 'm76_rehearsal_failure');
            throw new SystemUpdateControlPlaneViolation('m76_rehearsal_failure');
        }
    }

    private function assertExecutionGate(
        SystemUpdatePreviewRehearsalPlan $plan,
        SystemUpdatePreviewRehearsalAuthorization $authorization,
        int $nowUnix,
    ): void {
        if (! $plan->target()->qualified()) {
            throw new SystemUpdateControlPlaneViolation('m76_target_not_qualified');
        }

        if (! $plan->target()->syntheticOnly()) {
            throw new SystemUpdateControlPlaneViolation('m76_synthetic_preview_required');
        }

        if (! $authorization->isFreshAt($nowUnix)) {
            throw new SystemUpdateControlPlaneViolation('m76_deployment_authorization_stale');
        }

        if (! hash_equals(
            $plan->target()->fingerprint(),
            $authorization->targetQualificationFingerprint(),
        )) {
            throw new SystemUpdateControlPlaneViolation('m76_deployment_authorization_target_mismatch');
        }
    }

    /** @param list<SystemUpdatePreviewRehearsalPhase> $phases */
    private function persist(
        SystemUpdatePreviewRehearsalPlan $plan,
        array $phases,
        int $startedAtUnix,
        int $completedAtUnix,
        string $safeCode,
    ): void {
        $this->evidenceStore->persist(new SystemUpdatePreviewRehearsalEvidence(
            $plan,
            $phases,
            $startedAtUnix,
            $completedAtUnix,
            $safeCode,
        ));
    }
}
