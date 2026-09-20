<?php
declare(strict_types=1);

// Author by Lab | zefry
function prodManifestFail(string $c): never { throw new RuntimeException($c); }
function prodManifestLoad(string $p): array { if(!is_file($p)||is_link($p))prodManifestFail('input'); try{$v=json_decode((string)file_get_contents($p),true,64,JSON_THROW_ON_ERROR);}catch(JsonException){prodManifestFail('json');} if(!is_array($v)||array_is_list($v))prodManifestFail('shape'); return $v; }
function prodManifestEq(mixed $a,mixed $e,string $c):void{if($a!==$e)prodManifestFail($c);}
function prodManifestValidate(string $mp,string $ap):void{
 $m=prodManifestLoad($mp);$s=$m['source']['commit_sha']??null;if(!is_string($s)||preg_match('/\A[0-9a-f]{40}\z/',$s)!==1)prodManifestFail('source');
 prodManifestEq($m['manifest_version']??null,1,'version');prodManifestEq($m['schema_id']??null,'oneqay.production-release-manifest.v1','schema');prodManifestEq($m['release']['id']??null,'production-'.substr($s,0,12),'id');prodManifestEq($m['release']['channel']??null,'PRODUCTION_CANDIDATE','channel');prodManifestEq($m['release']['production']??null,true,'production');prodManifestEq($m['runtime']['required_runtime_class']??null,'production','runtime');prodManifestEq($m['runtime']['governed_business_runtime_default_enabled']??null,false,'gate');prodManifestEq($m['migration']['expected_count']??null,27,'migrations');prodManifestEq($m['migration']['execution_authorized']??null,false,'migration_authority');prodManifestEq($m['promotion_gate']['verified_durable_staging_evidence_required']??null,true,'staging');prodManifestEq($m['promotion_gate']['production_traffic_activation_authorized']??null,false,'traffic');prodManifestEq($m['operational_boundary']['environment_deployment']??null,'NOT_PERFORMED','deploy');prodManifestEq($m['operational_boundary']['production_activation']??null,'NOT_AUTHORIZED','activation');prodManifestEq($m['attribution']??null,'Lab | zefry','attribution');
 if(!is_file($ap)||is_link($ap))prodManifestFail('archive');$sha=$m['artifact']['sha256']??null;if(!is_string($sha)||!hash_equals($sha,hash_file('sha256',$ap)))prodManifestFail('hash');prodManifestEq($m['artifact']['size_bytes']??null,filesize($ap),'size');
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){if($argc!==3){exit(64);}try{prodManifestValidate($argv[1],$argv[2]);fwrite(STDOUT,"production_release_manifest_valid\n");exit(0);}catch(Throwable$e){fwrite(STDERR,"production_release_manifest_invalid:".$e->getMessage()."\n");exit(1);}}
