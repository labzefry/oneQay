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
    $schema=$db->getSchemaBuilder();$tables=$schema->getTableListing(null,false);sort($tables);$ddl=[];
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
    $added=array_values(array_diff(array_keys($after['ddl']),array_keys($before['ddl'])));$want=uModuleTables();sort($added);sort($want);
    uAssert($added===$want,'MODULE_TABLE_SET_INVALID');
    uAssert(count($after['receipts'])===count($before['receipts'])+3,'MODULE_MIGRATION_RECEIPTS_INVALID');
}
/** Exact paths only. MySQL DDL is not transactional: durable intent is written before its first statement. */
function uApplyModuleSchema($app,string $appPath,callable $checkpoint): array {
    $db=$app->make('db')->connection();$schema=$db->getSchemaBuilder();$before=uSchemaSnapshot($db);
    uAssert(count($before['receipts'])===27,'EXACT_HISTORICAL_RECEIPTS_REQUIRED');
    foreach(uModuleTables() as $table)uAssert(!$schema->hasTable($table),'MODULE_TABLE_ALREADY_PRESENT');
    $paths=uModulePaths($appPath);foreach($paths as $path)uAssert(is_file($path)&&!is_link($path),'MODULE_MIGRATION_MISSING');
    $names=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$paths);
    $checkpoint(['state'=>'ADDITIVE_SCHEMA_STARTED','historical_schema'=>$before,'module_migrations'=>$names]);
    $migrator=$app->make('migrator');$migrator->run($paths,['step'=>true]);
    $after=uSchemaSnapshot($db);uAssertHistoricalPreserved($before,$after);
    $actual=array_column(array_slice($after['receipts'],27),'migration');sort($actual);$expected=$names;sort($expected);uAssert($actual===$expected,'MODULE_MIGRATION_NAMES_INVALID');
    uAssert($db->table('oneqay_mail_settings')->where('id',1)->count()===1,'MAIL_SETTINGS_INITIALIZATION_FAILED');
    $receipt=['state'=>'ADDITIVE_SCHEMA_APPLIED','historical_schema'=>$before,'module_migrations'=>$names,'module_tables'=>uModuleTables()];
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
function uInstallModules(array $r): void {
    $p=uPaths($r);$file=__DIR__.'/module-installation.json';
    uAssert(!file_exists($file)&&!is_link($file),'MODULE_INSTALLATION_ALREADY_STARTED_NO_AUTOMATIC_RETRY');
    $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];
    $write=static function(array $state)use($binding,$file):void{uWrite($file,json_encode($binding+$state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
    $receipt=uApplyModuleSchema(app(),$p['app'],$write);
    // Uses canonical policy administration: an identity without its protected admin permission is denied.
    uGrantExistingOwnerBusinessAccess(app(),bMerchant()['merchant'],$r['source_commit']);
    $receipt['state']='ADDITIVE_MODULES_AND_OWNER_ACCESS_APPLIED';$receipt['owner_access']='CANONICAL_BUSINESS_MANAGER_OUTLET_ASSIGNMENT';$write($receipt);
    uVerifyModuleReceipt($r);
}
function uVerifyModuleReceipt(array $r): void {
    $receipt=uJson(__DIR__.'/module-installation.json');
    uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='ADDITIVE_MODULES_AND_OWNER_ACCESS_APPLIED','MODULE_INSTALLATION_INCOMPLETE');
    uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
