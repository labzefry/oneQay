<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PrepareCatalogItem
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private CatalogPreparationRepository $catalog,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private CatalogPreparationClock $clock,
    ) {}

    public function prepare(
        CatalogPreparationCommand $command,
        string $correlationId,
    ): CatalogPreparationResult {
        if (preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PosPermission::prepareCatalog());

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): CatalogPreparationResult => $this->catalog->prepare(
                $context,
                $command,
                $correlationId,
                $occurredAtUnix,
            ),
        );
    }
}

// Sprint48 JRN-005 Sprint47 JRN-006 compatibility preservation anchor.
