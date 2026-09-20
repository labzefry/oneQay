<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionDeploymentHandoffException extends RuntimeException {}
function prodHandoffFail(string $code): never { throw new ProductionDeploymentHandoffException($code); }
function prodHandoffLoad(string $path): array {
    if ($path === '' || !is_file($path) || is_link($path) || !is_readable($path)) prodHandoffFail('input_unavailable');
    $raw=file_get_contents($path);
    if(!is_string($raw) || strlen($raw)<2 || strlen($raw)>10485760) prodHandoffFail('input_invalid');
    try{$v=json_decode($raw,true,64,JSON_THROW_ON_ERROR);}catch(JsonException){prodHandoffFail('input_json_invalid');}
    if(!is_array($v)||array_is_list($v)) prodHandoffFail('input_shape_invalid');
    return $v;
}
function prodHandoffLit(mixed $v,mixed $e,string $c): void { if($v!==$e) prodHandoffFail($c); }
function prodHandoffPat(mixed $v,string $p,string $c): string { if(!is_string($v)||preg_match($p,$v)!==1) prodHandoffFail($c); return $v; }
function prodHandoffShaFile(string $path): string { $h=hash_file('sha256',$path); if(!is_string($h)) prodHandoffFail('hash_failed'); return $h; }
function prodHandoffWrite(string $path,array $data): void {
    $dir=dirname($path);
    if(!is_dir($dir)||!is_writable($dir)||is_link($path)||is_dir($path)) prodHandoffFail('output_invalid');
    $json=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp=tempnam($dir,'.oneqay-production-handoff-');
    if($tmp===false) prodHandoffFail('output_temp_failed');
    try {
        if(file_put_contents($tmp,$json,LOCK_EX)!==strlen($json)) prodHandoffFail('output_write_failed');
        @chmod($tmp,0600);
        if(!rename($tmp,$path)) prodHandoffFail('output_commit_failed');
        @chmod($path,0600);
    } finally { if(is_file($tmp)) @unlink($tmp); }
}
function prodHandoffPrepare(string $manifestPath,string $archivePath,string $sourceSha,string $proofPath): array {
    $m=prodHandoffLoad($manifestPath);
    $p=prodHandoffLoad($proofPath);
    prodHandoffLit($m['manifest_version']??null,1,'manifest_version_invalid');
    prodHandoffLit($m['schema_id']??null,'oneqay.production-release-manifest.v1','manifest_schema_invalid');
    prodHandoffLit($m['product']['name']??null,'oneQay','product_invalid');
    prodHandoffLit($m['product']['repository']??null,'labzefry/oneQay','repository_invalid');
    $release=prodHandoffPat($m['release']['id']??null,'/\Aproduction-[0-9a-f]{12}\z/','release_invalid');
    $source=prodHandoffPat($m['source']['commit_sha']??null,'/\A[0-9a-f]{40}\z/','source_invalid');
    prodHandoffLit($source,$sourceSha,'source_argument_mismatch');
    prodHandoffLit($release,'production-'.substr($source,0,12),'release_source_mismatch');
    prodHandoffLit($m['release']['channel']??null,'PRODUCTION','channel_invalid');
    prodHandoffLit($m['release']['environment']??null,'PRODUCTION','environment_invalid');
    prodHandoffLit($m['release']['production']??null,true,'production_invalid');
    prodHandoffLit($m['release']['production_data_allowed']??null,true,'production_data_invalid');
    prodHandoffLit($m['release']['synthetic_fixture_runtime']??null,false,'synthetic_invalid');
    prodHandoffLit($m['runtime']['required_runtime_class']??null,'production','runtime_invalid');
    $artifact=prodHandoffPat($m['artifact']['sha256']??null,'/\A[0-9a-f]{64}\z/','artifact_sha_invalid');
    prodHandoffLit(prodHandoffShaFile($archivePath),$artifact,'archive_sha_mismatch');
    $manifestSha=prodHandoffShaFile($manifestPath);
    $payload=prodHandoffPat($m['application_payload']['sha256']??null,'/\A[0-9a-f]{64}\z/','payload_sha_invalid');
    prodHandoffLit($m['application_payload']['staging_equivalence_required']??null,true,'staging_equivalence_required');
    prodHandoffLit($m['migration']['expected_count']??null,27,'migration_count_invalid');
    prodHandoffLit($m['migration']['execution_state']??null,'NOT_EXECUTED_BY_ARTIFACT_BUILD','migration_state_invalid');
    prodHandoffLit($m['migration']['execution_authorized']??null,false,'migration_authorized');
    prodHandoffLit($p['schema_version']??null,1,'proof_schema_invalid');
    prodHandoffLit($p['state']??null,'STAGING_APPLICATION_PAYLOAD_EQUIVALENCE_VERIFIED','proof_state_invalid');
    prodHandoffLit($p['staging_artifact_id']??null,10597712890,'staging_artifact_id_invalid');
    prodHandoffLit($p['staging_release_id']??null,'durable-staging-e37300d5d1be','staging_release_invalid');
    prodHandoffLit($p['staging_source_commit']??null,'e37300d5d1be6727cdb5d818b6365c6429f2af9d','staging_source_invalid');
    prodHandoffLit($p['staging_artifact_sha256']??null,'faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079','staging_artifact_sha_invalid');
    prodHandoffLit($p['application_payload_sha256']??null,$payload,'payload_equivalence_mismatch');
    prodHandoffLit($p['production_release_id']??null,$release,'proof_release_mismatch');
    prodHandoffLit($p['production_artifact_sha256']??null,$artifact,'proof_artifact_mismatch');
    prodHandoffLit($p['attribution']??null,'Lab | zefry','proof_attribution_invalid');
    return [
      'schema_version'=>1,
      'handoff_state'=>'VALIDATED_FOR_PRODUCTION_DEPLOYMENT_NOT_AUTHORIZED',
      'product'=>['name'=>'oneQay','repository'=>'labzefry/oneQay'],
      'artifact'=>['release_id'=>$release,'source_commit'=>$source,'artifact_filename'=>basename($archivePath),'artifact_sha256'=>$artifact,'manifest_sha256'=>$manifestSha],
      'application_payload'=>['sha256'=>$payload,'staging_equivalence_verified'=>true],
      'staging_promotion_proof'=>['state'=>$p['state'],'staging_artifact_id'=>$p['staging_artifact_id'],'staging_release_id'=>$p['staging_release_id'],'staging_source_commit'=>$p['staging_source_commit'],'staging_artifact_sha256'=>$p['staging_artifact_sha256'],'application_payload_sha256'=>$payload],
      'runtime'=>['required_runtime_class'=>'production','production'=>true,'production_data_allowed'=>true,'synthetic_fixture_runtime'=>false],
      'migration'=>['expected_count'=>27,'execution_state'=>'NOT_PERFORMED','execution_authorized'=>false],
      'deployment'=>['execution_state'=>'NOT_PERFORMED','authority_state'=>'NOT_GRANTED','production_activation_state'=>'NOT_AUTHORIZED'],
      'operational_boundary'=>['environment_deployment'=>'NOT_PERFORMED','migration27_execution'=>'NOT_PERFORMED','production_activation'=>'NOT_AUTHORIZED','selected_target'=>null,'producer_dispatch'=>'NOT_PERFORMED'],
      'attribution'=>'Lab | zefry'
    ];
}
if(PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
    if($argc!==6){
        fwrite(STDERR,"Usage: php tools/prepare-production-deployment-handoff.php <manifest.json> <archive.tar.gz> <source-sha> <staging-promotion-proof.json> <handoff.json>\n");
        exit(64);
    }
    try{
        $out=prodHandoffPrepare($argv[1],$argv[2],$argv[3],$argv[4]);
        prodHandoffWrite($argv[5],$out);
        fwrite(STDOUT,"production_deployment_handoff_prepared_not_authorized\n");
    } catch(ProductionDeploymentHandoffException $e){
        fwrite(STDERR,"production_deployment_handoff_failed:".$e->getMessage()."\n");
        exit(1);
    } catch(Throwable){
        fwrite(STDERR,"production_deployment_handoff_failed:unexpected_failure\n");
        exit(1);
    }
}
