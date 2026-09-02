<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class VoidSale
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private DurablePosSaleRepository $sales,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private PosSaleClock $clock,
    ) {}

    public function execute(
        SaleVoidCommand $command,
        string $correlationId,
    ): SaleVoidResult {
        if (preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PosPermission::voidSale());

        $voidedAtUnix = $this->clock->nowUnix();
        if ($voidedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): SaleVoidResult => $this->sales->voidCompletedSale(
                $context,
                $command,
                $correlationId,
                $voidedAtUnix,
            ),
        );
    }
}
