<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

use OneQay\PhysicalMapping\PhysicalMappingManifest;

interface PhysicalSchemaPlanner
{
    public function plan(
        PhysicalMappingManifest $baseline,
        PhysicalMappingManifest $target,
        CorrelationId|string $correlationId,
    ): PhysicalSchemaPlan;
}

final readonly class PhysicalSchemaChange implements \JsonSerializable
{
    public string $entityIdentifier;
    public ?string $componentIdentifier;

    public function __construct(
        public StableChangeIdentifier $identifier,
        public SchemaChangeKind $kind,
        public ChangeRisk $risk,
        string $entityIdentifier,
        ?string $componentIdentifier,
        public ?ManifestFingerprint $beforeFingerprint,
        public ?ManifestFingerprint $afterFingerprint,
    ) {
        $this->entityIdentifier = self::assertSafeIdentifier($entityIdentifier);
        $this->componentIdentifier = $componentIdentifier === null
            ? null
            : self::assertSafeIdentifier($componentIdentifier);
    }

    public function sortKey(): string
    {
        return implode('|', [
            $this->entityIdentifier,
            $this->kind->value,
            $this->componentIdentifier ?? '',
            $this->identifier->value,
        ]);
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'change_id' => $this->identifier->value,
            'kind' => $this->kind->value,
            'risk' => $this->risk->value,
            'entity_identifier' => $this->entityIdentifier,
            'component_identifier' => $this->componentIdentifier,
            'before_fingerprint' => $this->beforeFingerprint?->value,
            'after_fingerprint' => $this->afterFingerprint?->value,
        ];
    }

    private static function assertSafeIdentifier(string $value): string
    {
        $normalized = trim($value);
        if ($normalized === ''
            || strlen($normalized) > 64
            || preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $normalized) !== 1) {
            throw new SchemaPlanningException(
                SchemaPlanningException::SAFE_IDENTIFIER_INVALID,
                'Schema planning identifier is invalid.'
            );
        }
        return $normalized;
    }
}

final readonly class PhysicalSchemaPlan implements \JsonSerializable
{
    /** @var list<PhysicalSchemaChange> */
    private array $changes;

    /** @param list<PhysicalSchemaChange> $changes */
    public function __construct(
        public ManifestFingerprint $baselineFingerprint,
        public ManifestFingerprint $targetFingerprint,
        public PlanDisposition $disposition,
        public CorrelationId $correlationId,
        array $changes,
    ) {
        $identifiers = [];
        $hasBlockedChange = false;
        foreach ($changes as $change) {
            if (!$change instanceof PhysicalSchemaChange) {
                throw new SchemaPlanningException(
                    SchemaPlanningException::PLAN_INVALID,
                    'Schema planning change collection is invalid.'
                );
            }
            if (isset($identifiers[$change->identifier->value])) {
                throw new SchemaPlanningException(
                    SchemaPlanningException::PLAN_INVALID,
                    'Schema planning change identifier is duplicated.'
                );
            }
            $identifiers[$change->identifier->value] = true;
            $hasBlockedChange = $hasBlockedChange || $change->risk === ChangeRisk::BLOCKED;
        }
        usort(
            $changes,
            static fn (PhysicalSchemaChange $left, PhysicalSchemaChange $right): int => $left->sortKey() <=> $right->sortKey(),
        );
        $this->changes = array_values($changes);

        if ($this->changes === []) {
            if ($this->disposition !== PlanDisposition::NO_CHANGES
                || !$this->baselineFingerprint->equals($this->targetFingerprint)) {
                throw new SchemaPlanningException(
                    SchemaPlanningException::PLAN_INVALID,
                    'Empty schema plan state is invalid.'
                );
            }
            return;
        }

        if ($this->baselineFingerprint->equals($this->targetFingerprint)) {
            throw new SchemaPlanningException(
                SchemaPlanningException::PLAN_INVALID,
                'Changed schema plan fingerprints are invalid.'
            );
        }

        $expectedDisposition = $hasBlockedChange
            ? PlanDisposition::BLOCKED
            : PlanDisposition::REVIEW_REQUIRED;
        if ($this->disposition !== $expectedDisposition) {
            throw new SchemaPlanningException(
                SchemaPlanningException::PLAN_INVALID,
                'Schema plan disposition does not match its changes.'
            );
        }
    }

    /** @return list<PhysicalSchemaChange> */
    public function changes(): array
    {
        return $this->changes;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'baseline_fingerprint' => $this->baselineFingerprint->value,
            'target_fingerprint' => $this->targetFingerprint->value,
            'disposition' => $this->disposition->value,
            'correlation_id' => $this->correlationId->value,
            'change_count' => count($this->changes),
            'changes' => $this->changes,
        ];
    }
}
