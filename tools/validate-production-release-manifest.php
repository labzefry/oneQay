<?php

declare(strict_types=1);

// Author by Lab | zefry
final class ProductionReleaseManifestValidationException extends RuntimeException {}
function productionReleaseFail(string $message): never { throw new ProductionReleaseManifestValidationException($message); }
function productionReleaseLiteral(mixed $actual, mixed $expected, string $path): void { if ($actual !== $expected) productionReleaseFail($path.'_invalid'); }
function productionReleasePattern(mixed $value, string $pattern, string $path): string { if (!is_string($value) || preg_match($pattern, $value) !== 1) productionReleaseFail($path.'_invalid'); return $value; }
/** @return array<string,mixed> */
function productionReleaseLoad(string $path): array {
    if ($path === '' || !is_file($path) || is_link($path) || !is_readable($path)) productionReleaseFail('manifest_unavailable');
    try { $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { productionReleaseFail('manifest_json_invalid'); }
    if (!is_array($value) || array_is_list($value)) productionReleaseFail('manifest_shape_invalid');
    return $value;
}
/** @param array<string,mixed> $m */
function productionReleaseValidate(array $m, string $archive): void {
    productionReleaseLiteral($m['manifest_version'] ?? null, 1, 'manifest_version');
    productionReleaseLiteral($m['schema_id'] ?? null, 'oneqay.production-release-manifest.v1', 'schema_id');
    productionReleaseLiteral($m['product']['name'] ?? null, 'oneQay', 'product_name');
    productionReleaseLiteral($m['product']['repository'] ?? null, 'labzefry/oneQay', 'repository');
    $source = productionReleasePattern($m['source']['commit_sha'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_commit');
    $release = productionReleasePattern($m['release']['id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_id');
    productionReleaseLiteral($release, 'production-'.substr($source, 0, 12), 'release_source_binding');
    productionReleaseLiteral($m['release']['channel'] ?? null, 'PRODUCTION', 'channel');
    productionReleaseLiteral($m['release']['environment'] ?? null, 'PRODUCTION', 'environment');
    productionReleaseLiteral($m['release']['production'] ?? null, true, 'production');
    productionReleaseLiteral($m['release']['synthetic_fixture_runtime'] ?? null, false, 'synthetic_runtime');
    productionReleaseLiteral($m['release']['production_data_allowed'] ?? null, true, 'production_data_allowed');
    productionReleaseLiteral($m['runtime']['required_runtime_class'] ?? null, 'production', 'runtime_class');
    productionReleaseLiteral($m['runtime']['runtime_build_tools_required'] ?? null, false, 'runtime_build_tools');
    productionReleasePattern($m['application_payload']['sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'application_payload_sha256');
    productionReleaseLiteral($m['application_payload']['digest_algorithm'] ?? null, 'PATH_AND_CONTENT_SHA256_V1', 'payload_algorithm');
    productionReleaseLiteral($m['application_payload']['staging_equivalence_required'] ?? null, true, 'staging_equivalence_required');
    productionReleaseLiteral($m['migration']['expected_count'] ?? null, 27, 'migration_count');
    productionReleaseLiteral($m['migration']['execution_state'] ?? null, 'NOT_EXECUTED_BY_ARTIFACT_BUILD', 'migration_state');
    productionReleaseLiteral($m['migration']['execution_authorized'] ?? null, false, 'migration_authority');
    productionReleaseLiteral($m['operational_boundary']['environment_deployment'] ?? null, 'NOT_PERFORMED', 'deployment_state');
    productionReleaseLiteral($m['operational_boundary']['deployment_authority'] ?? null, 'NOT_GRANTED', 'deployment_authority');
    productionReleaseLiteral($m['operational_boundary']['production_activation'] ?? null, 'NOT_AUTHORIZED', 'production_activation');
    productionReleaseLiteral($m['operational_boundary']['selected_target'] ?? 'unexpected', null, 'selected_target');
    if ($archive === '' || !is_file($archive) || is_link($archive)) productionReleaseFail('archive_unavailable');
    $expectedName = $release.'.tar.gz';
    productionReleaseLiteral($m['artifact']['filename'] ?? null, $expectedName, 'artifact_filename');
    $sha = productionReleasePattern($m['artifact']['sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_sha256');
    productionReleaseLiteral(hash_file('sha256', $archive), $sha, 'artifact_hash');
    productionReleaseLiteral(filesize($archive), $m['artifact']['size_bytes'] ?? null, 'artifact_size');
}
if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (($argv[1] ?? null) === '--self-test') {
        $bad = false;
        try { productionReleasePattern('NOPE', '/\A[0-9a-f]{40}\z/', 'self_test'); } catch (ProductionReleaseManifestValidationException) { $bad = true; }
        if (!$bad) { fwrite(STDERR, "production_release_manifest_self_test_failed\n"); exit(1); }
        fwrite(STDOUT, "production_release_manifest_self_test_passed\n"); exit(0);
    }
    if ($argc !== 3) { fwrite(STDERR, "Usage: php tools/validate-production-release-manifest.php <manifest.json> <archive.tar.gz>\n"); exit(64); }
    try { productionReleaseValidate(productionReleaseLoad($argv[1]), $argv[2]); fwrite(STDOUT, "production_release_manifest_valid\n"); exit(0); }
    catch (Throwable $e) { fwrite(STDERR, "production_release_manifest_invalid:".$e->getMessage()."\n"); exit(1); }
}
