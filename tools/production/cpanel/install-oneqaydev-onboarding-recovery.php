<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact 35/84 predecessor; only three new tables and cashier employee links.
function uModulePaths(string $app):array {return [$app.'/database/module-migrations/business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php'];}
function uModuleTables():array {return ['oneqay_business_job_titles','oneqay_business_staff_accounts','oneqay_pos_shift_recoveries'];}
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
    foreach(uModuleTables() as $table)uAssert(!isset($before['ddl'][$table]),'RECOVERY_MODULE_ALREADY_PRESENT');
    $path=uModulePaths($appPath)[0];uAssert(is_file($path)&&!is_link($path),'RECOVERY_MIGRATION_MISSING');
    return ['paths'=>[$path],'preserved_replenishment'=>true];
}

function uAssertHistoricalPreserved(array $before,array $after):void {
 foreach($before['ddl']as$table=>$hash)uAssert(($after['ddl'][$table]??null)===$hash,'HISTORICAL_DATABASE_DDL_CHANGED');
 uAssert(array_slice($after['receipts'],0,count($before['receipts']))===$before['receipts'],'HISTORICAL_MIGRATION_RECEIPTS_CHANGED');
 $added=array_values(array_diff(array_keys($after['ddl']),array_keys($before['ddl'])));$want=uModuleTables();sort($added);sort($want);uAssert($added===$want,'RECOVERY_MODULE_TABLE_SET_INVALID');
 uAssert(count($after['receipts'])===36&&count($after['ddl'])===87,'RECOVERY_SCHEMA_TOTAL_INVALID');
 $names=array_column(array_slice($after['receipts'],35),'migration');uAssert($names===['2026_10_20_000001_create_workforce_accounts_and_shift_recovery'],'RECOVERY_RECEIPT_INVALID');
}
function uApplyModuleSchema($app,string $appPath,callable $checkpoint):array {
 $db=$app->make('db')->connection();$before=uSchemaSnapshot($db);$plan=uModulePlan($db,$appPath,$before);$names=array_map(static fn($p)=>pathinfo($p,PATHINFO_FILENAME),$plan['paths']);
 $checkpoint(['state'=>'ADDITIVE_SCHEMA_STARTED','historical_schema'=>$before,'module_migrations'=>$names]);
 $app->make('migrator')->run($plan['paths'],['step'=>true]);uAssertHistoricalPreserved($before,uSchemaSnapshot($db));
 $receipt=['state'=>'ADDITIVE_SCHEMA_APPLIED','historical_schema'=>$before,'module_migrations'=>$names,'module_tables'=>uModuleTables(),'installed_migration_count'=>1,'preserved_replenishment'=>true];$checkpoint($receipt);return $receipt;
}
function uInstallModules(array $r):array {
 $p=uPaths($r);$file=__DIR__.'/desktop-pos-installation.json';uAssert(!file_exists($file)&&!is_link($file),'SCHEMA_INSTALLATION_ALREADY_STARTED');
 $binding=['source_commit'=>$r['source_commit'],'request_sha256'=>hash('sha256',uRead(__DIR__.'/authority-request.json'))];
 $write=static function(array $state)use($binding,$file):void{uWrite($file,json_encode($binding+$state,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");};
 $receipt=uApplyModuleSchema(app(),$p['app'],$write);uVerifyModuleReceipt($r);return $receipt;
}
function uVerifyModuleReceipt(array $r):void {
 $receipt=uJson(__DIR__.'/desktop-pos-installation.json');uAssert(($receipt['source_commit']??null)===$r['source_commit']&&($receipt['request_sha256']??null)===hash('sha256',uRead(__DIR__.'/authority-request.json'))&&($receipt['state']??null)==='ADDITIVE_SCHEMA_APPLIED'&&($receipt['installed_migration_count']??null)===1&&($receipt['module_migrations']??null)===['2026_10_20_000001_create_workforce_accounts_and_shift_recovery'],'SCHEMA_INSTALLATION_INCOMPLETE');
 uAssertHistoricalPreserved($receipt['historical_schema'],uSchemaSnapshot(app('db')->connection()));
}
/** Publish owner-requested copy while preserving old revisions, custom drafts and uploaded images. */
function uPublishWebsiteContent($db,string $appPath,string $source):array {
 $defaults=json_decode(uRead($appPath.'/resources/website/defaults.json'),true,64,JSON_THROW_ON_ERROR);$slugs=['home','pricing','guides','terms','privacy'];
 return $db->transaction(function()use($db,$defaults,$slugs,$source):array{$out=[];
  foreach($slugs as$slug){$p=$db->table('oneqay_site_pages')->where('slug',$slug)->lockForUpdate()->first();
   if(!$p||$p->published_revision===null){$out[$slug]=['mode'=>'CERTIFIED_DEFAULT_ON_SOURCE_SWITCH'];continue;}
   $previous=$db->table('oneqay_site_revisions')->where('id',$p->published_revision)->where('slug',$slug)->first();uAssert($previous!==null,'PUBLISHED_CONTENT_REVISION_MISSING');$old=json_decode($previous->document,true,64,JSON_THROW_ON_ERROR);$next=$defaults[$slug];
   foreach(['id','en']as$locale){$used=[];
    foreach($old[$locale]['blocks']as$block){$image=$block['image']??'';if($image===''||isset($used[$image]))continue;uAssert(is_string($image)&&preg_match('/\A[a-f0-9]{32}\z/D',$image)===1&&$db->table('oneqay_site_media')->where('id',$image)->exists(),'PUBLISHED_IMAGE_REFERENCE_INVALID');$used[$image]=true;$found=false;
     foreach($next[$locale]['blocks']as&$candidate)if($candidate['id']===$block['id']){$candidate['image']=$image;$candidate['alt']=$block['alt']??'';$found=true;break;}unset($candidate);
     if(!$found)$next[$locale]['blocks'][]=['id'=>'retained-image-'.substr($image,0,12),'type'=>'text','tone'=>'light','eyebrow'=>'','title'=>'','body'=>'','image'=>$image,'alt'=>$block['alt']??'','button'=>'','href'=>'/register','items'=>[]];
    }
   }
   $json=json_encode($next,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$id=bin2hex(random_bytes(16));$now=time();$draft=json_decode($p->draft,true,64,JSON_THROW_ON_ERROR);$dirty=$draft!==$old;
   $db->table('oneqay_site_revisions')->insert(['id'=>$id,'slug'=>$slug,'document'=>$json,'actor'=>'deployment:'.$source,'created_at'=>$now]);uAssert($db->table('oneqay_site_pages')->where('slug',$slug)->where('version',$p->version)->update(['published_revision'=>$id,'version'=>$p->version+1,'draft'=>$dirty?$p->draft:$json,'updated_at'=>$now])===1,'CONTENT_PAGE_VERSION_CHANGED');
   $out[$slug]=['mode'=>'IMMUTABLE_REVISION_PUBLISHED','previous_revision'=>$p->published_revision,'revision'=>$id,'custom_draft_preserved'=>$dirty,'document_sha256'=>hash('sha256',$json)];
  }return $out;
 });
}
