<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCashVarianceReconciliationWorkspaceData
{
    /**
     * @param list<array{
     *   closing_evidence_id:string,
     *   shift_id:string,
     *   device_id:string,
     *   closing_actor_identity_id:string,
     *   recorded_at_unix:int,
     *   has_explanation:bool,
     *   has_review:bool,
     *   review_outcome:?string
     * }> $cases
     * @param array{
     *   closing_evidence_id:string,
     *   shift_id:string,
     *   device_id:string,
     *   opener_actor_identity_id:string,
     *   closing_actor_identity_id:string,
     *   recorded_at_unix:int,
     *   expected_cash_atomic:int,
     *   observed_closing_cash_atomic:int,
     *   variance_atomic:int,
     *   variance_direction:string,
     *   currency:string,
     *   currency_scale:int,
     *   explanation:?array{evidence_id:string,actor_identity_id:string,text:string,recorded_at_unix:int},
     *   review:?array{review_evidence_id:string,reviewer_actor_identity_id:string,outcome:string,reviewed_at_unix:int}
     * }|null $selected
     */
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $actorId,
        private array $cases,
        private ?array $selected,
    ) {
        foreach ([$tenantId, $organizationId, $outletId, $actorId] as $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException('Cash variance reconciliation scope is invalid.');
            }
        }
    }

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function actorId(): string { return $this->actorId; }

    /** @return list<array<string, mixed>> */
    public function cases(): array { return $this->cases; }

    /** @return array<string, mixed>|null */
    public function selected(): ?array { return $this->selected; }
}
