<?php
// Author by Lab | zefry. Synthetic assets only.
declare(strict_types=1);
require __DIR__.'/../cpanel/menu-vite-assets.php';
$root=sys_get_temp_dir().'/oneqay-vite-'.bin2hex(random_bytes(8));mkdir($root,0700);mkdir($root.'/assets',0700);
$manifest=['resources/js/app.ts'=>['file'=>'assets/app.js','css'=>['assets/app.css'],'dynamicImports'=>['qr']],'qr'=>['file'=>'assets/qr.js']];
foreach(['app.js','app.css','qr.js'] as $name)file_put_contents($root.'/assets/'.$name,$name);
$write=static function(array $value)use($root):void{file_put_contents($root.'/manifest.json',json_encode($value,JSON_THROW_ON_ERROR));};
$write($manifest);$result=menuViteInventory($root);
if(count($result)!==3 || $result['assets/qr.js']!==hash('sha256','qr.js'))throw new RuntimeException('DYNAMIC_ASSET_NOT_VERIFIED');
unlink($root.'/assets/qr.js');try{menuViteInventory($root);throw new Exception('MISSING_ACCEPTED');}catch(RuntimeException $e){if($e->getMessage()!=='VITE_ASSET_MISSING')throw $e;}
file_put_contents($root.'/assets/qr.js','qr.js');$bad=$manifest;$bad['qr']['file']='assets/../secret';$write($bad);try{menuViteInventory($root);throw new Exception('TRAVERSAL_ACCEPTED');}catch(RuntimeException $e){if($e->getMessage()!=='VITE_ASSET_INVALID')throw $e;}
$bad=$manifest;$bad['qr']['dynamicImports']=['missing'];$write($bad);try{menuViteInventory($root);throw new Exception('IMPORT_ACCEPTED');}catch(RuntimeException $e){if($e->getMessage()!=='VITE_IMPORT_INVALID')throw $e;}
$manifest['qr']['imports']=['resources/js/app.ts'];$write($manifest);if(count(menuViteInventory($root))!==3)throw new RuntimeException('CYCLE_FAILED');
foreach(glob($root.'/assets/*') as $f)unlink($f);rmdir($root.'/assets');unlink($root.'/manifest.json');rmdir($root);
echo "MENU_VITE_ASSETS_PASS assertions=5\n";
