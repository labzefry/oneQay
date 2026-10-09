<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3).'/tools/production/ProductionBusinessRuntimeGate.php';

use OneQay\Production\ProductionBusinessRuntimeGate as Gate;

// Author by Lab | zefry
$source=str_repeat('a',40);
$binding=['app_env'=>'production','app_debug'=>false,'configured_runtime'=>'production',
'persistence_enabled'=>true,'session_control_enabled'=>true,'session_idle_ttl'=>7200,
'session_absolute_ttl'=>43200,'activation_enabled'=>true,'traffic_authorized'=>true,
'environment_id'=>'oneqay-production-test-01','source_commit'=>$source,'artifact_sha256'=>str_repeat('b',64)];
$release=['environment'=>'PRODUCTION','required_runtime_class'=>'production','production'=>true,
'production_data_allowed'=>true,'business_runtime_activation_ready'=>true,
'source_commit'=>$source,'release_id'=>'production-'.substr($source,0,12)];
$checks=0;
function checkRuntime(bool $passes,string $label):void{global $checks;$checks++;if(!$passes)throw new RuntimeException($label);}
foreach(['local','test','ci'] as $dev) checkRuntime(Gate::allowsWithEvidence($dev,[],[]),'legacy-'.$dev);
foreach(['durable-staging','unknown','','staging'] as $bad) checkRuntime(!Gate::allowsWithEvidence($bad,$binding,$release),'deny-'.$bad);
checkRuntime(Gate::allowsWithEvidence('production',$binding,$release),'complete-provenance');
foreach(array_keys($binding) as $k){$copy=$binding;unset($copy[$k]);checkRuntime(!Gate::allowsWithEvidence('production',$copy,$release),'required-binding-'.$k);}
foreach(array_keys($release) as $k){$copy=$release;unset($copy[$k]);checkRuntime(!Gate::allowsWithEvidence('production',$binding,$copy),'required-release-'.$k);}
foreach(['activation_enabled'=>false,'traffic_authorized'=>false,'app_debug'=>true,'source_commit'=>str_repeat('c',40)] as $k=>$v){$copy=$binding;$copy[$k]=$v;checkRuntime(!Gate::allowsWithEvidence('production',$copy,$release),'denied-binding-'.$k);}
foreach(['business_runtime_activation_ready'=>false,'source_commit'=>str_repeat('d',40),'environment'=>'PREVIEW'] as $k=>$v){$copy=$release;$copy[$k]=$v;checkRuntime(!Gate::allowsWithEvidence('production',$binding,$copy),'denied-release-'.$k);}
checkRuntime(!Gate::allows('production'),'unbootstrapped-production-must-deny');
echo 'production_business_runtime_gate_contract_pass:'.$checks."\n";
