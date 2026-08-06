<?php

declare(strict_types=1);

namespace OneQay\Migration;

final class MigrationException extends \RuntimeException
{
    public const IDENTIFIER_INVALID = 'MIGRATION_IDENTIFIER_INVALID';
    public const CHECKSUM_INVALID = 'MIGRATION_CHECKSUM_INVALID';
    public const CHECKSUM_MISMATCH = 'MIGRATION_CHECKSUM_MISMATCH';
    public const DUPLICATE_IDENTIFIER = 'MIGRATION_DUPLICATE_IDENTIFIER';
    public const ORDER_INVALID = 'MIGRATION_ORDER_INVALID';
    public const DEPENDENCY_MISSING = 'MIGRATION_DEPENDENCY_MISSING';
    public const DESTRUCTIVE_DENIED = 'MIGRATION_DESTRUCTIVE_DENIED';
    public const ROLLBACK_UNAVAILABLE = 'MIGRATION_ROLLBACK_UNAVAILABLE';
    public const PLAN_INVALID = 'MIGRATION_PLAN_INVALID';
    public const LOCK_UNAVAILABLE = 'MIGRATION_LOCK_UNAVAILABLE';
    public const EXECUTION_FAILED = 'MIGRATION_EXECUTION_FAILED';
    public const NOT_READY = 'MIGRATION_NOT_READY';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class MigrationIdentifier
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (preg_match('/^MIG_[0-9]{8}_[0-9]{6}_[A-Z][A-Z0-9_]{2,64}$/D', $normalized) !== 1) {
            throw new MigrationException(MigrationException::IDENTIFIER_INVALID, 'Migration identifier is invalid.');
        }
        $this->value = $normalized;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}

final readonly class MigrationChecksum implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $normalized) !== 1) {
            throw new MigrationException(MigrationException::CHECKSUM_INVALID, 'Migration checksum is invalid.');
        }
        $this->value = $normalized;
    }

    public static function fromCanonicalDescriptor(string $descriptor): self
    {
        if ($descriptor === '' || strlen($descriptor) > 1048576) {
            throw new MigrationException(MigrationException::CHECKSUM_INVALID, 'Migration descriptor is invalid.');
        }
        return new self(hash('sha256', $descriptor));
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

enum MigrationSafetyClassification: string
{
    case SAFE = 'SAFE';
    case CAUTION = 'CAUTION';
    case DESTRUCTIVE = 'DESTRUCTIVE';
}

enum MigrationRollbackClassification: string
{
    case REVERSIBLE = 'REVERSIBLE';
    case FORWARD_ONLY = 'FORWARD_ONLY';
}

final readonly class MigrationDefinition implements \JsonSerializable
{
    /** @var list<string> */
    public array $dependencies;

    /** @param list<MigrationIdentifier|string> $dependencies */
    public function __construct(
        public MigrationIdentifier $identifier,
        public MigrationChecksum $declaredChecksum,
        public MigrationChecksum $artifactChecksum,
        array $dependencies,
        public MigrationSafetyClassification $safety,
        public MigrationRollbackClassification $rollback,
    ) {
        $normalized = [];
        foreach ($dependencies as $dependency) {
            $id = $dependency instanceof MigrationIdentifier ? $dependency : new MigrationIdentifier($dependency);
            if ($id->equals($this->identifier)) {
                throw new MigrationException(MigrationException::ORDER_INVALID, 'Migration cannot depend on itself.');
            }
            if (isset($normalized[$id->value])) {
                throw new MigrationException(MigrationException::DUPLICATE_IDENTIFIER, 'Migration dependency is duplicated.');
            }
            $normalized[$id->value] = true;
        }
        $this->dependencies = array_keys($normalized);
    }

    public function hasValidChecksum(): bool
    {
        return $this->declaredChecksum->equals($this->artifactChecksum);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'checksum' => $this->declaredChecksum->value,
            'dependencies' => $this->dependencies,
            'safety' => $this->safety->value,
            'rollback' => $this->rollback->value,
        ];
    }
}

final readonly class MigrationManifest implements \JsonSerializable
{
    /** @var list<MigrationDefinition> */
    private array $entries;

    /** @param list<MigrationDefinition> $entries */
    public function __construct(array $entries)
    {
        $indexes = [];
        foreach ($entries as $index => $entry) {
            if (!$entry instanceof MigrationDefinition) {
                throw new MigrationException(MigrationException::PLAN_INVALID, 'Migration manifest entry is invalid.');
            }
            $id = $entry->identifier->value;
            if (isset($indexes[$id])) {
                throw new MigrationException(MigrationException::DUPLICATE_IDENTIFIER, 'Migration identifier is duplicated.');
            }
            $indexes[$id] = $index;
        }

        $previous = null;
        foreach ($entries as $index => $entry) {
            if (!$entry->hasValidChecksum()) {
                throw new MigrationException(MigrationException::CHECKSUM_MISMATCH, 'Migration checksum mismatch.');
            }
            $id = $entry->identifier->value;
            if ($previous !== null && strcmp($previous, $id) >= 0) {
                throw new MigrationException(MigrationException::ORDER_INVALID, 'Migration manifest ordering is invalid.');
            }
            $previous = $id;
            foreach ($entry->dependencies as $dependency) {
                if (!array_key_exists($dependency, $indexes)) {
                    throw new MigrationException(MigrationException::DEPENDENCY_MISSING, 'Migration dependency is missing.');
                }
                if ($indexes[$dependency] >= $index) {
                    throw new MigrationException(MigrationException::ORDER_INVALID, 'Migration dependency ordering is invalid.');
                }
            }
        }
        $this->entries = array_values($entries);
    }

    /** @return list<MigrationDefinition> */
    public function entries(): array
    {
        return $this->entries;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_map(static fn (MigrationDefinition $entry): string => $entry->identifier->value, $this->entries);
    }

    /** @return array{entries: list<MigrationDefinition>} */
    public function jsonSerialize(): array
    {
        return ['entries' => $this->entries];
    }
}

final readonly class MigrationPlanningPolicy
{
    private function __construct(public bool $allowsDestructive) {}

    public static function safeDefault(): self
    {
        return new self(false);
    }

    public static function explicitlyAllowDestructive(): self
    {
        return new self(true);
    }
}

final readonly class MigrationPlan implements \JsonSerializable
{
    /** @var list<MigrationDefinition> */
    private array $entries;

    /** @param list<MigrationDefinition> $entries */
    public function __construct(array $entries, public bool $isDryRun = true)
    {
        if (!$this->isDryRun) {
            throw new MigrationException(MigrationException::PLAN_INVALID, 'Only dry-run migration plans are supported.');
        }
        foreach ($entries as $entry) {
            if (!$entry instanceof MigrationDefinition) {
                throw new MigrationException(MigrationException::PLAN_INVALID, 'Migration plan entry is invalid.');
            }
        }
        $this->entries = array_values($entries);
    }

    /** @return list<MigrationDefinition> */
    public function entries(): array
    {
        return $this->entries;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_map(static fn (MigrationDefinition $entry): string => $entry->identifier->value, $this->entries);
    }

    public function checksum(): string
    {
        $material = array_map(
            static fn (MigrationDefinition $entry): string => $entry->identifier->value . ':' . $entry->declaredChecksum->value,
            $this->entries,
        );
        return hash('sha256', implode('|', $material));
    }

    /** @return array{dry_run: bool, identifiers: list<string>, checksum: string} */
    public function jsonSerialize(): array
    {
        return ['dry_run' => true, 'identifiers' => $this->identifiers(), 'checksum' => $this->checksum()];
    }
}

final class MigrationPlanner
{
    /** @param list<MigrationIdentifier|string> $applied */
    public function plan(
        MigrationManifest $manifest,
        array $applied = [],
        ?MigrationPlanningPolicy $policy = null,
    ): MigrationPlan {
        $policy ??= MigrationPlanningPolicy::safeDefault();
        $known = array_fill_keys($manifest->identifiers(), true);
        $completed = [];
        foreach ($applied as $identifier) {
            $id = $identifier instanceof MigrationIdentifier ? $identifier->value : (new MigrationIdentifier($identifier))->value;
            if (!isset($known[$id])) {
                throw new MigrationException(MigrationException::PLAN_INVALID, 'Applied migration is not present in manifest.');
            }
            $completed[$id] = true;
        }

        $pendingSeen = false;
        foreach ($manifest->entries() as $entry) {
            $isApplied = isset($completed[$entry->identifier->value]);
            if (!$isApplied) {
                $pendingSeen = true;
            } elseif ($pendingSeen) {
                throw new MigrationException(MigrationException::ORDER_INVALID, 'Applied migrations must form a contiguous prefix.');
            }
        }

        $pending = [];
        foreach ($manifest->entries() as $entry) {
            $id = $entry->identifier->value;
            if (isset($completed[$id])) {
                continue;
            }
            foreach ($entry->dependencies as $dependency) {
                if (!isset($completed[$dependency])) {
                    throw new MigrationException(MigrationException::ORDER_INVALID, 'Pending dependency is not satisfied.');
                }
            }
            if ($entry->safety === MigrationSafetyClassification::DESTRUCTIVE && !$policy->allowsDestructive) {
                throw new MigrationException(MigrationException::DESTRUCTIVE_DENIED, 'Destructive migration is denied by default.');
            }
            $pending[] = $entry;
            $completed[$id] = true;
        }
        return new MigrationPlan($pending, true);
    }

    public function assertRollbackAvailable(MigrationDefinition $definition): void
    {
        if ($definition->rollback === MigrationRollbackClassification::FORWARD_ONLY) {
            throw new MigrationException(MigrationException::ROLLBACK_UNAVAILABLE, 'Rollback is unavailable.');
        }
    }
}

interface MigrationLock
{
    public function acquire(string $owner): bool;
    public function release(string $owner): void;
}

final class SyntheticMigrationLock implements MigrationLock
{
    private ?string $owner = null;

    public function __construct(private readonly bool $available = true) {}

    public function acquire(string $owner): bool
    {
        if (!$this->available || $owner === '' || $this->owner !== null) {
            return false;
        }
        $this->owner = $owner;
        return true;
    }

    public function release(string $owner): void
    {
        if ($this->owner !== null && hash_equals($this->owner, $owner)) {
            $this->owner = null;
        }
    }

    public function isHeld(): bool
    {
        return $this->owner !== null;
    }
}

interface MigrationExecutor
{
    public function execute(MigrationPlan $plan, string $correlationId): MigrationResult;
}

final readonly class MigrationResult implements \JsonSerializable
{
    /** @param list<string> $processedIdentifiers @param list<string> $errorCodes */
    private function __construct(
        public bool $isSuccessful,
        public bool $isDryRun,
        public array $processedIdentifiers,
        public array $errorCodes,
        public string $correlationId,
    ) {
        if ($this->correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
    }

    /** @param list<string> $processedIdentifiers */
    public static function success(string $correlationId, array $processedIdentifiers): self
    {
        return new self(true, true, array_values($processedIdentifiers), [], $correlationId);
    }

    /** @param list<string> $errorCodes */
    public static function failure(string $correlationId, array $errorCodes): self
    {
        return new self(false, true, [], array_values(array_unique($errorCodes)), $correlationId);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'successful' => $this->isSuccessful,
            'dry_run' => true,
            'processed_identifiers' => $this->processedIdentifiers,
            'error_codes' => $this->errorCodes,
            'correlation_id' => $this->correlationId,
        ];
    }
}

final class SyntheticMigrationExecutor implements MigrationExecutor
{
    public int $executeCalls = 0;

    public function __construct(private readonly bool $shouldFail = false) {}

    public function execute(MigrationPlan $plan, string $correlationId): MigrationResult
    {
        $this->executeCalls++;
        if ($this->shouldFail) {
            throw new MigrationException(MigrationException::EXECUTION_FAILED, 'Synthetic execution failed.');
        }
        return MigrationResult::success($correlationId, $plan->identifiers());
    }
}

final readonly class MigrationExecutionService
{
    public function run(
        MigrationPlan $plan,
        MigrationLock $lock,
        MigrationExecutor $executor,
        string $correlationId,
    ): MigrationResult {
        if ($correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
        $owner = hash('sha256', $correlationId);
        if (!$lock->acquire($owner)) {
            return MigrationResult::failure($correlationId, [MigrationException::LOCK_UNAVAILABLE, MigrationException::NOT_READY]);
        }
        try {
            return $executor->execute($plan, $correlationId);
        } catch (\Throwable) {
            return MigrationResult::failure($correlationId, [MigrationException::EXECUTION_FAILED, MigrationException::NOT_READY]);
        } finally {
            $lock->release($owner);
        }
    }
}
