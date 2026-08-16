<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewRehearsalEvidence
{
    /** @param list<SystemUpdatePreviewRehearsalPhase> $phases */
    public function __construct(
        private SystemUpdatePreviewRehearsalPlan $plan,
        private array $phases,
        private int $startedAtUnix,
        private int $completedAtUnix,
        private string $outcomeCode,
    ) {
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'evidence_version' => 1,
            'operation_id' => $this->plan->operationId(),
            'target' => [
                'target_id' => $this->plan->target()->targetId(),
                'qualification_evidence_id' => $this->plan->target()->evidenceId(),
                'qualification_fingerprint' => $this->plan->target()->fingerprint(),
                'runtime_class' => 'preview',
                'synthetic_only' => true,
                'production' => false,
            ],
            'baseline_release' => [
                'release_id' => $this->plan->baselineRelease()->releaseId(),
                'source_commit' => $this->plan->baselineRelease()->sourceCommit(),
            ],
            'candidate_release' => [
                'release_id' => $this->plan->candidateRelease()->releaseId(),
                'source_commit' => $this->plan->candidateRelease()->sourceCommit(),
            ],
            'migration_classification' => $this->plan->migrationClassification(),
            'phases' => array_map(
                static fn (SystemUpdatePreviewRehearsalPhase $phase): string => $phase->value,
                $this->phases,
            ),
            'started_at_unix' => $this->startedAtUnix,
            'completed_at_unix' => $this->completedAtUnix,
            'outcome_code' => $this->outcomeCode,
        ];
    }
}
