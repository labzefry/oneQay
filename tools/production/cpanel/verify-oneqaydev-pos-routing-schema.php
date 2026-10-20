<?php
declare(strict_types=1);
// Author by Lab | zefry. Existing 35-receipt/84-table schema verification only; never executes SQL migrations.
function uModulePaths(string $app):array {return [];}
function uModuleTables():array {return [];}
function uSchemaSnapshot($db): array {
    $schema=$db->getSchemaBuilder();$tables=$schema->getTableListing($db->getDriverName()==='mysql'?$db->getDatabaseName():null,false);sort($tables);$ddl=[];
    foreach($tables as $table){uAssert(preg_match('/\A[A-Za-z0-9_]+\z/D',$table)===1,'DATABASE_TABLE_NAME_INVALID');
        if($db->getDriverName()==='mysql'){$row=(array)$db->selectOne('SHOW CREATE TABLE `'.$table.'`');$raw=array_values($row)[1];$raw=preg_replace('/ AUTO_INCREMENT=\d+/','',$raw);}
        else {$raw=(string)($db->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',['table',$table])->sql??'');}
        $ddl[$table]=hash('sha256',$raw);
    }
    $rows=$db->table('migrations')->orderBy('id')->get()->map(static fn($v)=>(array)$v)->all();
    return ['ddl'=>$ddl,'receipts'=>$rows];
}
function uModulePlan($db,string $appPath,array $before):array {
    $files=glob($appPath.'/database/migrations/*.php');sort($files);uAssert(count($files)===27,'HISTORICAL_SCHEMA_COUNT_CHANGED');
    $expected=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$files);
    foreach([$appPath.'/database/module-migrations/pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php',$appPath.'/database/module-migrations/business/2026_10_12_000001_create_integrated_business_management.php',$appPath.'/database/module-migrations/platform/2026_10_13_000001_create_platform_and_notification.php',$appPath.'/database/module-migrations/business/2026_10_14_000001_create_merchandising_and_currency_preferences.php',$appPath.'/database/module-migrations/platform/2026_10_15_000001_create_public_website.php',$appPath.'/database/module-migrations/platform/2026_10_16_000001_create_control_plane_operations.php',$appPath.'/database/module-migrations/pos/2026_10_18_000001_create_desktop_pos_sync.php']as$file){uAssert(is_file($file)&&!is_link($file),'PREDECESSOR_MODULE_FILE_MISSING');$expected[]=pathinfo($file,PATHINFO_FILENAME);}
    $file=$appPath.'/database/module-migrations/pos/2026_10_19_000001_create_desktop_pos_operations.php';uAssert(is_file($file)&&!is_link($file),'PREDECESSOR_MODULE_FILE_MISSING');$expected[]=pathinfo($file,PATHINFO_FILENAME);
    uAssert(array_column($before['receipts'],'migration')===$expected,'EXACT_THIRTY_FIVE_RECEIPTS_REQUIRED');
    uAssert(count($before['ddl'])===84,'EXACT_EIGHTY_FOUR_TABLES_REQUIRED');
    foreach(['oneqay_desktop_cashiers','oneqay_desktop_preferences','oneqay_desktop_events'] as $table)uAssert(isset($before['ddl'][$table]),'EXISTING_ENTERPRISE_SCHEMA_REQUIRED');
    return ['paths'=>[],'preserved_replenishment'=>true];
}
function uAssertHistoricalPreserved(array $before,array $after):void {uAssert($before===$after,'CODE_ONLY_SCHEMA_CHANGED');}
function uApplyModuleSchema($app,string $appPath,callable $checkpoint):array {
    $db=$app->make('db')->connection();$before=uSchemaSnapshot($db);uModulePlan($db,$appPath,$before);
    $receipt=['state'=>'EXISTING_SCHEMA_VERIFIED','historical_schema'=>$before,'module_migrations'=>[],'module_tables'=>[],'installed_migration_count'=>0,'preserved_replenishment'=>true];
    $checkpoint($receipt);uAssertHistoricalPreserved($before,uSchemaSnapshot($db));return $receipt;
}
function uInstallModules(array $r):array {
    $p=uPaths($r);$file=__DIR__.'/desktop-pos-installation.json';uAssert(!file_exists($file)&&!is_link($file),'SCHEMA_VERIFICATION_ALREADY_STARTED');
    $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];
    $write=static function(array $state)use($binding,$file):void{uWrite($file,json_encode($binding+$state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
    $receipt=uApplyModuleSchema(app(),$p['app'],$write);uVerifyModuleReceipt($r);return $receipt;
}
function uVerifyModuleReceipt(array $r):void {
    $receipt=uJson(__DIR__.'/desktop-pos-installation.json');uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='EXISTING_SCHEMA_VERIFIED'&&($receipt['installed_migration_count']??null)===0&&($receipt['module_migrations']??null)===[],'SCHEMA_VERIFICATION_INCOMPLETE');
    uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
