<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface InventoryBaselineRepository
{
    public function establish(
        PosExecutionContext $context,
        InventoryBaselineCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): InventoryBaselineResult;
}
