<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateOperationStateStore
{
    public function currentState(): SystemUpdateOperationState;

    public function activeOperationId(): ?string;
}
