<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact thirty-one-receipt predecessor; four website tables, no account or role mutation.
function uModulePaths(string $app):array {return [$app.'/database/module-migrations/platform/2026_10_15_000001_create_public_website.php'];}
function uModuleTables():array {return ['oneqay_site_pages','oneqay_site_revisions','oneqay_site_media','oneqay_site_registrations'];}
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
function uAssertHistoricalPreserved(array $before,array $after): void {
    foreach($before['ddl'] as $name=>$hash)uAssert(($after['ddl'][$name]??null)===$hash,'HISTORICAL_DATABASE_DDL_CHANGED');
    uAssert(array_slice($after['receipts'],0,count($before['receipts']))===$before['receipts'],'HISTORICAL_MIGRATION_RECEIPTS_CHANGED');
    $added=array_values(array_diff(array_keys($after['ddl']),array_keys($before['ddl'])));$want=array_values(array_diff(uModuleTables(),array_keys($before['ddl'])));sort($added);sort($want);
    uAssert($added===$want,'MODULE_TABLE_SET_INVALID');
    $installed=1;
    uAssert(count($after['receipts'])===count($before['receipts'])+$installed,'MODULE_MIGRATION_RECEIPTS_INVALID');
}
/** Accept only the exact published 1–27 set, optionally plus the evidenced #28. */
function uExistingReplenishmentContract($db): void {
    $schema=$db->getSchemaBuilder();$table='oneqay_pos_inventory_replenishments';
    uAssert($schema->hasTable($table),'REPLENISHMENT_RECEIPT_WITHOUT_TABLE');
    $strings=['tenant_id'=>['varchar',64],'replenishment_id'=>['char',32],'operation_id'=>['varchar',128],
        'payload_fingerprint'=>['char',64],'actor_identity_id'=>['varchar',96],'organization_id'=>['varchar',64],
        'outlet_id'=>['varchar',64],'device_id'=>['varchar',64],'product_id'=>['varchar',64],'correlation_id'=>['varchar',128]];
    $integers=['before_available_quantity','replenished_quantity','after_available_quantity','occurred_at_unix'];
    $columns=$schema->getColumns($table);uAssert(count($columns)===14,'REPLENISHMENT_COLUMNS_INVALID');
    foreach($columns as $column){$name=$column['name'];$type=strtolower($column['type']);
        uAssert(($column['nullable']??true)===false&&($column['default']??null)===null&&!($column['auto_increment']??false)&&($column['generation']??null)===null,'REPLENISHMENT_COLUMN_POLICY_INVALID');
        if(isset($strings[$name])){[$kind,$length]=$strings[$name];uAssert($type===($db->getDriverName()==='mysql'?$kind.'('.$length.')':'varchar'),'REPLENISHMENT_TEXT_TYPE_INVALID');}
        else {uAssert(in_array($name,$integers,true)&&($db->getDriverName()==='mysql'?preg_match('/\Abigint(?:\(20\))? unsigned\z/D',$type)===1:$type==='integer'),'REPLENISHMENT_QUANTITY_TYPE_INVALID');}
    }
    $indexes=$schema->getIndexes($table);$seen=[];
    foreach($indexes as $index){if($index['primary']){$key='primary';$want=['tenant_id','replenishment_id'];uAssert($index['unique'],'REPLENISHMENT_PRIMARY_INVALID');}
        elseif($index['name']==='uq_pos_inventory_replenishments_operation'){$key='operation';$want=['tenant_id','operation_id'];uAssert($index['unique'],'REPLENISHMENT_UNIQUE_INVALID');}
        elseif($index['name']==='ix_pos_inventory_replenishments_product_time'){$key='product';$want=['tenant_id','outlet_id','product_id','occurred_at_unix'];}
        else {continue;}uAssert($index['columns']===$want&&!isset($seen[$key]),'REPLENISHMENT_INDEX_INVALID');$seen[$key]=true;}
    uAssert(count($seen)===3,'REPLENISHMENT_INDEX_MISSING');
    $want=[['tenant_id','actor_identity_id','oneqay_identities','tenant_id','id'],
        ['tenant_id','organization_id','oneqay_organizations','tenant_id','id'],
        ['tenant_id','outlet_id','oneqay_outlets','tenant_id','id'],
        ['tenant_id','device_id','oneqay_devices','tenant_id','id'],
        ['tenant_id','outlet_id','product_id','oneqay_pos_sale_catalog_items','tenant_id','outlet_id','product_id']];
    $actual=[];foreach($schema->getForeignKeys($table) as $key){
        if($db->getDriverName()==='mysql')uAssert(($key['foreign_schema']??null)===$db->getDatabaseName(),'REPLENISHMENT_CROSS_SCHEMA_REFERENCE_DENIED');
        uAssert(in_array(strtolower($key['on_update']),['restrict','no action'],true)&&in_array(strtolower($key['on_delete']),['restrict','no action'],true),'REPLENISHMENT_REFERENCE_POLICY_INVALID');
        $actual[]=[...$key['columns'],$key['foreign_table'],...$key['foreign_columns']];}
    sort($want);sort($actual);uAssert($actual===$want,'REPLENISHMENT_REFERENCES_INVALID');
}
function uModulePlan($db,string $appPath,array $before):array {
    $files=glob($appPath.'/database/migrations/*.php');sort($files);uAssert(count($files)===27,'HISTORICAL_SCHEMA_COUNT_CHANGED');
    $expected=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$files);
    foreach([$appPath.'/database/module-migrations/pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php',$appPath.'/database/module-migrations/business/2026_10_12_000001_create_integrated_business_management.php',$appPath.'/database/module-migrations/platform/2026_10_13_000001_create_platform_and_notification.php',$appPath.'/database/module-migrations/business/2026_10_14_000001_create_merchandising_and_currency_preferences.php']as$file){uAssert(is_file($file)&&!is_link($file),'PREDECESSOR_MODULE_FILE_MISSING');$expected[]=pathinfo($file,PATHINFO_FILENAME);}
    uAssert(array_column($before['receipts'],'migration')===$expected,'EXACT_THIRTY_ONE_RECEIPTS_REQUIRED');uExistingReplenishmentContract($db);
    $schema=$db->getSchemaBuilder();foreach([...array_map(static fn($n)=>'oneqay_business_'.$n,['partners','purchases','receipts','invoices','settlements','journals','interactions','employees','attendance','leave','events','operations']),'oneqay_business_settings','oneqay_business_product_profiles','oneqay_platform_businesses','oneqay_platform_operations','oneqay_platform_audit','oneqay_mail_settings','oneqay_mail_messages','oneqay_mail_suppressions','oneqay_mail_events']as$table)uAssert($schema->hasTable($table),'PREDECESSOR_MODULE_TABLE_MISSING');
    foreach(uModuleTables()as$table)uAssert(!$schema->hasTable($table),'PUBLIC_WEBSITE_ALREADY_PRESENT_NO_AUTOMATIC_RETRY');
    return ['paths'=>uModulePaths($appPath),'preserved_replenishment'=>true];
}
function uApplyModuleSchema($app,string $appPath,callable $checkpoint):array {
    $db=$app->make('db')->connection();$before=uSchemaSnapshot($db);$plan=uModulePlan($db,$appPath,$before);$names=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$plan['paths']);
    $checkpoint(['state'=>'ADDITIVE_SCHEMA_STARTED','historical_schema'=>$before,'module_migrations'=>$names]);
    $app->make('migrator')->run($plan['paths'],['step'=>true]);$after=uSchemaSnapshot($db);uAssertHistoricalPreserved($before,$after);
    uAssert(array_column(array_slice($after['receipts'],31),'migration')===$names&&count($after['receipts'])===32,'PUBLIC_WEBSITE_RECEIPT_INVALID');
    $added=array_keys(array_diff_key($after['ddl'],$before['ddl']));sort($added);$expected=uModuleTables();sort($expected);uAssert($added===$expected,'PUBLIC_WEBSITE_TABLE_SET_INVALID');
    $receipt=['state'=>'ADDITIVE_SCHEMA_APPLIED','historical_schema'=>$before,'module_migrations'=>$names,'module_tables'=>uModuleTables(),'installed_migration_count'=>1,'preserved_replenishment'=>true];$checkpoint($receipt);return $receipt;
}
function uInstallModules(array $r): array {
    $p=uPaths($r);$file=__DIR__.'/website-installation.json';
    uAssert(!file_exists($file)&&!is_link($file),'MODULE_INSTALLATION_ALREADY_STARTED_NO_AUTOMATIC_RETRY');
    $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];
    $write=static function(array $state)use($binding,$file):void{uWrite($file,json_encode($binding+$state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
    $receipt=uApplyModuleSchema(app(),$p['app'],$write);
    $receipt['state']='ADDITIVE_PUBLIC_WEBSITE_APPLIED';$receipt['owner_access']='PRESERVED_UNCHANGED';$write($receipt);
    uVerifyModuleReceipt($r);return $receipt;
}
function uVerifyModuleReceipt(array $r): void {
    $receipt=uJson(__DIR__.'/website-installation.json');
    uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='ADDITIVE_PUBLIC_WEBSITE_APPLIED','MODULE_INSTALLATION_INCOMPLETE');
    uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
