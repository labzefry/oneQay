<?php
declare(strict_types=1);

require_once dirname(__DIR__).'/app/Infrastructure/Runtime/ProductionBusinessRuntimeGate.php';

use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate as RuntimeGate;
// Author by Lab | zefry. Source guard integration and forced dark-release denial.
$tests=0;
function verify(bool $pass,string $msg):void{global $tests; ++$tests; if(!$pass){fwrite(STDERR, 'FAIL:'.$msg."\n");exit(1);}}
$source=str_repeat('a',40);
$e=['app_env'=>'production','app_debug'=>false,'configured_runtime'=>'production','persistence_enabled'=>true,'session_control_enabled'=>true,'session_idle_ttl'=>7200,'session_absolute_ttl'=>43200,'activation_enabled'=>true,'traffic_authorized'=>true,'environment_id'=>'oneqay-production-test-01','source_commit'=>$source,'artifact_sha256'=>str_repeat('b',64)];
$r=['environment'=>'PRODUCTION','required_runtime_class'=>'production','production'=>true,'production_data_allowed'=>true,'business_runtime_activation_ready'=>true,'source_commit'=>$source,'release_id'=>'production-'.substr($source,0,12)];
verify(RuntimeGate::allowsWithEvidence('production',$e,$r),'positive certified release and authority');
foreach(['local','test','ci'] as $dev) verify(RuntimeGate::allowsWithEvidence($dev,[],[]),'preserved legacy '.$dev);
foreach(['durable-staging','preview','','sandbox'] as $bad) verify(!RuntimeGate::allowsWithEvidence($bad,$e,$r),'unqualified runtime '.$bad);
foreach($e as $key=>$value){$bad=$e;unset($bad[$key]);verify(!RuntimeGate::allowsWithEvidence('production',$bad,$r),'binding required '.$key);}
foreach($r as $key=>$value){$bad=$r;unset($bad[$key]);verify(!RuntimeGate::allowsWithEvidence('production',$e,$bad),'release required '.$key);}
foreach(['traffic_authorized'=>false,'activation_enabled'=>false,'app_debug'=>true,'persistence_enabled'=>false,'source_commit'=>str_repeat('c',40)] as $key=>$value){$bad=$e;$bad[$key]=$value;verify(!RuntimeGate::allowsWithEvidence('production',$bad,$r),'binding denial '.$key);}
foreach(['business_runtime_activation_ready'=>false,'production_data_allowed'=>false,'source_commit'=>str_repeat('d',40)] as $key=>$value){$bad=$r;$bad[$key]=$value;verify(!RuntimeGate::allowsWithEvidence('production',$e,$bad),'release denial '.$key);}
$paths=[
'../../../apps/web/routes/web.php','../../../apps/web/app/Delivery/Http/Middleware/RequirePosSessionContextMiddleware.php',
'../../../apps/web/app/Delivery/Http/Identity/FirstPartySessionController.php',
'../../../apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
'../../../apps/web/app/Infrastructure/Pos/LaravelCloseShiftRepository.php',
'../../../apps/web/app/Providers/PosOperationsHubServiceProvider.php',
'../../../apps/web/app/Providers/FinalShiftCloseServiceProvider.php',
];
foreach($paths as $p){
 $file=realpath(__DIR__.'/'.$p);
 verify(is_string($file),'core file exists '.$p);
 $code=file_get_contents($file);
 verify(is_string($code)&&str_contains($code,'ProductionBusinessRuntimeGate::allows'),'native production guarded in '.$p);
 verify(str_contains($code,"['local', 'test', 'ci']"),'legacy boundary remains visible '.$p);
}
$manifest=dirname(__DIR__,3).'/tools/build-production-release.sh';
$builder=file_get_contents($manifest);
verify(str_contains((string)$builder,'"business_runtime_activation_ready":false'),'legacy dark-builder must remain dark only');
echo 'production_commercial_runtime_guard_integration_pass:'.$tests."\n";
