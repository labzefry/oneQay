<?php

declare(strict_types=1);

final class DurableStagingReleaseManifestValidationException extends RuntimeException
{
}

function durableStagingFail(string $message): never
{
    throw new DurableStagingReleaseManifestValidationException($message);
}

function durableStagingObject(mixed $value, string $path): array
{
    if (! is_array($value)) {
        durableStagingFail("{$path} must be an object.");
    }

    return $value;
}

function durableStagingExactKeys(array $value, array $expected, string $path): void
{
    $actual = array_keys($value);
    sort($actual);
    sort($expected);

    if ($actual !== $expected) {
        durableStagingFail("{$path} has missing or unknown fields.");
    }
}

function durableStagingLiteral(mixed $actual, mixed $expected, string $path): void
{
    if ($actual !== $expected) {
        durableStagingFail("{$path} has an unsupported value.");
    }
}

function durableStagingString(mixed $value, string $path): string
{
    if (! is_string($value) || $value === '') {
        durableStagingFail("{$path} must be a non-empty string.");
    }

    return $value;
}

function durableStagingInt(mixed $value, string $path): int
{
    if (! is_int($value)) {
        durableStagingFail("{$path} must be an integer.");
    }

    return $value;
}

function durableStagingPattern(string $value, string $pattern, string $path): void
{
    if (preg_match($pattern, $value) !== 1) {
        durableStagingFail("{$path} has an invalid format.");
    }
}

function durableStagingLoadJson(string $path): array
{
    if (! is_file($path)) {
        durableStagingFail("Missing JSON file: {$path}");
    }

    try {
        $value = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        durableStagingFail("Invalid JSON in {$path}: {$exception->getMessage()}");
    }

    return durableStagingObject($value, $path);
}

function validateDurableStagingSchemaReference(): void
{
    $schema = durableStagingLoadJson(dirname(__DIR__).'/release/durable-staging-manifest-v1.schema.json');

    durableStagingLiteral($schema['$schema'] ?? null, 'https://json-schema.org/draft/2020-12/schema', 'schema.$schema');
    durableStagingLiteral($schema['title'] ?? null, 'oneQay Durable Staging Release Manifest v1', 'schema.title');
    durableStagingLiteral($schema['type'] ?? null, 'object', 'schema.type');
    durableStagingLiteral($schema['additionalProperties'] ?? null, false, 'schema.additionalProperties');

    $properties = durableStagingObject($schema['properties'] ?? null, 'schema.properties');
    durableStagingLiteral($properties['schema_id']['const'] ?? null, 'oneqay.durable-staging-release-manifest.v1', 'schema.schema_id');
    durableStagingLiteral($properties['release']['properties']['channel']['const'] ?? null, 'STAGING', 'schema.release.channel');
    durableStagingLiteral($properties['release']['properties']['environment']['const'] ?? null, 'DURABLE_STAGING', 'schema.release.environment');
    durableStagingLiteral($properties['runtime']['properties']['required_runtime_class']['const'] ?? null, 'durable-staging', 'schema.runtime.required_runtime_class');
    durableStagingLiteral($properties['migration']['properties']['expected_count']['const'] ?? null, 27, 'schema.migration.expected_count');
    durableStagingLiteral($properties['migration']['properties']['execution_authorized']['const'] ?? null, false, 'schema.migration.execution_authorized');
}

function validateDurableStagingManifestArray(array $manifest): void
{
    durableStagingExactKeys($manifest, [
        'manifest_version',
        'schema_id',
        'product',
        'release',
        'source',
        'build',
        'artifact',
        'runtime',
        'migration',
        'attestation_binding',
        'operational_boundary',
        'attribution',
    ], 'manifest');

    durableStagingLiteral($manifest['manifest_version'], 1, 'manifest.manifest_version');
    durableStagingLiteral($manifest['schema_id'], 'oneqay.durable-staging-release-manifest.v1', 'manifest.schema_id');

    $product = durableStagingObject($manifest['product'], 'manifest.product');
    durableStagingExactKeys($product, ['name', 'repository'], 'manifest.product');
    durableStagingLiteral($product['name'], 'oneQay', 'manifest.product.name');
    durableStagingLiteral($product['repository'], 'labzefry/oneQay', 'manifest.product.repository');

    $release = durableStagingObject($manifest['release'], 'manifest.release');
    durableStagingExactKeys($release, [
        'id',
        'channel',
        'environment',
        'production',
        'synthetic_fixture_runtime',
        'production_data_allowed',
    ], 'manifest.release');
    $releaseId = durableStagingString($release['id'], 'manifest.release.id');
    durableStagingPattern($releaseId, '/^durable-staging-[0-9a-f]{12}$/D', 'manifest.release.id');
    durableStagingLiteral($release['channel'], 'STAGING', 'manifest.release.channel');
    durableStagingLiteral($release['environment'], 'DURABLE_STAGING', 'manifest.release.environment');
    durableStagingLiteral($release['production'], false, 'manifest.release.production');
    durableStagingLiteral($release['synthetic_fixture_runtime'], false, 'manifest.release.synthetic_fixture_runtime');
    durableStagingLiteral($release['production_data_allowed'], false, 'manifest.release.production_data_allowed');

    $source = durableStagingObject($manifest['source'], 'manifest.source');
    durableStagingExactKeys($source, ['commit_sha'], 'manifest.source');
    $sourceCommit = durableStagingString($source['commit_sha'], 'manifest.source.commit_sha');
    durableStagingPattern($sourceCommit, '/^[0-9a-f]{40}$/D', 'manifest.source.commit_sha');
    durableStagingLiteral($releaseId, 'durable-staging-'.substr($sourceCommit, 0, 12), 'manifest.release.id/source binding');

    $build = durableStagingObject($manifest['build'], 'manifest.build');
    durableStagingExactKeys($build, ['provider', 'source_date_epoch', 'provenance_reference'], 'manifest.build');
    durableStagingLiteral($build['provider'], 'GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI', 'manifest.build.provider');
    if (durableStagingInt($build['source_date_epoch'], 'manifest.build.source_date_epoch') < 1) {
        durableStagingFail('manifest.build.source_date_epoch must be positive.');
    }
    $provenance = durableStagingString($build['provenance_reference'], 'manifest.build.provenance_reference');
    if (strlen($provenance) > 1024) {
        durableStagingFail('manifest.build.provenance_reference is too long.');
    }

    $artifact = durableStagingObject($manifest['artifact'], 'manifest.artifact');
    durableStagingExactKeys($artifact, ['filename', 'format', 'media_type', 'size_bytes', 'sha256'], 'manifest.artifact');
    durableStagingLiteral($artifact['filename'], $releaseId.'.tar.gz', 'manifest.artifact.filename');
    durableStagingLiteral($artifact['format'], 'tar.gz', 'manifest.artifact.format');
    durableStagingLiteral($artifact['media_type'], 'application/gzip', 'manifest.artifact.media_type');
    if (durableStagingInt($artifact['size_bytes'], 'manifest.artifact.size_bytes') < 1) {
        durableStagingFail('manifest.artifact.size_bytes must be positive.');
    }
    durableStagingPattern(
        durableStagingString($artifact['sha256'], 'manifest.artifact.sha256'),
        '/^[0-9a-f]{64}$/D',
        'manifest.artifact.sha256'
    );

    $runtime = durableStagingObject($manifest['runtime'], 'manifest.runtime');
    durableStagingExactKeys($runtime, [
        'required_runtime_class',
        'php_constraint',
        'build_php',
        'build_node',
        'runtime_build_tools_required',
    ], 'manifest.runtime');
    durableStagingLiteral($runtime['required_runtime_class'], 'durable-staging', 'manifest.runtime.required_runtime_class');
    durableStagingLiteral($runtime['php_constraint'], '^8.2', 'manifest.runtime.php_constraint');
    durableStagingLiteral($runtime['build_php'], '8.3', 'manifest.runtime.build_php');
    durableStagingLiteral($runtime['build_node'], '24.19.0', 'manifest.runtime.build_node');
    durableStagingLiteral($runtime['runtime_build_tools_required'], false, 'manifest.runtime.runtime_build_tools_required');

    $migration = durableStagingObject($manifest['migration'], 'manifest.migration');
    durableStagingExactKeys($migration, [
        'source_included',
        'expected_count',
        'latest_migration',
        'execution_state',
        'execution_authorized',
    ], 'manifest.migration');
    durableStagingLiteral($migration['source_included'], true, 'manifest.migration.source_included');
    durableStagingLiteral($migration['expected_count'], 27, 'manifest.migration.expected_count');
    durableStagingLiteral(
        $migration['latest_migration'],
        '0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
        'manifest.migration.latest_migration'
    );
    durableStagingLiteral($migration['execution_state'], 'NOT_EXECUTED_BY_ARTIFACT_BUILD', 'manifest.migration.execution_state');
    durableStagingLiteral($migration['execution_authorized'], false, 'manifest.migration.execution_authorized');

    $attestation = durableStagingObject($manifest['attestation_binding'], 'manifest.attestation_binding');
    durableStagingExactKeys($attestation, [
        'readiness_endpoint',
        'source_commit_environment_key',
        'artifact_sha256_environment_key',
        'attestation_token_environment_key',
        'environment_values_embedded',
    ], 'manifest.attestation_binding');
    durableStagingLiteral($attestation['readiness_endpoint'], '/internal/oneqay/durable-runtime/readiness', 'manifest.attestation_binding.readiness_endpoint');
    durableStagingLiteral($attestation['source_commit_environment_key'], 'ONEQAY_RUNNING_SOURCE_COMMIT', 'manifest.attestation_binding.source_commit_environment_key');
    durableStagingLiteral($attestation['artifact_sha256_environment_key'], 'ONEQAY_RUNNING_ARTIFACT_SHA256', 'manifest.attestation_binding.artifact_sha256_environment_key');
    durableStagingLiteral($attestation['attestation_token_environment_key'], 'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN', 'manifest.attestation_binding.attestation_token_environment_key');
    durableStagingLiteral($attestation['environment_values_embedded'], false, 'manifest.attestation_binding.environment_values_embedded');

    $boundary = durableStagingObject($manifest['operational_boundary'], 'manifest.operational_boundary');
    durableStagingExactKeys($boundary, [
        'environment_creation',
        'environment_deployment',
        'migration27_execution',
        'permission_provisioning',
        'feature_activation',
        'deployment_authority',
        'technical_preview_activation',
        'production_activation',
        'updater_activation',
        'target_selection',
        'selected_target',
        'producer_dispatch',
    ], 'manifest.operational_boundary');
    durableStagingLiteral($boundary['environment_creation'], 'NOT_PERFORMED', 'manifest.operational_boundary.environment_creation');
    durableStagingLiteral($boundary['environment_deployment'], 'NOT_PERFORMED', 'manifest.operational_boundary.environment_deployment');
    durableStagingLiteral($boundary['migration27_execution'], 'NOT_PERFORMED', 'manifest.operational_boundary.migration27_execution');
    durableStagingLiteral($boundary['permission_provisioning'], 'NONE', 'manifest.operational_boundary.permission_provisioning');
    durableStagingLiteral($boundary['feature_activation'], 'INACTIVE', 'manifest.operational_boundary.feature_activation');
    durableStagingLiteral($boundary['deployment_authority'], 'NOT_GRANTED', 'manifest.operational_boundary.deployment_authority');
    durableStagingLiteral($boundary['technical_preview_activation'], 'NOT_AUTHORIZED', 'manifest.operational_boundary.technical_preview_activation');
    durableStagingLiteral($boundary['production_activation'], 'NOT_AUTHORIZED', 'manifest.operational_boundary.production_activation');
    durableStagingLiteral($boundary['updater_activation'], 'INACTIVE', 'manifest.operational_boundary.updater_activation');
    durableStagingLiteral($boundary['target_selection'], 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET', 'manifest.operational_boundary.target_selection');
    durableStagingLiteral($boundary['selected_target'], null, 'manifest.operational_boundary.selected_target');
    durableStagingLiteral($boundary['producer_dispatch'], 'NOT_PERFORMED', 'manifest.operational_boundary.producer_dispatch');

    durableStagingLiteral($manifest['attribution'], 'Lab | zefry', 'manifest.attribution');
}

function validateDurableStagingArtifactBinding(array $manifest, string $artifactPath): void
{
    if (! is_file($artifactPath)) {
        durableStagingFail("Missing artifact: {$artifactPath}");
    }

    $artifact = durableStagingObject($manifest['artifact'] ?? null, 'manifest.artifact');
    durableStagingLiteral(basename($artifactPath), $artifact['filename'] ?? null, 'artifact basename');

    $size = filesize($artifactPath);
    if ($size === false) {
        durableStagingFail('Unable to determine artifact size.');
    }
    durableStagingLiteral($size, $artifact['size_bytes'] ?? null, 'artifact size binding');

    $sha256 = hash_file('sha256', $artifactPath);
    if ($sha256 === false) {
        durableStagingFail('Unable to compute artifact SHA-256.');
    }
    durableStagingLiteral($sha256, $artifact['sha256'] ?? null, 'artifact SHA-256 binding');
}

function durableStagingSelfTest(string $artifactPath): array
{
    $source = str_repeat('a', 40);
    $releaseId = 'durable-staging-'.substr($source, 0, 12);
    $size = filesize($artifactPath);
    $sha256 = hash_file('sha256', $artifactPath);

    if ($size === false || $sha256 === false) {
        durableStagingFail('Self-test artifact inspection failed.');
    }

    return [
        'manifest_version' => 1,
        'schema_id' => 'oneqay.durable-staging-release-manifest.v1',
        'product' => ['name' => 'oneQay', 'repository' => 'labzefry/oneQay'],
        'release' => [
            'id' => $releaseId,
            'channel' => 'STAGING',
            'environment' => 'DURABLE_STAGING',
            'production' => false,
            'synthetic_fixture_runtime' => false,
            'production_data_allowed' => false,
        ],
        'source' => ['commit_sha' => $source],
        'build' => [
            'provider' => 'GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI',
            'source_date_epoch' => 1,
            'provenance_reference' => 'self-test://durable-staging-release-manifest-v1',
        ],
        'artifact' => [
            'filename' => basename($artifactPath),
            'format' => 'tar.gz',
            'media_type' => 'application/gzip',
            'size_bytes' => $size,
            'sha256' => $sha256,
        ],
        'runtime' => [
            'required_runtime_class' => 'durable-staging',
            'php_constraint' => '^8.2',
            'build_php' => '8.3',
            'build_node' => '24.19.0',
            'runtime_build_tools_required' => false,
        ],
        'migration' => [
            'source_included' => true,
            'expected_count' => 27,
            'latest_migration' => '0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
            'execution_state' => 'NOT_EXECUTED_BY_ARTIFACT_BUILD',
            'execution_authorized' => false,
        ],
        'attestation_binding' => [
            'readiness_endpoint' => '/internal/oneqay/durable-runtime/readiness',
            'source_commit_environment_key' => 'ONEQAY_RUNNING_SOURCE_COMMIT',
            'artifact_sha256_environment_key' => 'ONEQAY_RUNNING_ARTIFACT_SHA256',
            'attestation_token_environment_key' => 'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
            'environment_values_embedded' => false,
        ],
        'operational_boundary' => [
            'environment_creation' => 'NOT_PERFORMED',
            'environment_deployment' => 'NOT_PERFORMED',
            'migration27_execution' => 'NOT_PERFORMED',
            'permission_provisioning' => 'NONE',
            'feature_activation' => 'INACTIVE',
            'deployment_authority' => 'NOT_GRANTED',
            'technical_preview_activation' => 'NOT_AUTHORIZED',
            'production_activation' => 'NOT_AUTHORIZED',
            'updater_activation' => 'INACTIVE',
            'target_selection' => 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET',
            'selected_target' => null,
            'producer_dispatch' => 'NOT_PERFORMED',
        ],
        'attribution' => 'Lab | zefry',
    ];
}

function durableStagingExpectFailure(array $manifest, string $label): void
{
    try {
        validateDurableStagingManifestArray($manifest);
    } catch (DurableStagingReleaseManifestValidationException) {
        return;
    }

    durableStagingFail("Self-test expected failure: {$label}");
}

function runDurableStagingSelfTests(): void
{
    validateDurableStagingSchemaReference();

    $tempDir = sys_get_temp_dir().'/oneqay-durable-staging-manifest-'.bin2hex(random_bytes(8));
    if (! mkdir($tempDir, 0700, true) && ! is_dir($tempDir)) {
        durableStagingFail('Unable to create self-test directory.');
    }

    $artifactPath = $tempDir.'/durable-staging-aaaaaaaaaaaa.tar.gz';
    file_put_contents($artifactPath, 'oneQay durable staging release manifest v1 self-test');

    try {
        $valid = durableStagingSelfTest($artifactPath);
        validateDurableStagingManifestArray($valid);
        validateDurableStagingArtifactBinding($valid, $artifactPath);

        $production = $valid;
        $production['release']['production'] = true;
        durableStagingExpectFailure($production, 'production release');

        $migrationExecution = $valid;
        $migrationExecution['migration']['execution_authorized'] = true;
        durableStagingExpectFailure($migrationExecution, 'migration execution authority');

        $runtime = $valid;
        $runtime['runtime']['required_runtime_class'] = 'production';
        durableStagingExpectFailure($runtime, 'production runtime class');

        $selected = $valid;
        $selected['operational_boundary']['selected_target'] = ['environment_id' => 'not-allowed'];
        durableStagingExpectFailure($selected, 'selected target');

        $badDigest = $valid;
        $badDigest['artifact']['sha256'] = str_repeat('0', 64);
        try {
            validateDurableStagingArtifactBinding($badDigest, $artifactPath);
            durableStagingFail('Self-test expected failure: artifact digest mismatch');
        } catch (DurableStagingReleaseManifestValidationException) {
        }
    } finally {
        @unlink($artifactPath);
        @rmdir($tempDir);
    }

    fwrite(STDOUT, "Durable staging release manifest v1 self-tests passed.\n");
}

try {
    if (($argv[1] ?? null) === '--self-test') {
        runDurableStagingSelfTests();
        exit(0);
    }

    $manifestPath = $argv[1] ?? null;
    $artifactPath = $argv[2] ?? null;

    if (! is_string($manifestPath) || ! is_string($artifactPath)) {
        fwrite(STDERR, "Usage: php tools/validate-durable-staging-release-manifest.php <manifest.json> <artifact.tar.gz>\n");
        fwrite(STDERR, "   or: php tools/validate-durable-staging-release-manifest.php --self-test\n");
        exit(2);
    }

    validateDurableStagingSchemaReference();
    $manifest = durableStagingLoadJson($manifestPath);
    validateDurableStagingManifestArray($manifest);
    validateDurableStagingArtifactBinding($manifest, $artifactPath);
    fwrite(STDOUT, "Durable staging release manifest verified for ".basename($artifactPath).".\n");
} catch (DurableStagingReleaseManifestValidationException $exception) {
    fwrite(STDERR, "Durable staging release manifest validation failed: {$exception->getMessage()}\n");
    exit(1);
}

// Author by Lab | zefry
