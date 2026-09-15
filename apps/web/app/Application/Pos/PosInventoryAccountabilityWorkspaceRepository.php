<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosInventoryAccountabilityWorkspaceRepository
{
    public function read(PosExecutionContext $context): PosInventoryAccountabilityWorkspaceSnapshot;
}
