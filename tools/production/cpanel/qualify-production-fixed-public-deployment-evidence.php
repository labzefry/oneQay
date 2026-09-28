<?php
declare(strict_types=1);

// Author by Lab | zefry

function pfpEvFail(string $c): never { throw new RuntimeException($c); }
function pfpEvLoad(string $p): array {
    if ($p==='' || !is_file($p) || is_link($p) || !is_readable($p)) pfpEvFail('input');
    try { $v=json_decode((string)file_get_contents($p),true,64,JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpEvFail('json'); }
    if (!is_array($v) || array_is_list($v)) pfpEvFail('shape');
    return $v;
}
function pfpEvEq(mixed $a,mixed $e,string $c): void { if ($a!==$e) pfpEvFail($c); }
function pfpEvBool(mixed $a,bool $e,string $c): void { if (!is_bool($a) || $a!==$e) pfpEvFail($c); }
function pfpEvQualify(string $pp,string $ep): array {
    $p=pfpEvLoad($pp); $e=pfpEvLoad($ep);
    pfpEvEq($p['plan_state']??null,'QUALIFIED_FOR_PRODUCTION_DEPLOYMENT_NOT_EXECUTED_NOT_ACTIVATED','plan');
    pfpEvEq($p['plan_variant']??null,'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1','plan_variant');
    pfpEvEq($e['evidence_state']??null,'PRODUCTION_DEPLOYMENT_EVIDENCE_CANDIDATE','evidence');
    pfpEvEq($e['evidence_variant']??null,'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1','evidence_variant');
    pfpEvEq($e['deployment_plan_fingerprint']??null,$p['plan_fingerprint']??null,'fingerprint');
    pfpEvEq($e['environment_id']??null,$p['target']['environment_id']??null,'environment');
    pfpEvEq($e['runtime_class']??null,'production','runtime');
    pfpEvEq($e['release_id']??null,$p['artifact']['release_id']??null,'release');
    pfpEvEq($e['source_commit']??null,$p['artifact']['source_commit']??null,'source');
    pfpEvEq($e['artifact_sha256']??null,$p['artifact']['artifact_sha256']??null,'artifact');
    pfpEvEq($e['presentation']['document_root_mode']??null,'FIXED_PUBLIC_BRIDGE','presentation');
    pfpEvBool($e['presentation']['private_rollback_snapshot_retained']??null,true,'rollback_snapshot');
    foreach (['preflight_passed','previous_active_release_preserved','immutable_release_extracted','public_document_root_verified','external_runtime_configuration_bound','provenance_readback_verified','configuration_read_before_write_verified','configuration_read_after_write_verified','non_mutating_health_attestation_verified','rollback_path_verified','candidate_restored_after_rollback_rehearsal','fixed_public_bridge_verified','provider_disable_functions_policy_respected'] as $f) pfpEvBool($e['verification'][$f]??null,true,$f);
    pfpEvEq($e['runtime_readback']['exact_running_source_commit']??null,$p['artifact']['source_commit']??null,'readback_source');
    pfpEvEq($e['runtime_readback']['exact_running_artifact_sha256']??null,$p['artifact']['artifact_sha256']??null,'readback_artifact');
    pfpEvBool($e['runtime_readback']['business_runtime_activation_ready']??null,false,'business_runtime');
    pfpEvBool($e['runtime_readback']['production_traffic_active']??null,false,'traffic');
    pfpEvEq($e['operational_boundary']['migration27_execution']??null,'ALREADY_EXECUTED_NO_REPLAY','migration');
    pfpEvEq($e['operational_boundary']['permission_provisioning']??null,'ALREADY_PROVISIONED_NO_REPLAY','permission');
    pfpEvEq($e['operational_boundary']['feature_activation']??null,'ACTIVE_PRESERVED_NO_REACTIVATION','feature');
    pfpEvEq($e['operational_boundary']['production_business_traffic_activation']??null,'NOT_AUTHORIZED','traffic_boundary');
    pfpEvEq($e['operational_boundary']['target_reselection']??null,'NOT_AUTHORIZED','target_boundary');
    pfpEvBool($e['secrets_embedded']??null,false,'secret');
    return [
        'schema_version'=>1,'evidence_state'=>'PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED',
        'evidence_variant'=>'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1',
        'environment_id'=>$e['environment_id'],'runtime_class'=>'production','release_id'=>$e['release_id'],
        'source_commit'=>$e['source_commit'],'artifact_sha256'=>$e['artifact_sha256'],
        'deployment_plan_fingerprint'=>$e['deployment_plan_fingerprint'],'presentation'=>$e['presentation'],
        'verification'=>$e['verification'],'runtime_readback'=>$e['runtime_readback'],
        'operational_boundary'=>$e['operational_boundary'],'secrets_embedded'=>false,'attribution'=>'Lab | zefry'
    ];
}
function pfpEvWrite(string $p,array $v): void {
    $j=json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    if (file_put_contents($p,$j,LOCK_EX)!==strlen($j)) pfpEvFail('write'); @chmod($p,0600);
}
if (PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__) {
    if ($argc!==4) exit(64);
    try { pfpEvWrite($argv[3],pfpEvQualify($argv[1],$argv[2])); fwrite(STDOUT,"production_fixed_public_deployment_evidence_qualified_not_activated\n"); exit(0); }
    catch (Throwable $e) { fwrite(STDERR,"production_fixed_public_deployment_evidence_failed:".$e->getMessage()."\n"); exit(1); }
}
