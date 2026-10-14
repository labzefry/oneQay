<?php
declare(strict_types=1);
// Author by Lab | zefry. Loaded only by the source/owner-authority-bound CLI operator.
function uModulePaths(string $app): array {
    return [$app.'/database/module-migrations/pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php',
        $app.'/database/module-migrations/business/2026_10_12_000001_create_integrated_business_management.php',
        $app.'/database/module-migrations/platform/2026_10_13_000001_create_platform_and_notification.php'];
}
function uModuleTables(): array {
    return ['oneqay_pos_inventory_replenishments',...array_map(static fn($n)=>'oneqay_business_'.$n,['partners','purchases','receipts','invoices','settlements','journals','interactions','employees','attendance','leave','events','operations']),
        'oneqay_platform_businesses','oneqay_platform_operations','oneqay_platform_audit','oneqay_mail_settings','oneqay_mail_messages','oneqay_mail_suppressions','oneqay_mail_events'];
}
/** Frozen historical DDL and migration receipts; no business row snapshots or private data. */
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
    $installed=count($before['receipts'])===28?2:3;
    uAssert(count($after['receipts'])===count($before['receipts'])+$installed,'MODULE_MIGRATION_RECEIPTS_INVALID');
}
/** Accept only the exact published 1–27 set, optionally plus the evidenced #28. */
function uModulePlan($db,string $appPath,array $before): array {
    $base=glob($appPath.'/database/migrations/*.php');sort($base);
    uAssert(count($base)===27,'HISTORICAL_SOURCE_SET_INVALID');
    $expected=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$base);
    $paths=uModulePaths($appPath);$pos=pathinfo($paths[0],PATHINFO_FILENAME);
    $actual=array_column($before['receipts'],'migration');sort($actual);
    $withPos=[...$expected,$pos];sort($expected);sort($withPos);
    uAssert($actual===$expected||$actual===$withPos,'EXACT_HISTORICAL_RECEIPTS_REQUIRED');
    $preserved=$actual===$withPos;
    if($preserved){uExistingReplenishmentContract($db);array_shift($paths);}
    foreach(uModuleTables() as $table){
        if($preserved&&$table==='oneqay_pos_inventory_replenishments')continue;
        uAssert(!$db->getSchemaBuilder()->hasTable($table),'MODULE_TABLE_ALREADY_PRESENT');
    }
    foreach($paths as $path)uAssert(is_file($path)&&!is_link($path),'MODULE_MIGRATION_MISSING');
    return ['paths'=>$paths,'preserved_replenishment'=>$preserved];
}
/** Verify tenant keys, precision, uniqueness and references before retaining #28. */
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
/** Exact paths only. MySQL DDL is not transactional: durable intent is written before its first statement. */
function uApplyModuleSchema($app,string $appPath,callable $checkpoint): array {
    $db=$app->make('db')->connection();$schema=$db->getSchemaBuilder();$before=uSchemaSnapshot($db);
    $plan=uModulePlan($db,$appPath,$before);$paths=$plan['paths'];
    $names=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$paths);
    $checkpoint(['state'=>'ADDITIVE_SCHEMA_STARTED','historical_schema'=>$before,'module_migrations'=>$names]);
    $migrator=$app->make('migrator');$migrator->run($paths,['step'=>true]);
    $after=uSchemaSnapshot($db);uAssertHistoricalPreserved($before,$after);
    $actual=array_column(array_slice($after['receipts'],count($before['receipts'])),'migration');sort($actual);$expected=$names;sort($expected);uAssert($actual===$expected,'MODULE_MIGRATION_NAMES_INVALID');
    uAssert($db->table('oneqay_mail_settings')->where('id',1)->count()===1,'MAIL_SETTINGS_INITIALIZATION_FAILED');
    $receipt=['state'=>'ADDITIVE_SCHEMA_APPLIED','historical_schema'=>$before,'module_migrations'=>$names,'module_tables'=>uModuleTables(),
        'installed_migration_count'=>count($names),'preserved_replenishment'=>$plan['preserved_replenishment']];
    $checkpoint($receipt);return $receipt;
}
function uGrantExistingOwnerBusinessAccess($app,array $merchant,string $source): array {
    $actor=new \App\Application\Organization\VerifiedOrganizationalContext(
        \App\Domain\Identity\PlatformIdentityId::fromString($merchant['identity_id']),\App\Domain\Tenancy\TenantId::fromString($merchant['tenant_id']),
        \App\Domain\Organization\OrganizationId::fromString($merchant['organization_id']),\App\Domain\Outlet\OutletId::fromString($merchant['outlet_id']),\App\Domain\Device\DeviceId::fromString($merchant['device_id']));
    $contexts=$app->make(\App\Application\Organization\OrganizationalContextStore::class);$contexts->setVerified($actor);
    try { return $app->make(\App\Application\Business\BusinessRoleAdministration::class)->apply([
        'operation_id'=>'cpanel-business-modules-'.$source,'identity_id'=>$merchant['identity_id'],'role'=>'oneqay-business-manager','action'=>'assign']);
    } finally {$contexts->clear();}
}
function uInstallModules(array $r): array {
    $p=uPaths($r);$file=__DIR__.'/module-installation.json';
    uAssert(!file_exists($file)&&!is_link($file),'MODULE_INSTALLATION_ALREADY_STARTED_NO_AUTOMATIC_RETRY');
    $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];
    $write=static function(array $state)use($binding,$file):void{uWrite($file,json_encode($binding+$state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
    $receipt=uApplyModuleSchema(app(),$p['app'],$write);
    // Uses canonical policy administration: an identity without its protected admin permission is denied.
    uGrantExistingOwnerBusinessAccess(app(),bMerchant()['merchant'],$r['source_commit']);
    $receipt['state']='ADDITIVE_MODULES_AND_OWNER_ACCESS_APPLIED';$receipt['owner_access']='CANONICAL_BUSINESS_MANAGER_OUTLET_ASSIGNMENT';$write($receipt);
    uVerifyModuleReceipt($r);return $receipt;
}
function uVerifyModuleReceipt(array $r): void {
    $receipt=uJson(__DIR__.'/module-installation.json');
    uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='ADDITIVE_MODULES_AND_OWNER_ACCESS_APPLIED','MODULE_INSTALLATION_INCOMPLETE');
    uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
