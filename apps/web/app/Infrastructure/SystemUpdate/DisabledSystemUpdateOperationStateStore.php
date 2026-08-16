<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateOperationState;
use App\Application\SystemUpdate\SystemUpdateOperationStateStore;

// Author by Lab | zefry
final class DisabledSystemUpdateOperationStateStore implements SystemUpdateOperationStateStore
{
    public function currentState(): SystemUpdateOperationState
    {
        return SystemUpdateOperationState::IDLE;
    }

    public function activeOperationId(): ?string
    {
        return null;
    }
}
