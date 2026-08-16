<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateHealthResult;

// Author by Lab | zefry
interface SystemUpdatePreviewRehearsalDriver
{
    public function preflight(SystemUpdatePreviewRehearsalPlan $plan): void;

    public function verifyRecoveryCheckpoint(SystemUpdatePreviewRehearsalPlan $plan): void;

    public function stageCandidate(SystemUpdatePreviewRehearsalPlan $plan): void;

    public function activateCandidate(SystemUpdatePreviewRehearsalPlan $plan): void;

    public function verifyCandidateHealth(SystemUpdatePreviewRehearsalPlan $plan): SystemUpdateHealthResult;

    public function rollbackToBaseline(SystemUpdatePreviewRehearsalPlan $plan): void;

    public function verifyBaselineHealth(SystemUpdatePreviewRehearsalPlan $plan): SystemUpdateHealthResult;
}
