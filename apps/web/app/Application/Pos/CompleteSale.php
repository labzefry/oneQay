<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Pos\SaleReceipt;

// Author by Lab | zefry
final readonly class CompleteSale
{
    public function __construct(
        private DurablePosSaleRepository $sales,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private PosSaleClock $clock,
    ) {}

    public function complete(SaleCommand $command): SaleReceipt
    {
        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PosPermission::completeSale());

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): SaleReceipt => $this->sales->complete($context, $command, $occurredAtUnix),
        );
    }
}
