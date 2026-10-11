<?php
declare(strict_types=1);
// Author by Lab | zefry. Independent approval and private-output regression.
require __DIR__.'/../cpanel/activate-oneqaydev-certified-business.php';
function expectDenied(callable $f,string $label):void {
    try {$f();throw new RuntimeException('Unexpected authority acceptance: '.$label);}
    catch(BusinessActivationStop) {}
}
$now=1791709200;
$sha=str_repeat('a',64);
$comment=['id'=>123,'user'=>['login'=>'labzefry','type'=>'User'],'author_association'=>'OWNER',
    'issue_url'=>'https://api.github.com/repos/labzefry/oneQay/issues/942',
    'body'=>bApprovalBody($sha),'created_at'=>gmdate('Y-m-d\TH:i:s\Z',$now),
    'updated_at'=>gmdate('Y-m-d\TH:i:s\Z',$now)];
$grant=bQualifyApproval($comment,123,$sha,$now+899);
if($grant['expires_at_unix']!==$now+900)throw new RuntimeException('Window incorrectly bound');
$mutations=[
    'wrong owner'=>static function(array $c):array{$c['user']['login']='someone-else';return $c;},
    'bot issuer'=>static function(array $c):array{$c['user']['type']='Bot';return $c;},
    'not owner'=>static function(array $c):array{$c['author_association']='MEMBER';return $c;},
    'different repository'=>static function(array $c):array{$c['issue_url']='https://api.github.com/repos/labzefry/other/issues/941';return $c;},
    'different issue'=>static function(array $c):array{$c['issue_url']='https://api.github.com/repos/labzefry/oneQay/issues/940';return $c;},
    'engineering authority'=>static function(array $c):array{$c['body']='PRODUCT OWNER MERGE AUTHORIZATION';return $c;},
    'different source'=>static function(array $c):array{$c['body']=str_replace(B_SOURCE,str_repeat('b',40),$c['body']);return $c;},
    'different target'=>static function(array $c):array{$c['body']=str_replace('oneqaydev.n07.my.id','oneqay.n07.my.id',$c['body']);return $c;},
    'different archive'=>static function(array $c):array{$c['body']=str_replace(B_ARCHIVE,str_repeat('c',64),$c['body']);return $c;},
    'revoked permission'=>static function(array $c):array{$c['body']=str_replace('BUSINESS_TRAFFIC: GRANTED','BUSINESS_TRAFFIC: REVOKED',$c['body']);return $c;},
    'edited approval'=>static function(array $c):array{$c['updated_at']=gmdate('Y-m-d\TH:i:s\Z',1791709201);return $c;},
];
foreach($mutations as $label=>$mutate)expectDenied(static fn()=>bQualifyApproval($mutate($comment),123,$sha,$now),$label);
expectDenied(static fn()=>bQualifyApproval($comment,124,$sha,$now),'wrong comment id');
expectDenied(static fn()=>bQualifyApproval($comment,123,str_repeat('d',64),$now),'request substitution');
expectDenied(static fn()=>bQualifyApproval($comment,123,$sha,$now-1),'future authority');
expectDenied(static fn()=>bQualifyApproval($comment,123,$sha,$now+901),'expired authority');
$dir=sys_get_temp_dir().'/oneqay-business-output-'.bin2hex(random_bytes(8));
mkdir($dir,0700);$file=$dir.'/private.json';
bWrite($file,'private fixture');clearstatcache();
if((fileperms($file)&0777)!==0600 || file_get_contents($file)!=='private fixture')throw new RuntimeException('Private output not preserved');
$link=$dir.'/link';symlink($file,$link);
expectDenied(static fn()=>bWrite($link,'unexpected'),'symlink output');
if(file_get_contents($file)!=='private fixture')throw new RuntimeException('Symlink output changed target');
unlink($link);unlink($file);rmdir($dir);
echo "PASS: exact independent owner grant, 15 negative authority cases, 900-second window and private atomic output.\n";

// The original health index must remain intact; root and routed requests use the fresh filename.
$native="RewriteEngine On\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^ index.php [L]\n";
$handler="# php -- BEGIN cPanel-generated handler, do not edit\n<IfModule mime_module>\nAddHandler application/x-httpd-ea-php83 .php .php8 .phtml\n</IfModule>\n# php -- END cPanel-generated handler, do not edit\n";
$rewrite=bRewriteFor($native,"DirectoryIndex index.html index.php\n".$handler);
if(!str_contains($rewrite,'RewriteRule ^$ '.B_ENTRY_NAME.' [L]')
    || !str_contains($rewrite,'RewriteRule ^ '.B_ENTRY_NAME.' [L]')
    || !str_contains($rewrite,'DirectoryIndex '.B_ENTRY_NAME)
    || !str_contains($rewrite,$handler))throw new RuntimeException('Fresh routing or qualified PHP handler not preserved');
$bridge=bBridgeFor(str_repeat('a',64));
if(!str_contains($bridge,var_export(B_ENV,true)) || !str_contains($bridge,'clearstatcache(true,$binding)')
    || !str_contains($bridge,'hash_file(\'sha256\',$binding)')
    || !str_contains($bridge,"ProductionBusinessRuntimeGate::allows('production')"))throw new RuntimeException('Cached bridge lacks bound-file/native gate');
$r=['status'=>503,'body'=>'oneQayDev business activation not authorized','headers'=>''];
$e=bEntryEvidence($r);
if($e['bridge_identity']!=='MISSING' || !$e['known_health_only_response'])throw new RuntimeException('Old health bytecode was accepted');
$r=['status'=>200,'body'=>'<meta name="oneqay-merchant-entry" content="enabled"><script id="oneqay-merchant-login-context">PRIVATE-MERCHANT-CONTEXT</script>',
    'headers'=>"X-OneQay-Business-Bridge: ".B_BRIDGE_ID."\r\nX-OneQay-Entry-Phase: native-gate-passed\r\nSet-Cookie: oneqaydev-production-session=PRIVATE-COOKIE; path=/; secure; httponly; samesite=lax\r\n"];
$e=bEntryEvidence($r);
if($e['bridge_identity']!=='EXPECTED' || !$e['secure_session_cookie'] || !$e['merchant_entry_enabled']
    || str_contains(json_encode($e,JSON_THROW_ON_ERROR),'PRIVATE-'))throw new RuntimeException('Evidence policy or confidentiality failed');
$r['headers']="X-OneQay-Business-Bridge: ".B_BRIDGE_ID."\r\nX-OneQay-Entry-Phase: capability-denied\r\nX-OneQay-Missing-Capabilities: mbstring,PRIVATE-SECRET,pdo_mysql\r\n";
$e=bEntryEvidence($r);
if($e['missing_php_capabilities']!==['mbstring','pdo_mysql'] || str_contains(json_encode($e,JSON_THROW_ON_ERROR),'PRIVATE-'))throw new RuntimeException('Web capability diagnosis exposed untrusted values');
echo "PASS: fresh root/routed entry, preserved cPanel PHP handler, cached-bytecode binding guard, stale-bridge rejection and confidential HTTP capability evidence.\n";

