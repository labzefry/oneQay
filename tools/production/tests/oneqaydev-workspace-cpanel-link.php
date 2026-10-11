<?php
declare(strict_types=1);
// Author by Lab | zefry. Exercise real PHP capability removal, not a mocked function.
require __DIR__.'/../cpanel/update-oneqaydev-certified-workspace.php';
$dir=sys_get_temp_dir().'/oneqay-private-binding-'.bin2hex(random_bytes(8));mkdir($dir,0700);$env=$dir.'/runtime.env';$target=$dir.'/.env';file_put_contents($env,"FIXTURE_ONLY=true\n");chmod($env,0600);
try {
    if(!function_exists('link')&&!function_exists('symlink')){
        try{uBindExistingRuntime($env,$target);throw new RuntimeException('Expected capability refusal');}catch(WorkspaceUpdateStop $e){if($e->getMessage()!=='PRIVATE_LINK_CAPABILITY_UNAVAILABLE'||file_exists($target))throw $e;}
        echo "PRIVATE_BINDING_NO_CAPABILITY_REFUSED=PASS\n";
    }else{
        uBindExistingRuntime($env,$target);
        if(hash_file('sha256',$env)!==hash_file('sha256',$target)||((int)fileperms($target)&0077)!==0)throw new RuntimeException('Binding mismatch');
        if(function_exists('link')&&(is_link($target)||fileinode($env)!==fileinode($target)))throw new RuntimeException('Hard link must be preferred');
        try{uBindExistingRuntime($env,$target);throw new RuntimeException('Existing target overwritten');}catch(WorkspaceUpdateStop $e){if($e->getMessage()!=='RUNTIME_TARGET_ALREADY_EXISTS')throw $e;}
        echo "PRIVATE_BINDING_CAPABILITY_AND_PRESERVATION=PASS\n";
    }
    $recovery=file_get_contents(__DIR__.'/../cpanel/recover-oneqaydev-certified-workspace.php');$normal=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-certified-workspace.php');
    $get=static function(string $s):string{$a=strpos($s,'function uBindExistingRuntime(');return substr($s,$a,strpos($s,'function uRuntime(',$a)-$a);};
    if($get($recovery)!==$get($normal))throw new RuntimeException('Recovery binding differs');
    echo "RECOVERY_USES_TESTED_BINDING=PASS\n";
} finally {if(file_exists($target)||is_link($target))unlink($target);unlink($env);rmdir($dir);}
