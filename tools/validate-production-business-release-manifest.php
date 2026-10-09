<?php
declare(strict_types=1);

// Author by Lab | zefry
// Certified SOURCE readiness is not a production host deployment, payment acceptance, or traffic authority.
function pbmFail(string $reason): never { throw new RuntimeException($reason); }
function pbmLoad(string $path): array {
    if ($path === '' || is_link($path) || !is_file($path) || !is_readable($path) || filesize($path) > 131072) pbmFail('input');
    try { $data = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR); }
    catch (Throwable) { pbmFail('json'); }
    if (!is_array($data) || array_is_list($data)) pbmFail('shape');
    return $data;
}
function pbmEq(mixed $actual, mixed $expected, string $reason): void {
    if ($actual !== $expected) pbmFail($reason);
}
function pbmValidate(string $manifestPath, string $archivePath): void {
    $m = pbmLoad($manifestPath);
    $source = $m['source']['commit_sha'] ?? null;
    if (!is_string($source) || preg_match('/\A[a-f0-9]{40}\z/', $source) !== 1) pbmFail('source');
    $tree = $m['source']['application_tree_sha'] ?? null;
    if (!is_string($tree) || preg_match('/\A[a-f0-9]{40}\z/', $tree) !== 1) pbmFail('source_tree');
    $id = 'production-'.substr($source, 0, 12);
    pbmEq($m['manifest_version'] ?? null, 2, 'version');
    pbmEq($m['schema_id'] ?? null, 'oneqay.production-business-release-manifest.v2', 'schema');
    pbmEq($m['product']['name'] ?? null, 'oneQay', 'product');
    pbmEq($m['product']['repository'] ?? null, 'labzefry/oneQay', 'repository');
    pbmEq($m['release']['id'] ?? null, $id, 'release_id');
    pbmEq($m['release']['channel'] ?? null, 'BUSINESS_PRODUCTION_CANDIDATE', 'channel');
    pbmEq($m['release']['environment'] ?? null, 'PRODUCTION', 'environment');
    pbmEq($m['release']['production'] ?? null, true, 'production');
    pbmEq($m['release']['production_data_allowed'] ?? null, true, 'production_data');
    pbmEq($m['build']['provider'] ?? null, 'GITHUB_ACTIONS_CERTIFIED_SOURCE_CI', 'provider');
    $provenance = $m['build']['provenance_reference'] ?? null;
    if (!is_string($provenance) || preg_match('/\Agithub-actions:[0-9]+:[1-9][0-9]*:source-tested\z/', $provenance) !== 1) pbmFail('provenance');
    $epoch = $m['build']['source_date_epoch'] ?? null;
    if (!is_int($epoch) || $epoch < 1) pbmFail('epoch');
    pbmEq($m['runtime']['required_runtime_class'] ?? null, 'production', 'runtime');
    pbmEq($m['runtime']['business_runtime_activation_ready'] ?? null, true, 'business_source');
    pbmEq($m['runtime']['source_ci_certified'] ?? null, true, 'source_tested');
    pbmEq($m['runtime']['host_business_acceptance_verified'] ?? null, false, 'host_acceptance');
    pbmEq($m['runtime']['dark_deploy_health_endpoint'] ?? null, '/health/live', 'health');
    pbmEq($m['migration']['expected_count'] ?? null, 27, 'migrations');
    pbmEq($m['migration']['source_included'] ?? null, true, 'migration_source');
    pbmEq($m['migration']['execution_state'] ?? null, 'NOT_PERFORMED_BY_ARTIFACT_BUILD', 'migration_execution');
    pbmEq($m['migration']['execution_authorized'] ?? null, false, 'migration_authority');
    pbmEq($m['promotion_gate']['verified_durable_staging_evidence_required'] ?? null, true, 'staging');
    pbmEq($m['promotion_gate']['same_source_commit_required'] ?? null, true, 'same_source');
    pbmEq($m['promotion_gate']['production_traffic_activation_authorized'] ?? null, false, 'traffic');
    pbmEq($m['operational_boundary']['environment_deployment'] ?? null, 'NOT_PERFORMED', 'deployment');
    pbmEq($m['operational_boundary']['deployment_authority'] ?? null, 'NOT_GRANTED', 'authority');
    pbmEq($m['operational_boundary']['production_activation'] ?? null, 'NOT_AUTHORIZED', 'activation');
    pbmEq($m['operational_boundary']['updater_activation'] ?? null, 'INACTIVE', 'updater');
    pbmEq($m['attribution'] ?? null, 'Lab | zefry', 'attribution');
    pbmEq($m['artifact']['filename'] ?? null, $id.'.tar.gz', 'archive_name');
    pbmEq($m['artifact']['format'] ?? null, 'tar.gz', 'format');
    pbmEq(basename($archivePath), $id.'.tar.gz', 'archive_filename');
    $sha = $m['artifact']['sha256'] ?? null;
    if (!is_string($sha) || preg_match('/\A[a-f0-9]{64}\z/', $sha) !== 1) pbmFail('sha_shape');
    if ($archivePath === '' || is_link($archivePath) || !is_file($archivePath) || !is_readable($archivePath)) pbmFail('archive');
    pbmEq($m['artifact']['size_bytes'] ?? null, filesize($archivePath), 'size');
    if (!hash_equals($sha, hash_file('sha256', $archivePath))) pbmFail('sha_mismatch');
    // Validate only the exact release metadata member. Use tar (the packaging tool)
    // instead of PharData, whose gzip member reads vary across supported PHP builds.
    // Array argv deliberately avoids shell interpolation; no extraction to disk occurs.
    $command = ['tar', '-xOzf', $archivePath, '--', $id.'/RELEASE.json'];
    $pipes = [];
    $process = proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
    if (!is_resource($process)) pbmFail('release_archive_tool');
    fclose($pipes[0]);
    $raw = stream_get_contents($pipes[1], 65537);
    fclose($pipes[1]);
    $error = stream_get_contents($pipes[2], 1024);
    fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== 0 || !is_string($raw) || strlen($raw) < 2 || strlen($raw) > 65536) pbmFail('release_unreadable');
    try { $release = json_decode($raw, true, 32, JSON_THROW_ON_ERROR); }
    catch (Throwable) { pbmFail('release_json'); }
    if (!is_array($release) || array_is_list($release)) pbmFail('release_shape');
    foreach ([
        'release_id'=>$id,'source_commit'=>$source,'application_tree_sha'=>$tree,
        'environment'=>'PRODUCTION','required_runtime_class'=>'production',
        'production'=>true,'production_data_allowed'=>true,
        'business_runtime_activation_ready'=>true,'source_ci_certified'=>true,
        'host_business_acceptance_verified'=>false,'deployment_authority'=>'NOT_GRANTED',
        'environment_deployment'=>'NOT_PERFORMED','production_traffic_activation'=>'NOT_AUTHORIZED',
        'production_activation'=>'NOT_AUTHORIZED','updater_activation'=>'INACTIVE'
    ] as $field=>$expected) pbmEq($release[$field] ?? null, $expected, 'release_'.$field);
}
if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 3) exit(64);
    try { pbmValidate($argv[1], $argv[2]); fwrite(STDOUT, "production_business_source_candidate_valid_not_activated\n"); exit(0); }
    catch (Throwable $e) { fwrite(STDERR, "production_business_source_candidate_invalid:".$e->getMessage()."\n"); exit(1); }
}
