<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact 36/87 predecessor; one category table and two default-true flags only.
function uModulePaths(string $app):array{return [$app.'/database/module-migrations/business/2026_10_21_000001_create_product_categories.php',$app.'/database/module-migrations/pos/2026_10_21_000001_add_explicit_stock_tracking.php'];}
function uModuleTables():array{return ['oneqay_business_product_categories'];}
function uStockColumns():array{return ['oneqay_pos_sale_catalog_items'=>'tracks_stock','oneqay_pos_sale_lines'=>'stock_deducted'];}
function uSchemaSnapshot($db):array {
 $schema=$db->getSchemaBuilder();$tables=$schema->getTableListing($db->getDriverName()==='mysql'?$db->getDatabaseName():null,false);sort($tables);$ddl=[];$stock=[];
 foreach($tables as$table){uAssert(preg_match('/\A[A-Za-z0-9_]+\z/D',$table)===1,'DATABASE_TABLE_NAME_INVALID');
  if($db->getDriverName()==='mysql'){$raw=array_values((array)$db->selectOne('SHOW CREATE TABLE `'.$table.'`'))[1];$raw=preg_replace('/ AUTO_INCREMENT=\d+/','',$raw);}
  else $raw=(string)($db->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',['table',$table])->sql??'');
  $ddl[$table]=hash('sha256',$raw);
  if(isset(uStockColumns()[$table]))$stock[$table]=['columns'=>$schema->getColumns($table),'indexes'=>$schema->getIndexes($table),'foreign_keys'=>$schema->getForeignKeys($table)];
 }
 return ['ddl'=>$ddl,'stock'=>$stock,'receipts'=>$db->table('migrations')->orderBy('id')->get()->map(static fn($v)=>(array)$v)->all()];
}
function uModulePlan($db,string $appPath,array $before):array {
 $files=glob($appPath.'/database/migrations/*.php');sort($files);uAssert(count($files)===27,'HISTORICAL_SCHEMA_COUNT_CHANGED');$expected=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$files);
 foreach(['pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php','business/2026_10_12_000001_create_integrated_business_management.php','platform/2026_10_13_000001_create_platform_and_notification.php','business/2026_10_14_000001_create_merchandising_and_currency_preferences.php','platform/2026_10_15_000001_create_public_website.php','platform/2026_10_16_000001_create_control_plane_operations.php','pos/2026_10_18_000001_create_desktop_pos_sync.php','pos/2026_10_19_000001_create_desktop_pos_operations.php','business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php']as$f){uAssert(is_file($appPath.'/database/module-migrations/'.$f),'PREDECESSOR_MODULE_FILE_MISSING');$expected[]=pathinfo($f,PATHINFO_FILENAME);}
 uAssert(array_column($before['receipts'],'migration')===$expected,'EXACT_THIRTY_SIX_RECEIPTS_REQUIRED');uAssert(count($before['ddl'])===87,'EXACT_EIGHTY_SEVEN_TABLES_REQUIRED');
 uAssert(!isset($before['ddl']['oneqay_business_product_categories']),'CATEGORY_SCHEMA_ALREADY_PRESENT');
 foreach(uStockColumns()as$table=>$column)uAssert(!$db->getSchemaBuilder()->hasColumn($table,$column),'STOCK_SCHEMA_ALREADY_PRESENT');
 foreach(uModulePaths($appPath)as$p)uAssert(is_file($p)&&!is_link($p),'MAIL_STOCK_MIGRATION_MISSING');return ['paths'=>uModulePaths($appPath),'preserved_replenishment'=>true];
}
function uAssertHistoricalPreserved(array $before,array $after):void {
 foreach($before['ddl']as$table=>$hash){
  uAssert(isset($after['ddl'][$table]),'HISTORICAL_TABLE_REMOVED');
  if(!isset(uStockColumns()[$table])){uAssert($after['ddl'][$table]===$hash,'HISTORICAL_DATABASE_DDL_CHANGED');continue;}
  $b=$before['stock'][$table];$a=$after['stock'][$table];$column=uStockColumns()[$table];$found=array_values(array_filter($a['columns'],static fn($c)=>$c['name']===$column));
  uAssert(count($found)===1&&!$found[0]['nullable']&&in_array(strtolower((string)$found[0]['default']),['1',"'1'",'true'],true)&&in_array(strtolower($found[0]['type_name']),['tinyint','boolean'],true),'DEFAULT_TRUE_STOCK_FLAG_INVALID');
  $a['columns']=array_values(array_filter($a['columns'],static fn($c)=>$c['name']!==$column));uAssert($a===$b,'EXISTING_STOCK_COLUMNS_OR_CONSTRAINTS_CHANGED');
 }
 uAssert(array_slice($after['receipts'],0,36)===$before['receipts'],'HISTORICAL_MIGRATION_RECEIPTS_CHANGED');
 uAssert(array_values(array_diff(array_keys($after['ddl']),array_keys($before['ddl'])))===uModuleTables(),'MAIL_STOCK_TABLE_SET_INVALID');
 uAssert(count($after['receipts'])===38&&count($after['ddl'])===88,'MAIL_STOCK_SCHEMA_TOTAL_INVALID');
 uAssert(array_column(array_slice($after['receipts'],36),'migration')===['2026_10_21_000001_create_product_categories','2026_10_21_000001_add_explicit_stock_tracking'],'MAIL_STOCK_RECEIPTS_INVALID');
}
/** Private, consistent logical snapshot. Never return payloads or credentials in the deployment report. */
function uPrivateDatabaseBackup($db,string $file):array {
 uAssert(!file_exists($file)&&!is_link($file)&&is_dir(dirname($file))&&!is_link(dirname($file)),'BACKUP_PATH_INVALID');$start=time();$mask=umask(0077);$out=gzopen($file,'wb6');umask($mask);uAssert(is_resource($out)&&chmod($file,0600),'DATABASE_BACKUP_CREATE_FAILED');$hash=hash_init('sha256');$rows=0;$tables=[];$pdo=$db->getPdo();
 $write=static function(string $s)use($out,$hash):void{hash_update($hash,$s);uAssert(gzwrite($out,$s)===strlen($s),'DATABASE_BACKUP_WRITE_FAILED');};
 try{
  uAssert($db->transactionLevel()===0,'BACKUP_TRANSACTION_ALREADY_OPEN');
  if($db->getDriverName()==='mysql'){$db->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$db->statement('START TRANSACTION WITH CONSISTENT SNAPSHOT');}else $db->beginTransaction();
  $write("-- oneQay private pre-update backup; restore into an isolated empty database only\n");$write($db->getDriverName()==='mysql'?"SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n":"PRAGMA foreign_keys=OFF;\n");$listing=$db->getSchemaBuilder()->getTableListing($db->getDriverName()==='mysql'?$db->getDatabaseName():null,false);sort($listing);
  foreach($listing as$table){uAssert(preg_match('/\A[A-Za-z0-9_]+\z/D',$table)===1,'DATABASE_TABLE_NAME_INVALID');uAssert(time()-$start<180,'DATABASE_BACKUP_TIME_LIMIT');
   if($db->getDriverName()==='mysql')$ddl=array_values((array)$db->selectOne('SHOW CREATE TABLE `'.$table.'`'))[1];else $ddl=$db->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',['table',$table])->sql;
   $write($ddl.";\n");$tables[$table]=0;
   foreach($db->table($table)->cursor()as$row){$record=(array)$row;$columns=[];$values=[];foreach($record as$k=>$v){uAssert(preg_match('/\A[A-Za-z0-9_]+\z/D',$k)===1,'DATABASE_COLUMN_NAME_INVALID');$columns[]='`'.$k.'`';$values[]=$v===null?'NULL':$pdo->quote((string)$v);}
    $write('INSERT INTO `'.$table.'` ('.implode(',',$columns).') VALUES ('.implode(',',$values).");\n");$rows++;$tables[$table]++;
   }
  }
  $write($db->getDriverName()==='mysql'?"SET FOREIGN_KEY_CHECKS=1;\n":"PRAGMA foreign_keys=ON;\n");if($db->getDriverName()==='mysql')$db->statement('COMMIT');else $db->commit();
  uAssert(gzclose($out),'DATABASE_BACKUP_CLOSE_FAILED');$out=null;$want=hash_final($hash);$check=hash_init('sha256');$in=gzopen($file,'rb');uAssert(is_resource($in),'DATABASE_BACKUP_READ_FAILED');try{while(!gzeof($in)){$part=gzread($in,1048576);uAssert(is_string($part),'DATABASE_BACKUP_VERIFY_FAILED');hash_update($check,$part);}}finally{gzclose($in);}uAssert(hash_equals($want,hash_final($check)),'DATABASE_BACKUP_VERIFY_FAILED');
  return ['state'=>'PRIVATE_BACKUP_VERIFIED','file'=>basename($file),'sha256'=>(string)hash_file('sha256',$file),'logical_sha256'=>$want,'tables'=>count($tables),'rows'=>$rows,'mode'=>'0600','created_at'=>time()];
 }catch(Throwable $e){try{if($db->getDriverName()==='mysql')$db->statement('ROLLBACK');elseif($db->transactionLevel()>0)$db->rollBack();}catch(Throwable){}throw $e;}finally{if(is_resource($out))gzclose($out);}
}
function uAssertRollbackSafe($db):void {
 // Never race a concurrent unlimited sale by promoting an obsolete application after a point-in-time check.
 uAssert(!$db->getSchemaBuilder()->hasColumn('oneqay_pos_sale_lines','stock_deducted'),'STOCK_SCHEMA_EXISTS_FORWARD_FIX_REQUIRED');
}
function uApplyModuleSchema($app,string $appPath,callable $checkpoint):array {
 $db=$app->make('db')->connection();$before=uSchemaSnapshot($db);$plan=uModulePlan($db,$appPath,$before);$names=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$plan['paths']);
 $checkpoint(['state'=>'BOUNDED_SCHEMA_STARTED','historical_schema'=>$before,'module_migrations'=>$names]);foreach($plan['paths']as$migration)$app->make('migrator')->run([$migration],['step'=>true]);uAssertHistoricalPreserved($before,uSchemaSnapshot($db));
 foreach(uStockColumns()as$table=>$column)uAssert(!$db->table($table)->where($column,false)->exists(),'LEGACY_STOCK_EFFECT_CHANGED');
 $receipt=['state'=>'BOUNDED_SCHEMA_APPLIED','historical_schema'=>$before,'module_migrations'=>$names,'module_tables'=>uModuleTables(),'installed_migration_count'=>2,'preserved_replenishment'=>true];$checkpoint($receipt);return $receipt;
}
function uInstallModules(array $r):array {
 $p=uPaths($r);$file=__DIR__.'/desktop-pos-installation.json';uAssert(!file_exists($file)&&!is_link($file),'SCHEMA_INSTALLATION_ALREADY_STARTED');$db=app('db')->connection();uModulePlan($db,$p['app'],uSchemaSnapshot($db));
 $backup=uPrivateDatabaseBackup($db,__DIR__.'/rollback/database.sql.gz');uAssert($backup['tables']===87,'BACKUP_TABLE_COUNT_CHANGED');uWrite(__DIR__.'/database-backup.json',json_encode($backup,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
 $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];$write=static function(array $s)use($binding,$file):void{uWrite($file,json_encode($binding+$s,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
 $receipt=uApplyModuleSchema(app(),$p['app'],$write);uVerifyModuleReceipt($r);return $receipt;
}
function uVerifyModuleReceipt(array $r):void {
 $receipt=uJson(__DIR__.'/desktop-pos-installation.json');uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='BOUNDED_SCHEMA_APPLIED'&&($receipt['installed_migration_count']??null)===2&&($receipt['module_migrations']??null)===['2026_10_21_000001_create_product_categories','2026_10_21_000001_add_explicit_stock_tracking'],'SCHEMA_INSTALLATION_INCOMPLETE');uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
