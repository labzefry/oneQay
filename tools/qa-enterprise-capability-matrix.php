<?php
declare(strict_types=1);
// Author by Lab | zefry. Assert grounded capability inventory; no host mutations.
$root=dirname(__DIR__);
$file=$root.'/ops/product/ENTERPRISE_CAPABILITY_MATRIX_2026-10-12.json';
$md=$root.'/docs/operations/ONEQAY_ENTERPRISE_CAPABILITY_MATRIX_2026-10-12.md';
$assert=static function(bool $ok,string $why):void {if(!$ok){fwrite(STDERR,"FAIL: $why\n");exit(1);}};
$data=json_decode((string)file_get_contents($file),true,64,JSON_THROW_ON_ERROR);
$assert(($data['schema']??null)==='oneqay.enterprise-capability-matrix.v1','Matrix schema changed.');
$assert(($data['baseline_main']??null)==='d15f2fc3f9aa9320670657d03b044e132bf812a2','Unreconciled source identity.');
$assert(($data['source_only_not_host_proof']??false)===true,'Accidental host acceptance claim.');
$assert(count($data['rows']??[])>=55,'One or more enterprise capability families missing.');
$allowed=['MISSING','PARTIAL','IMPLEMENTED','CI_VERIFIED','RELEASE_CERTIFIED','DEPLOYED','ACCEPTANCE_VERIFIED','BLOCKED'];
$stages=[];$ids=[];
foreach($data['rows'] as $row){
  $stage=$row['stage']??'';
  $assert(in_array($stage,['E0','E1','E2','E3','E4','E5'],true),'Unknown stage.');
  $stages[$stage]=true;
  $id=$row['id']??'';$assert(is_string($id)&&!isset($ids[$id]),'Missing/duplicate ID.');
  $ids[$id]=true;
  $assert(in_array($row['status']??'', $allowed,true),'Invalid capability status.');
  foreach(['requirement','code','ui','api','persistence','permission','tests','operational_dependency','release_artifact','host_deployment','acceptance_evidence','blocker','next_action'] as $key){$assert(array_key_exists($key,$row),'Missing evidence field: '.$key);}
  foreach(['code','ui','api','persistence','tests'] as $category){
    $assert(is_array($row[$category]),'Paths must be list: '.$id);
    foreach($row[$category] as $path){
      $assert(is_string($path)&&$path!==''&&!str_contains($path,'..'),'Unsafe evidence path.');
      $assert(is_file($root.'/'.$path),'Missing source evidence file: '.$path);
    }
  }
  $assert($row['host_deployment']==='NOT_VERIFIED_FOR_D15F; last reported host source production-e93b7fef42cb','Unexpected host promotion claim.');
  $assert($row['acceptance_evidence']==='NOT_VERIFIED_ON_REAL_HOST','Unverified acceptance promoted.');
  if($row['status']==='MISSING')$assert($row['code']===[] && $row['ui']===[],'Placeholder counted as completed feature.');
}
$assert(count($stages)===6,'E0–E5 must all be represented.');
$human=(string)file_get_contents($md);
foreach(array_keys($ids) as $id)$assert(str_contains($human,$id),'Human inventory missing '.$id);
$assert(str_contains($human,'backend-capable')&&str_contains($human,'CASH'), 'Missing deployment blocker and real business acceptance.');
echo "oneQay Enterprise capability inventory: ".count($ids)." rows, all E0–E5, evidence and no false host claims PASS.\n";
