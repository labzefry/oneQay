<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RecordShiftOpeningCash
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private ShiftOpeningCashRepository $evidence,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private ShiftOpeningClock $clock,
    ) {}

    public function record(
        ShiftOpeningCashCommand $command,
        string $correlationId,
    ): ShiftOpeningCashResult {
        if (preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);
        $this->authorization->require($verified, PosPermission::recordShiftOpeningCash());

        $recordedAtUnix = $this->clock->nowUnix();
        if ($recordedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): ShiftOpeningCashResult => $this->evidence->record(
                $context,
                $command,
                $correlationId,
                $recordedAtUnix,
            ),
        );
    }
}
