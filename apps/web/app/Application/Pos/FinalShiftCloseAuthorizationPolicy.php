<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final class FinalShiftCloseAuthorizationPolicy
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function requireAuthorizedActors(
        string $closerActorId,
        string $openerActorId,
        bool $nonZeroVariance,
        ?string $explanationAuthorActorId = null,
        ?string $reviewerActorId = null,
    ): void {
        $this->assertActorId($closerActorId);
        $this->assertActorId($openerActorId);

        if (hash_equals($closerActorId, $openerActorId)) {
            throw new PosTransactionViolation();
        }

        if (! $nonZeroVariance) {
            return;
        }

        if ($explanationAuthorActorId === null || $reviewerActorId === null) {
            throw new PosTransactionViolation();
        }

        $this->assertActorId($explanationAuthorActorId);
        $this->assertActorId($reviewerActorId);

        if (
            hash_equals($closerActorId, $explanationAuthorActorId)
            || hash_equals($closerActorId, $reviewerActorId)
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertActorId(string $actorId): void
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $actorId) !== 1) {
            throw new PosTransactionViolation();
        }
    }
}
