<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;
use App\Application\SystemUpdate\Security\PrivilegedUpdateSecurityPolicy;
use Throwable;

// Author by Lab | zefry
final readonly class SystemUpdateActivationCoordinator
{
    private const LOCK_LEASE_SECONDS = 300;

    public function __construct(
        private SystemUpdateFeatureGate $featureGate,
        private SystemUpdateStateMachine $stateMachine,
        private SystemUpdateReleaseStore $releaseStore,
        private SystemUpdateActiveReleasePointerStore $pointerStore,
        private SystemUpdateDeploymentLockManager $lockManager,
        private SystemUpdateOperationJournal $journal,
        private SystemUpdateHealthVerifier $healthVerifier,
    ) {
    }

    public function activate(
        SystemUpdatePreparedRelease $release,
        PrivilegedUpdateAuthorization $authorization,
        int $nowUnix,
    ): SystemUpdateActivationResult {
        $this->assertActivationAuthority($release, $authorization, $nowUnix);

        $currentPointer = $this->pointerStore->current();
        if ($currentPointer === null) {
            throw new SystemUpdateControlPlaneViolation('rollback_target_required');
        }

        $previousStable = $currentPointer->active();
        if ($previousStable->equals($release->identity())) {
            throw new SystemUpdateControlPlaneViolation('release_already_active');
        }

        $lock = $this->lockManager->acquire(
            $release->operationId(),
            $authorization->identityId()->value(),
            $nowUnix,
            self::LOCK_LEASE_SECONDS,
        );

        $pointerSwitched = false;
        $state = SystemUpdateOperationState::STAGED;

        try {
            $this->releaseStore->commitStagedRelease($release);
            $this->releaseStore->assertReleaseReady($release->identity());

            $this->journal->begin(
                $release,
                $authorization->identityId()->value(),
                $previousStable,
                $nowUnix,
            );

            $state = $this->move($release->operationId(), $state, SystemUpdateOperationState::PREFLIGHTING, $nowUnix);
            $this->releaseStore->assertReleaseReady($release->identity());

            $state = $this->move($release->operationId(), $state, SystemUpdateOperationState::READY_TO_APPLY, $nowUnix);

            // Stage 6 remains separate: only NO_CHANGE_REQUIRED is permitted here.
            $state = $this->move(
                $release->operationId(),
                $state,
                SystemUpdateOperationState::APPLYING_SHARED_CONFIGURATION,
                $nowUnix,
            );

            // Stable public bootstrap deployment is not mutated by this foundation.
            $state = $this->move(
                $release->operationId(),
                $state,
                SystemUpdateOperationState::PREPARING_PUBLIC_SURFACE,
                $nowUnix,
            );

            $state = $this->move($release->operationId(), $state, SystemUpdateOperationState::SWITCHING, $nowUnix);
            $this->pointerStore->switchTo($release->identity(), $previousStable, $nowUnix);
            $pointerSwitched = true;

            $state = $this->move(
                $release->operationId(),
                $state,
                SystemUpdateOperationState::VERIFYING_HEALTH,
                $nowUnix,
            );

            $health = $this->healthVerifier->verify($release->identity());
            $this->journal->recordHealth($release->operationId(), $release->identity(), $health, $nowUnix);

            if ($health->healthyFor($release->identity())) {
                $state = $this->move($release->operationId(), $state, SystemUpdateOperationState::SUCCEEDED, $nowUnix);
                $this->lockManager->release($lock);

                return new SystemUpdateActivationResult($state, $release->identity(), 'activation_healthy');
            }

            $state = $this->move(
                $release->operationId(),
                $state,
                SystemUpdateOperationState::ROLLING_BACK,
                $nowUnix,
                $health->safeCode(),
            );

            $this->pointerStore->restorePrevious($previousStable, $release->identity(), $nowUnix);

            $rollbackHealth = $this->healthVerifier->verify($previousStable);
            $this->journal->recordHealth($release->operationId(), $previousStable, $rollbackHealth, $nowUnix);

            if ($rollbackHealth->healthyFor($previousStable)) {
                $state = $this->move(
                    $release->operationId(),
                    $state,
                    SystemUpdateOperationState::ROLLED_BACK,
                    $nowUnix,
                    'new_release_unhealthy',
                );
                $this->lockManager->release($lock);

                return new SystemUpdateActivationResult($state, $previousStable, 'automatic_application_rollback');
            }

            $this->move(
                $release->operationId(),
                $state,
                SystemUpdateOperationState::FAILED,
                $nowUnix,
                'rollback_health_failed',
            );

            // Retain the lock until lease expiry. Any later attempt must reconcile the stale lock
            // and persisted FAILED operation instead of silently starting a second deployment.
            throw new SystemUpdateControlPlaneViolation('rollback_health_failed');
        } catch (SystemUpdateControlPlaneViolation $violation) {
            if (! $pointerSwitched) {
                $this->lockManager->release($lock);
            }

            throw $violation;
        } catch (Throwable) {
            if (! $pointerSwitched) {
                $this->lockManager->release($lock);
            }

            throw new SystemUpdateControlPlaneViolation('activation_foundation_failure');
        }
    }

    private function assertActivationAuthority(
        SystemUpdatePreparedRelease $release,
        PrivilegedUpdateAuthorization $authorization,
        int $nowUnix,
    ): void {
        if (! $this->featureGate->controlPlaneEnabled()) {
            throw new SystemUpdateControlPlaneViolation('control_plane_disabled');
        }

        if (! $this->featureGate->installEnabled()) {
            throw new SystemUpdateControlPlaneViolation('install_disabled');
        }

        if ($authorization->capability() !== PrivilegedUpdateCapability::INSTALL) {
            throw new SystemUpdateControlPlaneViolation('privileged_authorization_required');
        }

        if (! PrivilegedUpdateSecurityPolicy::timestampIsFresh(
            $authorization->authorizedAtUnix(),
            $nowUnix,
            PrivilegedUpdateSecurityPolicy::STEP_UP_MAX_AGE_SECONDS,
        )) {
            throw new SystemUpdateControlPlaneViolation('privileged_authorization_stale');
        }

        if (! $release->activationEligible()) {
            throw new SystemUpdateControlPlaneViolation('release_activation_not_eligible');
        }

        if ($release->migrationClassification() !== SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION) {
            throw new SystemUpdateControlPlaneViolation('schema_change_not_supported');
        }

        if ($release->rollbackCompatibility() !== SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY) {
            throw new SystemUpdateControlPlaneViolation('rollback_not_compatible');
        }
    }

    private function move(
        string $operationId,
        SystemUpdateOperationState $from,
        SystemUpdateOperationState $to,
        int $nowUnix,
        ?string $safeFailureCode = null,
    ): SystemUpdateOperationState {
        $this->stateMachine->assertAllowed($from, $to);
        $this->journal->transition($operationId, $from, $to, $nowUnix, $safeFailureCode);

        return $to;
    }
}
