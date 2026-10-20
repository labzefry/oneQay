<?php
declare(strict_types=1);
// Author by Lab | zefry. Validate real certified bytes without loading or touching hosting.
$folder=$argv[1]??'';
if(!is_string($folder)||realpath($folder)!==$folder||!is_dir($folder))throw new RuntimeException('PACKAGE_DIRECTORY_INVALID');
require $folder.'/update.php';
require $folder.'/install-modules.php';
$request=uRequest();
uCheckPackage($request);
uAssert(hash_equals(uApprovalBody(hash_file('sha256',$folder.'/authority-request.json'),$request),uRead($folder.'/authorization-body.txt')),'PACKAGE_AUTHORITY_BODY_MISMATCH');
uAssert(count(uModulePaths($folder.'/payload/'.$request['release_id'].'/apps/web'))===0&&count(uModuleTables())===0,'PACKAGE_MODULE_SCOPE_MISMATCH');
echo 'POS_ROUTING_CERTIFIED_PACKAGE_PASS files='.$request['release_file_count'].PHP_EOL;
