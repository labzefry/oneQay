<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
enum SystemUpdateOperationState: string
{
    case IDLE = 'IDLE';
    case CHECKING = 'CHECKING';
    case AVAILABLE = 'AVAILABLE';
    case DOWNLOADING = 'DOWNLOADING';
    case VERIFYING = 'VERIFYING';
    case STAGED = 'STAGED';
    case PREFLIGHTING = 'PREFLIGHTING';
    case READY_TO_APPLY = 'READY_TO_APPLY';
    case APPLYING_SHARED_CONFIGURATION = 'APPLYING_SHARED_CONFIGURATION';
    case PREPARING_PUBLIC_SURFACE = 'PREPARING_PUBLIC_SURFACE';
    case SWITCHING = 'SWITCHING';
    case VERIFYING_HEALTH = 'VERIFYING_HEALTH';
    case SUCCEEDED = 'SUCCEEDED';
    case ROLLING_BACK = 'ROLLING_BACK';
    case ROLLED_BACK = 'ROLLED_BACK';
    case FAILED = 'FAILED';
}
