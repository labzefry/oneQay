<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2).'/validate-production-business-release-manifest.php';

// Author by Lab | zefry. Pure local archive/manifest negative tests, never authorizes host traffic.
$assert = static function (bool $condition, string $reason): void {
    if (!$condition) throw new RuntimeException('business_certification_regression:'.$reason);
};
$source = str_repeat('a', 40);
$tree = str_repeat('b', 40);
$id = 'production-'.substr($source, 0, 12);
$root = sys_get_temp_dir().'/oneqay-business-cert-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700), 'private_temp');
$release = [
    'release_id'=>$id,'source_commit'=>$source,'application_tree_sha'=>$tree,
    'environment'=>'PRODUCTION','required_runtime_class'=>'production',
    'production'=>true,'production_data_allowed'=>true,
    'business_runtime_activation_ready'=>true,'source_ci_certified'=>true,
    'host_business_acceptance_verified'=>false,'deployment_authority'=>'NOT_GRANTED',
    'environment_deployment'=>'NOT_PERFORMED','production_traffic_activation'=>'NOT_AUTHORIZED',
    'production_activation'=>'NOT_AUTHORIZED','updater_activation'=>'INACTIVE',
];
$manifest = [
    'manifest_version'=>2,
    'schema_id'=>'oneqay.production-business-release-manifest.v2',
    'product'=>['name'=>'oneQay','repository'=>'labzefry/oneQay'],
    'release'=>['id'=>$id,'channel'=>'BUSINESS_PRODUCTION_CANDIDATE','environment'=>'PRODUCTION','production'=>true,'production_data_allowed'=>true],
    'source'=>['commit_sha'=>$source,'application_tree_sha'=>$tree],
    'build'=>['provider'=>'GITHUB_ACTIONS_CERTIFIED_SOURCE_CI','provenance_reference'=>'github-actions:123:1:source-tested','source_date_epoch'=>1791500000],
    'artifact'=>['filename'=>$id.'.tar.gz','format'=>'tar.gz'],
    'runtime'=>['required_runtime_class'=>'production','business_runtime_activation_ready'=>true,'source_ci_certified'=>true,'host_business_acceptance_verified'=>false,'dark_deploy_health_endpoint'=>'/health/live'],
    'migration'=>['expected_count'=>27,'source_included'=>true,'execution_state'=>'NOT_PERFORMED_BY_ARTIFACT_BUILD','execution_authorized'=>false],
    'promotion_gate'=>['verified_durable_staging_evidence_required'=>true,'same_source_commit_required'=>true,'production_traffic_activation_authorized'=>false],
    'operational_boundary'=>['environment_deployment'=>'NOT_PERFORMED','deployment_authority'=>'NOT_GRANTED','production_activation'=>'NOT_AUTHORIZED','updater_activation'=>'INACTIVE'],
    'attribution'=>'Lab | zefry',
];
$tarPath = $root.'/'.$id.'.tar';
$tar = new PharData($tarPath);
$tar->addFromString($id.'/RELEASE.json', json_encode($release, JSON_THROW_ON_ERROR));
$tar->compress(Phar::GZ);
unset($tar);
$archive = $tarPath.'.gz';
$manifest['artifact']['sha256'] = hash_file('sha256', $archive);
$manifest['artifact']['size_bytes'] = filesize($archive);
$manifestPath = $root.'/manifest.json';
$write = static function (array $value) use ($manifestPath): void {
    file_put_contents($manifestPath, json_encode($value, JSON_THROW_ON_ERROR));
};
$write($manifest);
// The production builder uses GNU tar/gzip; validate the fixture via the same member-read contract.
$argv = ['tar','-xOzf',$archive,'--',$id.'/RELEASE.json'];
$process = proc_open($argv,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$streams);
$assert(is_resource($process),'tar_member_probe');
fclose($streams[0]);
$bytes=stream_get_contents($streams[1],65537);
fclose($streams[1]);
fclose($streams[2]);
$assert(proc_close($process)===0,'tar_member_read');
$decoded=json_decode((string)$bytes,true,32,JSON_THROW_ON_ERROR);
$assert(is_array($decoded)&&($decoded['release_id']??null)===$id,'embedded_release_json');
pbmValidate($manifestPath, $archive);
$denials = [
    static function (array &$m): void { $m['runtime']['business_runtime_activation_ready'] = false; },
    static function (array &$m): void { $m['runtime']['source_ci_certified'] = false; },
    static function (array &$m): void { $m['runtime']['host_business_acceptance_verified'] = true; },
    static function (array &$m): void { $m['promotion_gate']['production_traffic_activation_authorized'] = true; },
    static function (array &$m): void { $m['operational_boundary']['deployment_authority'] = 'GRANTED'; },
    static function (array &$m): void { $m['build']['provenance_reference'] = 'local://counterfeit'; },
    static function (array &$m): void { $m['source']['commit_sha'] = str_repeat('c', 40); },
    static function (array &$m): void { $m['artifact']['sha256'] = str_repeat('0', 64); },
];
foreach ($denials as $index=>$mutate) {
    $copy = $manifest;
    $mutate($copy);
    $write($copy);
    $denied = false;
    try { pbmValidate($manifestPath, $archive); } catch (Throwable) { $denied = true; }
    $assert($denied, 'rejected_mutation_'.$index);
}
$write($manifest);
file_put_contents($archive, 'tampered', FILE_APPEND);
$denied = false;
try { pbmValidate($manifestPath, $archive); } catch (Throwable) { $denied = true; }
$assert($denied, 'tampered_archive');
foreach ([$manifestPath, $archive, $tarPath] as $p) @unlink($p);
@rmdir($root);
fwrite(STDOUT, "production_business_release_certification_negative_regression_pass\n");
