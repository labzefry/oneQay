<?php
// Author by Lab | zefry. Synthetic deployment contract only; never connects to hosting.
declare(strict_types=1);
function nNeed(bool $ok,string $code):void{if(!$ok)throw new RuntimeException($code);}
function nHash(string $path,string $expected):void{nNeed(is_file($path)&&hash_equals($expected,hash_file('sha256',$path)),'SOURCE_OR_BINDING_CHANGED');}
function nSchema($db):array{return $db->schema;}
require __DIR__.'/../cpanel/menu-book-migrations.php';
$n=0;$assert=static function(bool $ok)use(&$n){if(!$ok)throw new RuntimeException('ASSERTION_FAILED');$n++;};
$before=['ddl'=>[],'receipts'=>[]];for($i=0;$i<108;$i++)$before['ddl']['old_'.$i]=hash('sha256',(string)$i);for($i=0;$i<41;$i++)$before['receipts'][]=['id'=>$i+1,'migration'=>'old_'.$i,'batch'=>1];
$after=$before;foreach(mbNewTables()as$t)$after['ddl'][$t]=hash('sha256',$t);foreach(mbMigrationFiles()as$i=>$f)$after['receipts'][]=['id'=>42+$i,'migration'=>basename($f,'.php'),'batch'=>2+$i];
mbVerifySchema($before,$after);$assert(true);
$cases=[];$v=$after;unset($v['ddl'][mbNewTables()[0]]);$cases[]=$v;$v=$after;$v['ddl']['old_0']='changed';$cases[]=$v;$v=$after;$v['receipts'][0]['migration']='changed';$cases[]=$v;$v=$after;$v['receipts'][42]['migration']='wrong';$cases[]=$v;$v=$after;$v['ddl']['unexpected']='extra';$cases[]=$v;
foreach($cases as$bad){try{mbVerifySchema($before,$bad);$assert(false);}catch(RuntimeException){$assert(true);}}
$db=new class($after){public function __construct(public array $schema){} public function getName(){return 'synthetic';}};
$app=new class{public function make($x){throw new RuntimeException('REPLAY_RAN_MIGRATIONS');}};
$assert(mbApplyMigrations($app,$db,__DIR__,$before)===$after);
$db->schema=$after;unset($db->schema['ddl'][mbNewTables()[0]]);try{mbApplyMigrations($app,$db,__DIR__,$before);$assert(false);}catch(RuntimeException $e){$assert($e->getMessage()==='ADDITIVE_SCHEMA_INCOMPLETE');}
echo 'MENU_MIGRATION_CONTRACT_PASS assertions='.$n.PHP_EOL;
