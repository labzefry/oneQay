<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface CatalogPreparationRepository
{
    public function prepare(
        PosExecutionContext $context,
        CatalogPreparationCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): CatalogPreparationResult;
}
