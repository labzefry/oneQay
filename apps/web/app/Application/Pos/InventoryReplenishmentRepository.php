<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface InventoryReplenishmentRepository
{
    public function replenish(
        PosExecutionContext $context,
        InventoryReplenishmentCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): InventoryReplenishmentResult;
}
