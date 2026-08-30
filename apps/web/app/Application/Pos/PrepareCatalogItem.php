<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;

// Author by Lab | zefry
final readonly class PrepareCatalogItem
{
    public function __construct(
        private CatalogPreparationRepository $catalog,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private CatalogPreparationClock $clock,
    ) {}

    public function prepare(CatalogPreparationCommand $command): CatalogPreparationResult
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PosPermission::prepareCatalog());

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): CatalogPreparationResult => $this->catalog->prepare($context, $command, $occurredAtUnix),
        );
    }
}
