<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class LaravelMigrationMaterializationException extends \RuntimeException
{
    public const ARTIFACT_INVALID = 'LARAVEL_MIGRATION_MATERIALIZATION_ARTIFACT_INVALID';
    public const FRAMEWORK_TARGET_MISMATCH = 'LARAVEL_MIGRATION_MATERIALIZATION_FRAMEWORK_TARGET_MISMATCH';
    public const STAGING_PARENT_INVALID = 'LARAVEL_MIGRATION_MATERIALIZATION_STAGING_PARENT_INVALID';
    public const SYMLINK_DENIED = 'LARAVEL_MIGRATION_MATERIALIZATION_SYMLINK_DENIED';
    public const PATH_INVALID = 'LARAVEL_MIGRATION_MATERIALIZATION_PATH_INVALID';
    public const SYNTAX_INVALID = 'LARAVEL_MIGRATION_MATERIALIZATION_SYNTAX_INVALID';
    public const SOURCE_SHAPE_INVALID = 'LARAVEL_MIGRATION_MATERIALIZATION_SOURCE_SHAPE_INVALID';
    public const SOURCE_FINGERPRINT_MISMATCH = 'LARAVEL_MIGRATION_MATERIALIZATION_SOURCE_FINGERPRINT_MISMATCH';
    public const WORKSPACE_CONFLICT = 'LARAVEL_MIGRATION_MATERIALIZATION_WORKSPACE_CONFLICT';
    public const EXISTING_CONTENT_MISMATCH = 'LARAVEL_MIGRATION_MATERIALIZATION_EXISTING_CONTENT_MISMATCH';
    public const WRITE_FAILED = 'LARAVEL_MIGRATION_MATERIALIZATION_WRITE_FAILED';
    public const POST_WRITE_FINGERPRINT_MISMATCH = 'LARAVEL_MIGRATION_MATERIALIZATION_POST_WRITE_FINGERPRINT_MISMATCH';
    public const UNEXPECTED_FILE = 'LARAVEL_MIGRATION_MATERIALIZATION_UNEXPECTED_FILE';
    public const MISSING_FILE = 'LARAVEL_MIGRATION_MATERIALIZATION_MISSING_FILE';
    public const PERSISTED_VALIDATION_MISMATCH = 'LARAVEL_MIGRATION_MATERIALIZATION_PERSISTED_VALIDATION_MISMATCH';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class LaravelMigrationPersistedFile implements \JsonSerializable
{
    public function __construct(
        public string $relativePath,
        public string $expectedSourceFingerprint,
        public string $persistedSourceFingerprint,
        public int $persistedBytes,
        public bool $alreadyIdentical,
    ) {
        if (preg_match('/^database\/migrations\/0000_00_00_[0-9]{6}_[a-z0-9_]+_[a-f0-9]{12}\.php$/D', $this->relativePath) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $this->expectedSourceFingerprint) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $this->persistedSourceFingerprint) !== 1
            || $this->persistedBytes < 1) {
            throw new LaravelMigrationMaterializationException(
                LaravelMigrationMaterializationException::ARTIFACT_INVALID,
                'Persisted migration file evidence is invalid.',
            );
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'relative_path' => $this->relativePath,
            'expected_source_fingerprint' => $this->expectedSourceFingerprint,
            'persisted_source_fingerprint' => $this->persistedSourceFingerprint,
            'persisted_bytes' => $this->persistedBytes,
            'already_identical' => $this->alreadyIdentical,
        ];
    }
}

final readonly class LaravelMigrationMaterializationReport implements \JsonSerializable
{
    /** @var list<LaravelMigrationPersistedFile> */
    private array $files;

    /** @param list<LaravelMigrationPersistedFile> $files */
    public function __construct(
        public ManifestFingerprint $generationArtifactFingerprint,
        public string $framework,
        public string $frameworkVersion,
        public CorrelationId $generationCorrelationId,
        public CorrelationId $materializationCorrelationId,
        public string $workspaceRelativePath,
        array $files,
    ) {
        if ($this->framework !== LaravelMigrationGenerationArtifact::FRAMEWORK
            || $this->frameworkVersion !== LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION
            || preg_match('/^\.oneqay-migration-materialization\/[a-f0-9]{24}$/D', $this->workspaceRelativePath) !== 1
            || $files === []) {
            throw new LaravelMigrationMaterializationException(
                LaravelMigrationMaterializationException::ARTIFACT_INVALID,
                'Migration materialization report is invalid.',
            );
        }
        foreach ($files as $file) {
            if (!$file instanceof LaravelMigrationPersistedFile) {
                throw new LaravelMigrationMaterializationException(
                    LaravelMigrationMaterializationException::ARTIFACT_INVALID,
                    'Migration materialization file evidence is invalid.',
                );
            }
        }
        $this->files = array_values($files);
    }

    /** @return list<LaravelMigrationPersistedFile> */
    public function files(): array
    {
        return $this->files;
    }

    public function jsonSerialize(): array
    {
        return [
            'generation_artifact_fingerprint' => $this->generationArtifactFingerprint->value,
            'framework' => $this->framework,
            'framework_version' => $this->frameworkVersion,
            'generation_correlation_id' => $this->generationCorrelationId->value,
            'materialization_correlation_id' => $this->materializationCorrelationId->value,
            'workspace_relative_path' => $this->workspaceRelativePath,
            'file_count' => count($this->files),
            'files' => $this->files,
        ];
    }
}

final class GovernedLaravelMigrationMaterializer
{
    private const EXPECTED_PHP_REQUIREMENT = '^8.2';
    private const WORKSPACE_ROOT = '.oneqay-migration-materialization';

    /** @var list<string> */
    private const ALLOWED_ARROW_METHODS = [
        'string',
        'bigInteger',
        'decimal',
        'boolean',
        'char',
        'date',
        'dateTime',
        'json',
        'charset',
        'collation',
        'nullable',
        'default',
        'primary',
        'unique',
        'foreign',
        'references',
        'on',
    ];

    /** @var list<string> */
    private const FORBIDDEN_SOURCE_MARKERS = [
        'DB::',
        'Artisan::',
        'new PDO(',
        'PDO(',
        'Schema::drop',
        'dropColumn(',
        'dropForeign(',
        'dropUnique(',
        'artisan migrate',
        'migrate:fresh',
        'migrate:rollback',
        'CREATE TABLE',
        'ALTER TABLE',
        'DROP TABLE',
        'INSERT INTO',
        'DELETE FROM',
        'exec(',
        'shell_exec(',
        'system(',
        'passthru(',
        'proc_open(',
        'popen(',
        'curl_',
        'fsockopen(',
        'stream_socket_client(',
    ];

    public function materialize(
        LaravelMigrationGenerationArtifact $artifact,
        string $applicationComposerJson,
        string $stagingParent,
        CorrelationId|string $correlationId,
    ): LaravelMigrationMaterializationReport {
        $correlation = $correlationId instanceof CorrelationId ? $correlationId : new CorrelationId($correlationId);
        [$parent, $artifactFingerprint, $workspaceRelativePath] = $this->preflight($artifact, $applicationComposerJson, $stagingParent);
        $workspace = $this->join($parent, $workspaceRelativePath);
        $this->ensureDirectory($this->join($parent, self::WORKSPACE_ROOT), $parent);
        $this->ensureDirectory($workspace, $parent);
        $databaseDirectory = $this->join($workspace, 'database');
        $migrationDirectory = $this->join($databaseDirectory, 'migrations');
        $this->ensureDirectory($databaseDirectory, $workspace);
        $this->ensureDirectory($migrationDirectory, $workspace);

        $expected = $this->expectedFileMap($artifact);
        $this->assertExactFileSet($migrationDirectory, $expected, true);

        $results = [];
        foreach ($artifact->files() as $file) {
            $destination = $this->join($workspace, $file->relativePath);
            $this->assertDestination($destination, $workspace);
            $alreadyIdentical = false;

            if (is_link($destination)) {
                $this->fail(self::SYMLINK_DENIED, 'Symbolic links are not allowed for staged migration files.');
            }
            if (file_exists($destination)) {
                if (!is_file($destination)) {
                    $this->fail(self::WORKSPACE_CONFLICT, 'A staged migration target is not a regular file.');
                }
                $existing = @file_get_contents($destination);
                if (!is_string($existing) || !hash_equals($file->sourceFingerprint, hash('sha256', $existing))) {
                    $this->fail(self::EXISTING_CONTENT_MISMATCH, 'Existing staged migration content does not match the generation artifact.');
                }
                $this->assertSyntax($existing);
                $this->assertSourceShape($existing);
                $alreadyIdentical = true;
            } else {
                $written = @file_put_contents($destination, $file->source, LOCK_EX);
                if ($written === false || $written !== strlen($file->source)) {
                    $this->fail(self::WRITE_FAILED, 'Unable to materialize an exact staged migration file.');
                }
            }

            $persisted = @file_get_contents($destination);
            if (!is_string($persisted)) {
                $this->fail(self::WRITE_FAILED, 'Unable to read back a staged migration file.');
            }
            $persistedFingerprint = hash('sha256', $persisted);
            if (!hash_equals($file->sourceFingerprint, $persistedFingerprint)) {
                $this->fail(self::POST_WRITE_FINGERPRINT_MISMATCH, 'Staged migration fingerprint changed after materialization.');
            }
            $this->assertSyntax($persisted);
            $this->assertSourceShape($persisted);
            $results[] = new LaravelMigrationPersistedFile(
                $file->relativePath,
                $file->sourceFingerprint,
                $persistedFingerprint,
                strlen($persisted),
                $alreadyIdentical,
            );
        }

        $this->assertExactFileSet($migrationDirectory, $expected, false);

        return new LaravelMigrationMaterializationReport(
            $artifactFingerprint,
            LaravelMigrationGenerationArtifact::FRAMEWORK,
            LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION,
            $artifact->generationCorrelationId,
            $correlation,
            $workspaceRelativePath,
            $results,
        );
    }

    public function validate(
        LaravelMigrationGenerationArtifact $artifact,
        string $applicationComposerJson,
        string $stagingParent,
        CorrelationId|string $correlationId,
    ): LaravelMigrationMaterializationReport {
        $correlation = $correlationId instanceof CorrelationId ? $correlationId : new CorrelationId($correlationId);
        [$parent, $artifactFingerprint, $workspaceRelativePath] = $this->preflight($artifact, $applicationComposerJson, $stagingParent);
        $workspace = $this->join($parent, $workspaceRelativePath);
        $migrationDirectory = $this->join($workspace, 'database/migrations');
        if (is_link($workspace) || is_link($migrationDirectory)) {
            $this->fail(self::SYMLINK_DENIED, 'Symbolic links are not allowed in the isolated staging workspace.');
        }
        if (!is_dir($workspace) || !is_dir($migrationDirectory)) {
            $this->fail(self::MISSING_FILE, 'Expected isolated migration staging workspace is missing.');
        }

        $expected = $this->expectedFileMap($artifact);
        $this->assertExactFileSet($migrationDirectory, $expected, false);
        $results = [];
        foreach ($artifact->files() as $file) {
            $destination = $this->join($workspace, $file->relativePath);
            $this->assertDestination($destination, $workspace);
            if (is_link($destination) || !is_file($destination)) {
                $this->fail(self::PERSISTED_VALIDATION_MISMATCH, 'A staged migration file is not a regular validated file.');
            }
            $persisted = @file_get_contents($destination);
            if (!is_string($persisted)) {
                $this->fail(self::PERSISTED_VALIDATION_MISMATCH, 'A staged migration file cannot be read for validation.');
            }
            $fingerprint = hash('sha256', $persisted);
            if (!hash_equals($file->sourceFingerprint, $fingerprint)) {
                $this->fail(self::PERSISTED_VALIDATION_MISMATCH, 'Persisted migration content no longer matches the generation artifact.');
            }
            $this->assertSyntax($persisted);
            $this->assertSourceShape($persisted);
            $results[] = new LaravelMigrationPersistedFile(
                $file->relativePath,
                $file->sourceFingerprint,
                $fingerprint,
                strlen($persisted),
                true,
            );
        }

        return new LaravelMigrationMaterializationReport(
            $artifactFingerprint,
            LaravelMigrationGenerationArtifact::FRAMEWORK,
            LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION,
            $artifact->generationCorrelationId,
            $correlation,
            $workspaceRelativePath,
            $results,
        );
    }

    /** @return array{string,ManifestFingerprint,string} */
    private function preflight(
        LaravelMigrationGenerationArtifact $artifact,
        string $applicationComposerJson,
        string $stagingParent,
    ): array {
        $this->assertComposerTarget($applicationComposerJson);
        $this->assertArtifact($artifact);
        $parent = $this->assertStagingParent($stagingParent);
        $artifactFingerprint = new ManifestFingerprint(hash('sha256', $this->encode($artifact)));
        $workspaceRelativePath = self::WORKSPACE_ROOT . '/' . substr($artifactFingerprint->value, 0, 24);
        return [$parent, $artifactFingerprint, $workspaceRelativePath];
    }

    private function assertComposerTarget(string $applicationComposerJson): void
    {
        try {
            $decoded = json_decode($applicationComposerJson, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->fail(self::FRAMEWORK_TARGET_MISMATCH, 'Application Composer target manifest is invalid.');
        }
        if (!is_array($decoded) || !isset($decoded['require']) || !is_array($decoded['require'])) {
            $this->fail(self::FRAMEWORK_TARGET_MISMATCH, 'Application Composer target manifest is incomplete.');
        }
        $php = $decoded['require']['php'] ?? null;
        $laravel = $decoded['require']['laravel/framework'] ?? null;
        if (!is_string($php) || !hash_equals(self::EXPECTED_PHP_REQUIREMENT, $php)
            || !is_string($laravel) || !hash_equals(LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION, $laravel)) {
            $this->fail(self::FRAMEWORK_TARGET_MISMATCH, 'Application Composer framework target does not match Sprint 16.');
        }
    }

    private function assertArtifact(LaravelMigrationGenerationArtifact $artifact): void
    {
        $paths = [];
        $migrationIds = [];
        $sourceIds = [];
        $ordered = [];
        foreach ($artifact->files() as $file) {
            if (!$file instanceof LaravelMigrationFileArtifact
                || preg_match('/^database\/migrations\/0000_00_00_[0-9]{6}_[a-z0-9_]+_[a-f0-9]{12}\.php$/D', $file->relativePath) !== 1
                || !str_starts_with($file->relativePath, 'database/migrations/')
                || !hash_equals($artifact->generationCorrelationId->value, $file->generationCorrelationId->value)) {
                $this->fail(self::ARTIFACT_INVALID, 'Sprint 16 generation artifact contains invalid file metadata.');
            }
            if (isset($paths[$file->relativePath])
                || isset($migrationIds[$file->migrationIdentifier->value])
                || isset($sourceIds[$file->sourceChangeIdentifier->value])) {
                $this->fail(self::ARTIFACT_INVALID, 'Sprint 16 generation artifact contains duplicated identities.');
            }
            if (!hash_equals($file->sourceFingerprint, hash('sha256', $file->source))) {
                $this->fail(self::SOURCE_FINGERPRINT_MISMATCH, 'In-memory migration source fingerprint does not match its artifact.');
            }
            $this->assertSyntax($file->source);
            $this->assertSourceShape($file->source);
            $paths[$file->relativePath] = true;
            $migrationIds[$file->migrationIdentifier->value] = true;
            $sourceIds[$file->sourceChangeIdentifier->value] = true;
            $ordered[] = $file->relativePath;
        }
        if ($ordered === []) {
            $this->fail(self::ARTIFACT_INVALID, 'Sprint 16 generation artifact is empty.');
        }
        $sorted = $ordered;
        sort($sorted, SORT_STRING);
        if ($ordered !== $sorted) {
            $this->fail(self::ARTIFACT_INVALID, 'Sprint 16 generation artifact file order is invalid.');
        }
    }

    private function assertStagingParent(string $stagingParent): string
    {
        $candidate = trim($stagingParent);
        if ($candidate === '' || is_link($candidate) || !is_dir($candidate)) {
            $this->fail(self::STAGING_PARENT_INVALID, 'Staging parent must be an existing non-symlink directory.');
        }
        $real = @realpath($candidate);
        if ($real === false || !is_dir($real) || is_link($real)) {
            $this->fail(self::STAGING_PARENT_INVALID, 'Staging parent cannot be resolved safely.');
        }
        foreach (['artisan', 'composer.json'] as $applicationMarker) {
            if (file_exists($this->join($real, $applicationMarker)) || is_link($this->join($real, $applicationMarker))) {
                $this->fail(self::STAGING_PARENT_INVALID, 'Staging parent appears to be an application or repository root.');
            }
        }
        return $real;
    }

    private function assertSyntax(string $source): void
    {
        try {
            token_get_all($source, TOKEN_PARSE);
        } catch (\ParseError) {
            $this->fail(self::SYNTAX_INVALID, 'Generated Laravel migration source is not valid PHP syntax.');
        }
    }

    private function assertSourceShape(string $source): void
    {
        foreach ([
            'use Illuminate\\Database\\Migrations\\Migration;',
            'use Illuminate\\Database\\Schema\\Blueprint;',
            'use Illuminate\\Support\\Facades\\Schema;',
            'return new class extends Migration',
            'public function up(): void',
            'public function down(): void',
            "throw new \\LogicException('Forward-only generated migration; rollback is not authorized.');",
        ] as $required) {
            if (!str_contains($source, $required)) {
                $this->fail(self::SOURCE_SHAPE_INVALID, 'Generated Laravel migration source shape is incomplete.');
            }
        }
        if (!str_contains($source, 'Schema::create(') && !str_contains($source, 'Schema::table(')) {
            $this->fail(self::SOURCE_SHAPE_INVALID, 'Generated Laravel migration source has no bounded schema operation.');
        }
        foreach (self::FORBIDDEN_SOURCE_MARKERS as $marker) {
            if (stripos($source, $marker) !== false) {
                $this->fail(self::SOURCE_SHAPE_INVALID, 'Generated Laravel migration source contains a forbidden execution surface.');
            }
        }
        if (preg_match_all('/->([A-Za-z][A-Za-z0-9_]*)\s*\(/', $source, $matches) !== false) {
            foreach (array_unique($matches[1]) as $method) {
                if (!in_array($method, self::ALLOWED_ARROW_METHODS, true)) {
                    $this->fail(self::SOURCE_SHAPE_INVALID, 'Generated Laravel migration source uses an unapproved fluent API method.');
                }
            }
        }
    }

    /** @return array<string,LaravelMigrationFileArtifact> */
    private function expectedFileMap(LaravelMigrationGenerationArtifact $artifact): array
    {
        $expected = [];
        foreach ($artifact->files() as $file) {
            $expected[basename($file->relativePath)] = $file;
        }
        return $expected;
    }

    /** @param array<string,LaravelMigrationFileArtifact> $expected */
    private function assertExactFileSet(string $migrationDirectory, array $expected, bool $allowMissing): void
    {
        if (is_link($migrationDirectory)) {
            $this->fail(self::SYMLINK_DENIED, 'Migration staging directory must not be a symbolic link.');
        }
        if (!is_dir($migrationDirectory)) {
            if ($allowMissing) {
                return;
            }
            $this->fail(self::MISSING_FILE, 'Migration staging directory is missing.');
        }
        $seen = [];
        try {
            $iterator = new \FilesystemIterator($migrationDirectory, \FilesystemIterator::SKIP_DOTS);
            foreach ($iterator as $item) {
                if ($item->isLink()) {
                    $this->fail(self::SYMLINK_DENIED, 'Symbolic links are not allowed in the migration staging directory.');
                }
                if (!$item->isFile() || !isset($expected[$item->getFilename()])) {
                    $this->fail(self::UNEXPECTED_FILE, 'Migration staging directory contains an unexpected entry.');
                }
                $seen[$item->getFilename()] = true;
            }
        } catch (\UnexpectedValueException) {
            $this->fail(self::PERSISTED_VALIDATION_MISMATCH, 'Migration staging directory cannot be inspected safely.');
        }
        if (!$allowMissing && count($seen) !== count($expected)) {
            $this->fail(self::MISSING_FILE, 'Migration staging directory is missing expected files.');
        }
    }

    private function ensureDirectory(string $path, string $boundary): void
    {
        if (is_link($path)) {
            $this->fail(self::SYMLINK_DENIED, 'Symbolic links are not allowed in the materialization workspace.');
        }
        if (file_exists($path)) {
            if (!is_dir($path)) {
                $this->fail(self::WORKSPACE_CONFLICT, 'Materialization workspace path conflicts with a non-directory entry.');
            }
        } elseif (!@mkdir($path, 0700, false) && !is_dir($path)) {
            $this->fail(self::WRITE_FAILED, 'Unable to create an isolated migration staging directory.');
        }
        $resolved = @realpath($path);
        $resolvedBoundary = @realpath($boundary);
        if ($resolved === false || $resolvedBoundary === false || !$this->isWithin($resolved, $resolvedBoundary)) {
            $this->fail(self::PATH_INVALID, 'Materialization workspace escaped its authorized boundary.');
        }
    }

    private function assertDestination(string $destination, string $workspace): void
    {
        $parent = dirname($destination);
        $resolvedParent = @realpath($parent);
        $resolvedWorkspace = @realpath($workspace);
        if ($resolvedParent === false || $resolvedWorkspace === false || !$this->isWithin($resolvedParent, $resolvedWorkspace)) {
            $this->fail(self::PATH_INVALID, 'Migration destination escaped its isolated workspace.');
        }
    }

    private function isWithin(string $path, string $boundary): bool
    {
        $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
        $normalizedBoundary = rtrim(str_replace('\\', '/', $boundary), '/');
        return $normalizedPath === $normalizedBoundary || str_starts_with($normalizedPath . '/', $normalizedBoundary . '/');
    }

    private function join(string $base, string $relative): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function fail(string $code, string $message): never
    {
        throw new LaravelMigrationMaterializationException($code, $message);
    }
}
