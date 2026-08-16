<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

// Author by Lab | zefry
enum SystemUpdatePreviewRehearsalPhase: string
{
    case PREFLIGHT = 'PREFLIGHT';
    case RECOVERY_CHECKPOINT_VERIFIED = 'RECOVERY_CHECKPOINT_VERIFIED';
    case CANDIDATE_STAGED = 'CANDIDATE_STAGED';
    case CANDIDATE_ACTIVATED = 'CANDIDATE_ACTIVATED';
    case CANDIDATE_HEALTH_VERIFIED = 'CANDIDATE_HEALTH_VERIFIED';
    case ROLLBACK_EXERCISED = 'ROLLBACK_EXERCISED';
    case BASELINE_HEALTH_VERIFIED = 'BASELINE_HEALTH_VERIFIED';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
}
