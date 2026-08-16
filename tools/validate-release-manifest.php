<?php

declare(strict_types=1);

final class ReleaseManifestValidationException extends RuntimeException
{
}

function manifestFail(string $message): never
{
    throw new ReleaseManifestValidationException($message);
}

function expectArrayValue(mixed $value, string $path): array
{
    if (!is_array($value)) {
        manifestFail("{$path} must be an object.");
    }

    return $value;
}

function expectExactKeys(array $value, array $expectedKeys, string $path): void
{
    $actual = array_keys($value);
    sort($actual);
    sort($expectedKeys);

    if ($actual !== $expectedKeys) {
        manifestFail("{$path} has missing or unknown fields.");
    }
}

function expectString(mixed $value, string $path): string
{
    if (!is_string($value) || $value === '') {
        manifestFail("{$path} must be a non-empty string.");
    }

    return $value;
}

function expectInt(mixed $value, string $path): int
{
    if (!is_int($value)) {
        manifestFail("{$path} must be an integer.");
    }

    return $value;
}

function expectBool(mixed $value, string $path): bool
{
    if (!is_bool($value)) {
        manifestFail("{$path} must be a boolean.");
    }

    return $value;
}

function expectLiteral(mixed $value, mixed $expected, string $path): void
{
    if ($value !== $expected) {
        manifestFail("{$path} has an unsupported value.");
    }
}

function expectPattern(string $value, string $pattern, string $path): void
{
    if (preg_match($pattern, $value) !== 1) {
        manifestFail("{$path} has an invalid format.");
    }
}

function loadJsonObject(string $path): array
{
    if (!is_file($path)) {
        manifestFail("Missing JSON file: {$path}");
    }

    try {
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        manifestFail("Invalid JSON in {$path}: {$exception->getMessage()}");
    }

    return expectArrayValue($decoded, $path);
}

function validateCanonicalSchemaReference(string $schemaPath): void
{
    $schema = loadJsonObject($schemaPath);

    expectLiteral($schema['$schema'] ?? null, 'https://json-schema.org/draft/2020-12/schema', 'schema.$schema');
    expectLiteral($schema['title'] ?? null, 'oneQay Governed Release Manifest v1', 'schema.title');
    expectLiteral($schema['type'] ?? null, 'object', 'schema.type');
    expectLiteral($schema['additionalProperties'] ?? null, false, 'schema.additionalProperties');

    $properties = expectArrayValue($schema['properties'] ?? null, 'schema.properties');
    expectLiteral($properties['manifest_version']['const'] ?? null, 1, 'schema.properties.manifest_version.const');
    expectLiteral($properties['schema_id']['const'] ?? null, 'oneqay.release-manifest.v1', 'schema.properties.schema_id.const');
    expectLiteral($properties['product']['properties']['name']['const'] ?? null, 'oneQay', 'schema.product.name.const');
    expectLiteral($properties['product']['properties']['repository']['const'] ?? null, 'labzefry/oneQay', 'schema.product.repository.const');
    expectLiteral($properties['compatibility']['properties']['migration_classification']['const'] ?? null, 'NO_SCHEMA_CHANGE', 'schema.compatibility.migration_classification.const');
    expectLiteral($properties['compatibility']['properties']['updater_activation']['const'] ?? null, 'DISABLED', 'schema.compatibility.updater_activation.const');
}

function validateManifestArray(array $manifest): void
{
    expectExactKeys($manifest, [
        'manifest_version',
        'schema_id',
        'product',
        'release',
        'source',
        'build',
        'artifact',
        'runtime',
        'compatibility',
        'release_notes_reference',
        'attribution',
    ], 'manifest');

    expectLiteral($manifest['manifest_version'], 1, 'manifest.manifest_version');
    expectLiteral($manifest['schema_id'], 'oneqay.release-manifest.v1', 'manifest.schema_id');

    $product = expectArrayValue($manifest['product'], 'manifest.product');
    expectExactKeys($product, ['name', 'repository'], 'manifest.product');
    expectLiteral($product['name'], 'oneQay', 'manifest.product.name');
    expectLiteral($product['repository'], 'labzefry/oneQay', 'manifest.product.repository');

    $release = expectArrayValue($manifest['release'], 'manifest.release');
    expectExactKeys($release, ['id', 'version', 'channel', 'environment', 'production', 'synthetic_data_only'], 'manifest.release');
    $releaseId = expectString($release['id'], 'manifest.release.id');
    expectPattern($releaseId, '/^m75-preview-[0-9a-f]{12}$/D', 'manifest.release.id');
    if ($release['version'] !== null) {
        $version = expectString($release['version'], 'manifest.release.version');
        expectPattern($version, '/^[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/D', 'manifest.release.version');
    }
    expectLiteral($release['channel'], 'PREVIEW', 'manifest.release.channel');
    expectLiteral($release['environment'], 'TECHNICAL_PREVIEW', 'manifest.release.environment');
    expectLiteral(expectBool($release['production'], 'manifest.release.production'), false, 'manifest.release.production');
    expectLiteral(expectBool($release['synthetic_data_only'], 'manifest.release.synthetic_data_only'), true, 'manifest.release.synthetic_data_only');

    $source = expectArrayValue($manifest['source'], 'manifest.source');
    expectExactKeys($source, ['commit_sha'], 'manifest.source');
    $sourceSha = expectString($source['commit_sha'], 'manifest.source.commit_sha');
    expectPattern($sourceSha, '/^[0-9a-f]{40}$/D', 'manifest.source.commit_sha');
    if ($releaseId !== 'm75-preview-'.substr($sourceSha, 0, 12)) {
        manifestFail('manifest.release.id must be bound to the source commit prefix.');
    }

    $build = expectArrayValue($manifest['build'], 'manifest.build');
    expectExactKeys($build, ['provider', 'source_date_epoch', 'provenance_reference'], 'manifest.build');
    expectLiteral($build['provider'], 'GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI', 'manifest.build.provider');
    if (expectInt($build['source_date_epoch'], 'manifest.build.source_date_epoch') < 1) {
        manifestFail('manifest.build.source_date_epoch must be positive.');
    }
    $provenance = expectString($build['provenance_reference'], 'manifest.build.provenance_reference');
    if (strlen($provenance) > 1024) {
        manifestFail('manifest.build.provenance_reference is too long.');
    }

    $artifact = expectArrayValue($manifest['artifact'], 'manifest.artifact');
    expectExactKeys($artifact, ['filename', 'format', 'media_type', 'size_bytes', 'sha256'], 'manifest.artifact');
    $artifactFilename = expectString($artifact['filename'], 'manifest.artifact.filename');
    expectLiteral($artifactFilename, $releaseId.'.tar.gz', 'manifest.artifact.filename');
    expectLiteral($artifact['format'], 'tar.gz', 'manifest.artifact.format');
    expectLiteral($artifact['media_type'], 'application/gzip', 'manifest.artifact.media_type');
    if (expectInt($artifact['size_bytes'], 'manifest.artifact.size_bytes') < 1) {
        manifestFail('manifest.artifact.size_bytes must be positive.');
    }
    $artifactSha = expectString($artifact['sha256'], 'manifest.artifact.sha256');
    expectPattern($artifactSha, '/^[0-9a-f]{64}$/D', 'manifest.artifact.sha256');

    $runtime = expectArrayValue($manifest['runtime'], 'manifest.runtime');
    expectExactKeys($runtime, ['php_constraint', 'build_php', 'build_node', 'runtime_build_tools_required'], 'manifest.runtime');
    expectLiteral($runtime['php_constraint'], '^8.2', 'manifest.runtime.php_constraint');
    expectLiteral($runtime['build_php'], '8.3', 'manifest.runtime.build_php');
    expectLiteral($runtime['build_node'], '24.19.0', 'manifest.runtime.build_node');
    expectLiteral(expectBool($runtime['runtime_build_tools_required'], 'manifest.runtime.runtime_build_tools_required'), false, 'manifest.runtime.runtime_build_tools_required');

    $compatibility = expectArrayValue($manifest['compatibility'], 'manifest.compatibility');
    expectExactKeys($compatibility, [
        'supported_current_release_policy',
        'allow_downgrade',
        'migration_classification',
        'rollback_compatibility',
        'public_surface_compatibility',
        'private_storage_layout_version',
        'updater_activation',
    ], 'manifest.compatibility');
    expectLiteral($compatibility['supported_current_release_policy'], 'GOVERNED_PREVIEW_NO_SCHEMA_CHANGE', 'manifest.compatibility.supported_current_release_policy');
    expectLiteral(expectBool($compatibility['allow_downgrade'], 'manifest.compatibility.allow_downgrade'), false, 'manifest.compatibility.allow_downgrade');
    expectLiteral($compatibility['migration_classification'], 'NO_SCHEMA_CHANGE', 'manifest.compatibility.migration_classification');
    expectLiteral($compatibility['rollback_compatibility'], 'APPLICATION_POINTER_ROLLBACK_COMPATIBLE', 'manifest.compatibility.rollback_compatibility');
    expectLiteral($compatibility['public_surface_compatibility'], 'M7_5_PREVIEW_PUBLIC_SURFACE_V1', 'manifest.compatibility.public_surface_compatibility');
    expectLiteral(expectInt($compatibility['private_storage_layout_version'], 'manifest.compatibility.private_storage_layout_version'), 1, 'manifest.compatibility.private_storage_layout_version');
    expectLiteral($compatibility['updater_activation'], 'DISABLED', 'manifest.compatibility.updater_activation');

    expectLiteral($manifest['release_notes_reference'], 'UPDATER.md#release-manifest-v1', 'manifest.release_notes_reference');
    expectLiteral($manifest['attribution'], 'Lab | zefry', 'manifest.attribution');
}

function validateArtifactBinding(array $manifest, string $artifactPath): void
{
    if (!is_file($artifactPath)) {
        manifestFail("Missing artifact: {$artifactPath}");
    }

    $artifact = expectArrayValue($manifest['artifact'] ?? null, 'manifest.artifact');
    expectLiteral(basename($artifactPath), $artifact['filename'] ?? null, 'artifact basename');

    $size = filesize($artifactPath);
    if ($size === false) {
        manifestFail('Unable to determine artifact size.');
    }
    expectLiteral($size, $artifact['size_bytes'] ?? null, 'artifact size binding');

    $sha256 = hash_file('sha256', $artifactPath);
    if ($sha256 === false) {
        manifestFail('Unable to compute artifact SHA-256.');
    }
    expectLiteral($sha256, $artifact['sha256'] ?? null, 'artifact SHA-256 binding');
}

function validateManifestFile(string $manifestPath, ?string $artifactPath = null): void
{
    $schemaPath = dirname(__DIR__).'/release/manifest-v1.schema.json';
    validateCanonicalSchemaReference($schemaPath);

    $manifest = loadJsonObject($manifestPath);
    validateManifestArray($manifest);

    if ($artifactPath !== null) {
        validateArtifactBinding($manifest, $artifactPath);
    }
}

function selfTestManifest(string $artifactPath): array
{
    $sha = str_repeat('a', 40);
    $releaseId = 'm75-preview-'.substr($sha, 0, 12);
    $size = filesize($artifactPath);
    $digest = hash_file('sha256', $artifactPath);

    if ($size === false || $digest === false) {
        manifestFail('Self-test could not inspect temporary artifact.');
    }

    return [
        'manifest_version' => 1,
        'schema_id' => 'oneqay.release-manifest.v1',
        'product' => ['name' => 'oneQay', 'repository' => 'labzefry/oneQay'],
        'release' => [
            'id' => $releaseId,
            'version' => null,
            'channel' => 'PREVIEW',
            'environment' => 'TECHNICAL_PREVIEW',
            'production' => false,
            'synthetic_data_only' => true,
        ],
        'source' => ['commit_sha' => $sha],
        'build' => [
            'provider' => 'GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI',
            'source_date_epoch' => 1,
            'provenance_reference' => 'self-test://release-manifest-v1',
        ],
        'artifact' => [
            'filename' => basename($artifactPath),
            'format' => 'tar.gz',
            'media_type' => 'application/gzip',
            'size_bytes' => $size,
            'sha256' => $digest,
        ],
        'runtime' => [
            'php_constraint' => '^8.2',
            'build_php' => '8.3',
            'build_node' => '24.19.0',
            'runtime_build_tools_required' => false,
        ],
        'compatibility' => [
            'supported_current_release_policy' => 'GOVERNED_PREVIEW_NO_SCHEMA_CHANGE',
            'allow_downgrade' => false,
            'migration_classification' => 'NO_SCHEMA_CHANGE',
            'rollback_compatibility' => 'APPLICATION_POINTER_ROLLBACK_COMPATIBLE',
            'public_surface_compatibility' => 'M7_5_PREVIEW_PUBLIC_SURFACE_V1',
            'private_storage_layout_version' => 1,
            'updater_activation' => 'DISABLED',
        ],
        'release_notes_reference' => 'UPDATER.md#release-manifest-v1',
        'attribution' => 'Lab | zefry',
    ];
}

function expectSelfTestFailure(array $manifest, string $label): void
{
    try {
        validateManifestArray($manifest);
    } catch (ReleaseManifestValidationException) {
        return;
    }

    manifestFail("Self-test expected failure: {$label}");
}

function runSelfTests(): void
{
    $schemaPath = dirname(__DIR__).'/release/manifest-v1.schema.json';
    validateCanonicalSchemaReference($schemaPath);

    $tempDir = sys_get_temp_dir().'/oneqay-release-manifest-'.bin2hex(random_bytes(8));
    if (!mkdir($tempDir, 0700, true) && !is_dir($tempDir)) {
        manifestFail('Unable to create self-test directory.');
    }

    $artifactPath = $tempDir.'/m75-preview-aaaaaaaaaaaa.tar.gz';
    file_put_contents($artifactPath, 'oneQay governed release manifest v1 self-test');

    try {
        $valid = selfTestManifest($artifactPath);
        validateManifestArray($valid);
        validateArtifactBinding($valid, $artifactPath);

        $badVersion = $valid;
        $badVersion['manifest_version'] = 2;
        expectSelfTestFailure($badVersion, 'unsupported manifest version');

        $badRepository = $valid;
        $badRepository['product']['repository'] = 'example/other';
        expectSelfTestFailure($badRepository, 'arbitrary repository');

        $schemaChange = $valid;
        $schemaChange['compatibility']['migration_classification'] = 'SCHEMA_CHANGE';
        expectSelfTestFailure($schemaChange, 'schema-changing release');

        $downgrade = $valid;
        $downgrade['compatibility']['allow_downgrade'] = true;
        expectSelfTestFailure($downgrade, 'downgrade permission');

        $unknownField = $valid;
        $unknownField['unexpected'] = 'reject-me';
        expectSelfTestFailure($unknownField, 'unknown top-level field');

        $badReleaseId = $valid;
        $badReleaseId['release']['id'] = 'm75-preview-bbbbbbbbbbbb';
        expectSelfTestFailure($badReleaseId, 'release/source mismatch');

        $badDigest = $valid;
        $badDigest['artifact']['sha256'] = str_repeat('0', 64);
        try {
            validateArtifactBinding($badDigest, $artifactPath);
            manifestFail('Self-test expected failure: artifact digest mismatch');
        } catch (ReleaseManifestValidationException) {
        }
    } finally {
        @unlink($artifactPath);
        @rmdir($tempDir);
    }

    fwrite(STDOUT, "Release Manifest v1 self-tests passed.\n");
}

try {
    if (($argv[1] ?? null) === '--self-test') {
        runSelfTests();
        exit(0);
    }

    $manifestPath = $argv[1] ?? null;
    $artifactPath = $argv[2] ?? null;

    if (!is_string($manifestPath) || !is_string($artifactPath)) {
        fwrite(STDERR, "Usage: php tools/validate-release-manifest.php <manifest.json> <artifact.tar.gz>\n");
        fwrite(STDERR, "   or: php tools/validate-release-manifest.php --self-test\n");
        exit(2);
    }

    validateManifestFile($manifestPath, $artifactPath);
    fwrite(STDOUT, "Release Manifest v1 verified for ".basename($artifactPath).".\n");
} catch (ReleaseManifestValidationException $exception) {
    fwrite(STDERR, "Release Manifest v1 validation failed: {$exception->getMessage()}\n");
    exit(1);
}

// Author by Lab | zefry
