<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CloseShift
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private CloseShiftRepository $shifts,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private ShiftCloseClock $clock,
    ) {}

    public function close(CloseShiftCommand $command, string $correlationId): CloseShiftResult
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, FinalShiftClosePermission::identifier());

        $closedAtUnix = $this->clock->nowUnix();
        if ($closedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): CloseShiftResult => $this->shifts->close(
                $context,
                $command,
                $correlationId,
                $closedAtUnix,
            ),
        );
    }
}
