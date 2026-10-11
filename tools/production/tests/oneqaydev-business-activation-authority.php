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

