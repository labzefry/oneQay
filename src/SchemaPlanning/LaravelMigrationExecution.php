<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class LaravelMigrationExecutionException extends \RuntimeException
{
    public const ARTIFACT_BINDING_INVALID = 'LARAVEL_MIGRATION_EXECUTION_ARTIFACT_BINDING_INVALID';
    public const MATERIALIZATION_VALIDATION_FAILED = 'LARAVEL_MIGRATION_EXECUTION_MATERIALIZATION_VALIDATION_FAILED';
    public const MATERIALIZATION_REPORT_MISMATCH = 'LARAVEL_MIGRATION_EXECUTION_MATERIALIZATION_REPORT_MISMATCH';
    public const EXECUTION_PARENT_INVALID = 'LARAVEL_MIGRATION_EXECUTION_PARENT_INVALID';
    public const SYMLINK_DENIED = 'LARAVEL_MIGRATION_EXECUTION_SYMLINK_DENIED';
    public const UNSUPPORTED_TARGET = 'LARAVEL_MIGRATION_EXECUTION_UNSUPPORTED_TARGET';
    public const TARGET_PREFLIGHT_FAILED = 'LARAVEL_MIGRATION_EXECUTION_TARGET_PREFLIGHT_FAILED';
    public const BASELINE_WITNESS_INVALID = 'LARAVEL_MIGRATION_EXECUTION_BASELINE_WITNESS_INVALID';
    public const LOCK_UNAVAILABLE = 'LARAVEL_MIGRATION_EXECUTION_LOCK_UNAVAILABLE';
    public const JOURNAL_INVALID = 'LARAVEL_MIGRATION_EXECUTION_JOURNAL_INVALID';
    public const JOURNAL_STATE_INVALID = 'LARAVEL_MIGRATION_EXECUTION_JOURNAL_STATE_INVALID';
    public const JOURNAL_IO_FAILED = 'LARAVEL_MIGRATION_EXECUTION_JOURNAL_IO_FAILED';
    public const MIGRATION_FILE_MISMATCH = 'LARAVEL_MIGRATION_EXECUTION_FILE_MISMATCH';
    public const MIGRATION_OBJECT_INVALID = 'LARAVEL_MIGRATION_EXECUTION_OBJECT_INVALID';
    public const MIGRATION_EXECUTION_FAILED = 'LARAVEL_MIGRATION_EXECUTION_FAILED';
    public const TARGET_VERIFICATION_FAILED = 'LARAVEL_MIGRATION_EXECUTION_TARGET_VERIFICATION_FAILED';
    public const TARGET_WITNESS_MISMATCH = 'LARAVEL_MIGRATION_EXECUTION_TARGET_WITNESS_MISMATCH';
    public const COMPLETE_STATE_MISMATCH = 'LARAVEL_MIGRATION_EXECUTION_COMPLETE_STATE_MISMATCH';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

interface LaravelMigrationExecutionTargetAdapter
{
    public const DISPOSABLE_SQLITE_TEST = 'DISPOSABLE_SQLITE_TEST';

    public function targetKind(): string;

    /** Returns a SHA-256 witness for the known synthetic baseline. */
    public function preflight(): string;

    /** Executes exactly one already-revalidated staged migration artifact. */
    public function execute(LaravelMigrationFileArtifact $file, string $absoluteStagedPath): void;

    /** Returns a SHA-256 witness for the verified final synthetic target. */
    public function verify(): string;
}

final readonly class LaravelMigrationExecutionReport implements \JsonSerializable
{
    /** @var list<string> */
    private array $executedMigrationIdentifiers;

    /** @param list<string> $executedMigrationIdentifiers */
    public function __construct(
        public ManifestFingerprint $generationArtifactFingerprint,
        public ManifestFingerprint $targetManifestFingerprint,
        public string $framework,
        public string $frameworkVersion,
        public CorrelationId $generationCorrelationId,
        public CorrelationId $materializationCorrelationId,
        public CorrelationId $executionCorrelationId,
        public string $executionWorkspaceRelativePath,
        public string $targetKind,
        public string $baselineWitness,
        public string $targetWitness,
        array $executedMigrationIdentifiers,
        public string $finalState,
        public bool $alreadyComplete,
    ) {
        if ($framework !== LaravelMigrationGenerationArtifact::FRAMEWORK
            || $frameworkVersion !== LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION
            || $targetKind !== LaravelMigrationExecutionTargetAdapter::DISPOSABLE_SQLITE_TEST
            || preg_match('/^\.oneqay-migration-execution\/[a-f0-9]{24}$/D', $executionWorkspaceRelativePath) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $baselineWitness) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $targetWitness) !== 1
            || $finalState !== 'COMPLETE'
            || $executedMigrationIdentifiers === []) {
            throw new LaravelMigrationExecutionException(
                LaravelMigrationExecutionException::ARTIFACT_BINDING_INVALID,
                'Migration execution report is invalid.',
            );
        }
        $seen = [];
        foreach ($executedMigrationIdentifiers as $identifier) {
            if (!is_string($identifier) || isset($seen[$identifier])) {
                throw new LaravelMigrationExecutionException(
                    LaravelMigrationExecutionException::ARTIFACT_BINDING_INVALID,
                    'Migration execution report identifiers are invalid.',
                );
            }
            new \OneQay\Migration\MigrationIdentifier($identifier);
            $seen[$identifier] = true;
        }
        $this->executedMigrationIdentifiers = array_values($executedMigrationIdentifiers);
    }

    /** @return list<string> */
    public function executedMigrationIdentifiers(): array
    {
        return $this->executedMigrationIdentifiers;
    }

    public function jsonSerialize(): array
    {
        return [
            'generation_artifact_fingerprint' => $this->generationArtifactFingerprint->value,
            'target_manifest_fingerprint' => $this->targetManifestFingerprint->value,
            'framework' => $this->framework,
            'framework_version' => $this->frameworkVersion,
            'generation_correlation_id' => $this->generationCorrelationId->value,
            'materialization_correlation_id' => $this->materializationCorrelationId->value,
            'execution_correlation_id' => $this->executionCorrelationId->value,
            'execution_workspace_relative_path' => $this->executionWorkspaceRelativePath,
            'target_kind' => $this->targetKind,
            'baseline_witness' => $this->baselineWitness,
            'target_witness' => $this->targetWitness,
            'executed_file_count' => count($this->executedMigrationIdentifiers),
            'executed_migration_identifiers' => $this->executedMigrationIdentifiers,
            'final_state' => $this->finalState,
            'already_complete' => $this->alreadyComplete,
        ];
    }
}

final class GovernedLaravelMigrationExecutor
{
    private const EXECUTION_ROOT = '.oneqay-migration-execution';
    private const JOURNAL_FILE = 'journal.json';
    private const LOCK_FILE = 'execution.lock';

    public function __construct(
        private readonly GovernedLaravelMigrationMaterializer $materializer = new GovernedLaravelMigrationMaterializer(),
    ) {}

    public function execute(
        LaravelMigrationGenerationArtifact $artifact,
        LaravelMigrationMaterializationReport $materializationReport,
        string $applicationComposerJson,
        string $stagingParent,
        LaravelMigrationExecutionTargetAdapter $target,
        CorrelationId|string $correlationId,
    ): LaravelMigrationExecutionReport {
        $correlation = $correlationId instanceof CorrelationId ? $correlationId : new CorrelationId($correlationId);
        if ($target->targetKind() !== LaravelMigrationExecutionTargetAdapter::DISPOSABLE_SQLITE_TEST) {
            $this->fail(LaravelMigrationExecutionException::UNSUPPORTED_TARGET, 'Migration execution target is not authorized for Sprint 18.');
        }

        $parent = $this->assertParent($stagingParent);
        $artifactFingerprint = new ManifestFingerprint(hash('sha256', $this->encode($artifact)));
        $validationCorrelation = new CorrelationId('s18-validation-' . substr(hash('sha256', $correlation->value), 0, 24));
        try {
            $validated = $this->materializer->validate($artifact, $applicationComposerJson, $parent, $validationCorrelation);
        } catch (\Throwable) {
            $this->fail(LaravelMigrationExecutionException::MATERIALIZATION_VALIDATION_FAILED, 'Sprint 17 materialization validation failed before execution.');
        }
        $this->assertMaterializationBinding($artifact, $artifactFingerprint, $materializationReport, $validated);

        $workspaceRelative = self::EXECUTION_ROOT . '/' . substr($artifactFingerprint->value, 0, 24);
        $root = $this->join($parent, self::EXECUTION_ROOT);
        $workspace = $this->join($parent, $workspaceRelative);
        $this->ensureDirectory($root, $parent);
        $this->ensureDirectory($workspace, $parent);
        $journalPath = $this->join($workspace, self::JOURNAL_FILE);
        $lockPath = $this->join($workspace, self::LOCK_FILE);

        $lock = $this->acquireLock($lockPath, $correlation);
        try {
            $this->assertWorkspaceContents($workspace);
            $orderedIdentifiers = array_map(
                static fn (LaravelMigrationFileArtifact $file): string => $file->migrationIdentifier->value,
                $artifact->files(),
            );

            $existing = $this->readJournal($journalPath);
            if ($existing !== null) {
                $this->assertJournalIdentity($existing, $artifact, $artifactFingerprint, $orderedIdentifiers, $target->targetKind());
                if (($existing['state'] ?? null) !== 'COMPLETE') {
                    $this->fail(LaravelMigrationExecutionException::JOURNAL_STATE_INVALID, 'Incomplete or failed migration execution cannot be resumed.');
                }
                try {
                    $targetWitness = strtolower(trim($target->verify()));
                } catch (\Throwable) {
                    $this->fail(LaravelMigrationExecutionException::TARGET_VERIFICATION_FAILED, 'Completed execution target verification failed.');
                }
                $this->assertWitness($targetWitness, LaravelMigrationExecutionException::TARGET_WITNESS_MISMATCH);
                if (!hash_equals((string) $existing['target_witness'], $targetWitness)
                    || ($existing['applied_identifiers'] ?? null) !== $orderedIdentifiers) {
                    $this->fail(LaravelMigrationExecutionException::COMPLETE_STATE_MISMATCH, 'Completed execution state no longer matches the disposable target.');
                }
                return $this->report(
                    $artifact,
                    $artifactFingerprint,
                    $materializationReport,
                    $correlation,
                    $workspaceRelative,
                    (string) $existing['baseline_witness'],
                    $targetWitness,
                    $orderedIdentifiers,
                    true,
                );
            }

            try {
                $baselineWitness = strtolower(trim($target->preflight()));
            } catch (\Throwable) {
                $this->fail(LaravelMigrationExecutionException::TARGET_PREFLIGHT_FAILED, 'Disposable execution target preflight failed.');
            }
            $this->assertWitness($baselineWitness, LaravelMigrationExecutionException::BASELINE_WITNESS_INVALID);

            $journal = [
                'generation_artifact_fingerprint' => $artifactFingerprint->value,
                'target_manifest_fingerprint' => $artifact->targetManifestFingerprint->value,
                'framework' => LaravelMigrationGenerationArtifact::FRAMEWORK,
                'framework_version' => LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION,
                'execution_correlation_id' => $correlation->value,
                'target_kind' => $target->targetKind(),
                'baseline_witness' => $baselineWitness,
                'ordered_identifiers' => $orderedIdentifiers,
                'applied_identifiers' => [],
                'state' => 'PREPARED',
                'error_code' => null,
                'target_witness' => null,
            ];
            $this->writeJournal($journalPath, $journal);
            $journal['state'] = 'RUNNING';
            $this->writeJournal($journalPath, $journal);

            foreach ($artifact->files() as $file) {
                $absolutePath = $this->validatedStagedPath($parent, $validated, $file);
                try {
                    $target->execute($file, $absolutePath);
                } catch (\Throwable) {
                    $journal['state'] = 'FAILED';
                    $journal['error_code'] = LaravelMigrationExecutionException::MIGRATION_EXECUTION_FAILED;
                    $this->writeJournal($journalPath, $journal);
                    $this->fail(LaravelMigrationExecutionException::MIGRATION_EXECUTION_FAILED, 'Governed migration execution failed.');
                }
                $journal['applied_identifiers'][] = $file->migrationIdentifier->value;
                $this->writeJournal($journalPath, $journal);
            }

            try {
                $targetWitness = strtolower(trim($target->verify()));
            } catch (\Throwable) {
                $journal['state'] = 'FAILED';
                $journal['error_code'] = LaravelMigrationExecutionException::TARGET_VERIFICATION_FAILED;
                $this->writeJournal($journalPath, $journal);
                $this->fail(LaravelMigrationExecutionException::TARGET_VERIFICATION_FAILED, 'Disposable execution target verification failed.');
            }
            $this->assertWitness($targetWitness, LaravelMigrationExecutionException::TARGET_WITNESS_MISMATCH);
            if (hash_equals($baselineWitness, $targetWitness)) {
                $journal['state'] = 'FAILED';
                $journal['error_code'] = LaravelMigrationExecutionException::TARGET_WITNESS_MISMATCH;
                $this->writeJournal($journalPath, $journal);
                $this->fail(LaravelMigrationExecutionException::TARGET_WITNESS_MISMATCH, 'Execution target witness did not change after governed migrations.');
            }

            $journal['state'] = 'COMPLETE';
            $journal['target_witness'] = $targetWitness;
            $this->writeJournal($journalPath, $journal);

            return $this->report(
                $artifact,
                $artifactFingerprint,
                $materializationReport,
                $correlation,
                $workspaceRelative,
                $baselineWitness,
                $targetWitness,
                $orderedIdentifiers,
                false,
            );
        } finally {
            $this->releaseLock($lock);
        }
    }

    private function assertMaterializationBinding(
        LaravelMigrationGenerationArtifact $artifact,
        ManifestFingerprint $artifactFingerprint,
        LaravelMigrationMaterializationReport $supplied,
        LaravelMigrationMaterializationReport $validated,
    ): void {
        if (!$artifactFingerprint->equals($supplied->generationArtifactFingerprint)
            || !$artifactFingerprint->equals($validated->generationArtifactFingerprint)
            || $supplied->framework !== LaravelMigrationGenerationArtifact::FRAMEWORK
            || $validated->framework !== LaravelMigrationGenerationArtifact::FRAMEWORK
            || $supplied->frameworkVersion !== LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION
            || $validated->frameworkVersion !== LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION
            || !hash_equals($artifact->generationCorrelationId->value, $supplied->generationCorrelationId->value)
            || !hash_equals($artifact->generationCorrelationId->value, $validated->generationCorrelationId->value)
            || !hash_equals($supplied->workspaceRelativePath, $validated->workspaceRelativePath)
            || count($supplied->files()) !== count($artifact->files())
            || count($validated->files()) !== count($artifact->files())) {
            $this->fail(LaravelMigrationExecutionException::MATERIALIZATION_REPORT_MISMATCH, 'Sprint 17 materialization report does not bind to the exact generation artifact.');
        }

        foreach ($artifact->files() as $index => $file) {
            $left = $supplied->files()[$index] ?? null;
            $right = $validated->files()[$index] ?? null;
            if (!$left instanceof LaravelMigrationPersistedFile
                || !$right instanceof LaravelMigrationPersistedFile
                || !hash_equals($file->relativePath, $left->relativePath)
                || !hash_equals($file->relativePath, $right->relativePath)
                || !hash_equals($file->sourceFingerprint, $left->expectedSourceFingerprint)
                || !hash_equals($file->sourceFingerprint, $left->persistedSourceFingerprint)
                || !hash_equals($file->sourceFingerprint, $right->expectedSourceFingerprint)
                || !hash_equals($file->sourceFingerprint, $right->persistedSourceFingerprint)
                || $left->persistedBytes !== strlen($file->source)
                || $right->persistedBytes !== strlen($file->source)) {
                $this->fail(LaravelMigrationExecutionException::MATERIALIZATION_REPORT_MISMATCH, 'Sprint 17 persisted file evidence does not bind to the exact generation artifact.');
            }
        }
    }

    private function validatedStagedPath(
        string $parent,
        LaravelMigrationMaterializationReport $validated,
        LaravelMigrationFileArtifact $file,
    ): string {
        $path = $this->join($parent, $validated->workspaceRelativePath . '/' . $file->relativePath);
        if (is_link($path) || !is_file($path)) {
            $this->fail(LaravelMigrationExecutionException::MIGRATION_FILE_MISMATCH, 'Validated staged migration file is unavailable.');
        }
        $real = realpath($path);
        if (!is_string($real) || !$this->isBelow($real, $parent)) {
            $this->fail(LaravelMigrationExecutionException::MIGRATION_FILE_MISMATCH, 'Validated staged migration path escaped its authorized parent.');
        }
        $persisted = @file_get_contents($real);
        if (!is_string($persisted) || !hash_equals($file->sourceFingerprint, hash('sha256', $persisted))) {
            $this->fail(LaravelMigrationExecutionException::MIGRATION_FILE_MISMATCH, 'Validated staged migration bytes changed before execution.');
        }
        return $real;
    }

    /** @return resource */
    private function acquireLock(string $path, CorrelationId $correlation)
    {
        if (is_link($path)) {
            $this->fail(LaravelMigrationExecutionException::SYMLINK_DENIED, 'Execution lock path may not be a symbolic link.');
        }
        $handle = @fopen($path, 'c+');
        if (!is_resource($handle) || !@flock($handle, LOCK_EX | LOCK_NB)) {
            if (is_resource($handle)) {
                @fclose($handle);
            }
            $this->fail(LaravelMigrationExecutionException::LOCK_UNAVAILABLE, 'Governed migration execution lock is unavailable.');
        }
        $owner = hash('sha256', $correlation->value);
        if (!@ftruncate($handle, 0) || @fwrite($handle, $owner) !== strlen($owner) || !@fflush($handle)) {
            @flock($handle, LOCK_UN);
            @fclose($handle);
            $this->fail(LaravelMigrationExecutionException::JOURNAL_IO_FAILED, 'Execution lock metadata could not be written safely.');
        }
        return $handle;
    }

    /** @param resource $handle */
    private function releaseLock($handle): void
    {
        if (is_resource($handle)) {
            @flock($handle, LOCK_UN);
            @fclose($handle);
        }
    }

    /** @return array<string,mixed>|null */
    private function readJournal(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }
        if (is_link($path) || !is_file($path)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal is not a regular file.');
        }
        $content = @file_get_contents($path);
        if (!is_string($content) || $content === '' || strlen($content) > 1048576) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal cannot be read safely.');
        }
        try {
            $journal = json_decode($content, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal JSON is invalid.');
        }
        if (!is_array($journal)
            || !in_array($journal['state'] ?? null, ['PREPARED', 'RUNNING', 'COMPLETE', 'FAILED'], true)
            || !is_array($journal['ordered_identifiers'] ?? null)
            || !is_array($journal['applied_identifiers'] ?? null)
            || !is_string($journal['generation_artifact_fingerprint'] ?? null)
            || !is_string($journal['target_manifest_fingerprint'] ?? null)
            || !is_string($journal['execution_correlation_id'] ?? null)
            || !is_string($journal['target_kind'] ?? null)
            || !is_string($journal['baseline_witness'] ?? null)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal shape is invalid.');
        }
        return $journal;
    }

    /** @param array<string,mixed> $journal */
    private function writeJournal(string $path, array $journal): void
    {
        $encoded = json_encode($journal, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
        $temp = $path . '.tmp';
        if (is_link($path) || is_link($temp) || file_exists($temp)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_IO_FAILED, 'Execution journal temporary path is unsafe.');
        }
        $written = @file_put_contents($temp, $encoded, LOCK_EX);
        if ($written === false || $written !== strlen($encoded) || !@rename($temp, $path)) {
            @unlink($temp);
            $this->fail(LaravelMigrationExecutionException::JOURNAL_IO_FAILED, 'Execution journal could not be persisted atomically.');
        }
        $readback = @file_get_contents($path);
        if (!is_string($readback) || !hash_equals(hash('sha256', $encoded), hash('sha256', $readback))) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_IO_FAILED, 'Execution journal readback verification failed.');
        }
    }

    /** @param array<string,mixed> $journal @param list<string> $orderedIdentifiers */
    private function assertJournalIdentity(
        array $journal,
        LaravelMigrationGenerationArtifact $artifact,
        ManifestFingerprint $artifactFingerprint,
        array $orderedIdentifiers,
        string $targetKind,
    ): void {
        if (!hash_equals($artifactFingerprint->value, (string) ($journal['generation_artifact_fingerprint'] ?? ''))
            || !hash_equals($artifact->targetManifestFingerprint->value, (string) ($journal['target_manifest_fingerprint'] ?? ''))
            || !hash_equals(LaravelMigrationGenerationArtifact::FRAMEWORK, (string) ($journal['framework'] ?? ''))
            || !hash_equals(LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION, (string) ($journal['framework_version'] ?? ''))
            || !hash_equals($targetKind, (string) ($journal['target_kind'] ?? ''))
            || ($journal['ordered_identifiers'] ?? null) !== $orderedIdentifiers
            || preg_match('/^[a-f0-9]{64}$/D', (string) ($journal['baseline_witness'] ?? '')) !== 1) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal identity no longer matches the governed artifact.');
        }
        $applied = $journal['applied_identifiers'] ?? [];
        if (!is_array($applied) || array_slice($orderedIdentifiers, 0, count($applied)) !== $applied) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution journal applied identifiers are not an ordered prefix.');
        }
        if (($journal['state'] ?? null) === 'COMPLETE'
            && (preg_match('/^[a-f0-9]{64}$/D', (string) ($journal['target_witness'] ?? '')) !== 1
                || $applied !== $orderedIdentifiers)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Completed execution journal is incomplete.');
        }
    }

    private function assertWorkspaceContents(string $workspace): void
    {
        $entries = @scandir($workspace);
        if (!is_array($entries)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution workspace cannot be inspected.');
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!in_array($entry, [self::LOCK_FILE, self::JOURNAL_FILE], true)) {
                $this->fail(LaravelMigrationExecutionException::JOURNAL_INVALID, 'Execution workspace contains an unexpected entry.');
            }
            $path = $this->join($workspace, $entry);
            if (is_link($path)) {
                $this->fail(LaravelMigrationExecutionException::SYMLINK_DENIED, 'Execution workspace entries may not be symbolic links.');
            }
        }
    }

    private function assertParent(string $path): string
    {
        $candidate = rtrim(trim($path), "\\/");
        if ($candidate === '' || is_link($candidate) || !is_dir($candidate)) {
            $this->fail(LaravelMigrationExecutionException::EXECUTION_PARENT_INVALID, 'Execution staging parent is invalid.');
        }
        $real = realpath($candidate);
        if (!is_string($real) || $real === '' || is_file($this->join($real, 'artisan')) || is_file($this->join($real, 'composer.json'))) {
            $this->fail(LaravelMigrationExecutionException::EXECUTION_PARENT_INVALID, 'Execution staging parent may not be an application or repository root.');
        }
        return rtrim($real, DIRECTORY_SEPARATOR);
    }

    private function ensureDirectory(string $path, string $boundary): void
    {
        if (is_link($path)) {
            $this->fail(LaravelMigrationExecutionException::SYMLINK_DENIED, 'Execution workspace directory may not be a symbolic link.');
        }
        if (!file_exists($path) && !@mkdir($path, 0700, false) && !is_dir($path)) {
            $this->fail(LaravelMigrationExecutionException::JOURNAL_IO_FAILED, 'Execution workspace directory could not be created.');
        }
        $real = realpath($path);
        if (!is_string($real) || !$this->isBelow($real, $boundary)) {
            $this->fail(LaravelMigrationExecutionException::EXECUTION_PARENT_INVALID, 'Execution workspace escaped its authorized boundary.');
        }
    }

    private function assertWitness(string $witness, string $errorCode): void
    {
        if (preg_match('/^[a-f0-9]{64}$/D', $witness) !== 1) {
            $this->fail($errorCode, 'Execution target witness is invalid.');
        }
    }

    /** @param list<string> $identifiers */
    private function report(
        LaravelMigrationGenerationArtifact $artifact,
        ManifestFingerprint $artifactFingerprint,
        LaravelMigrationMaterializationReport $materialization,
        CorrelationId $correlation,
        string $workspaceRelative,
        string $baselineWitness,
        string $targetWitness,
        array $identifiers,
        bool $alreadyComplete,
    ): LaravelMigrationExecutionReport {
        return new LaravelMigrationExecutionReport(
            $artifactFingerprint,
            $artifact->targetManifestFingerprint,
            LaravelMigrationGenerationArtifact::FRAMEWORK,
            LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION,
            $artifact->generationCorrelationId,
            $materialization->materializationCorrelationId,
            $correlation,
            $workspaceRelative,
            LaravelMigrationExecutionTargetAdapter::DISPOSABLE_SQLITE_TEST,
            $baselineWitness,
            $targetWitness,
            $identifiers,
            'COMPLETE',
            $alreadyComplete,
        );
    }

    private function join(string $base, string $relative): string
    {
        return rtrim($base, "\\/") . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, "\\/"));
    }

    private function isBelow(string $path, string $boundary): bool
    {
        $root = rtrim($boundary, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($path . (is_dir($path) ? DIRECTORY_SEPARATOR : ''), $root);
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function fail(string $code, string $message): never
    {
        throw new LaravelMigrationExecutionException($code, $message);
    }
}
