<?php
declare(strict_types=1);
// Author by Lab | zefry. Synthetic transports exercise the actual retry and guarded restart implementation.
$tmp=sys_get_temp_dir().'/oneqay-https-retry-'.bin2hex(random_bytes(8));mkdir($tmp,0700);$op=$tmp.'/operator';mkdir($op);
$src=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-control-plane.php');
$src=preg_replace("~const U_ROOT = '[^']+';~",'const U_ROOT = '.var_export($tmp.'/private',true).';',$src,1);
$src=preg_replace("~const U_PUBLIC = '[^']+';~",'const U_PUBLIC = '.var_export($tmp.'/public',true).';',$src,1);
$src=str_replace('function uAuthority(', 'function fixtureUnusedAuthority(', $src);file_put_contents($op.'/update.php',$src);require $op.'/update.php';
$n=0;$grantValid=true;$grantCalls=0;
function uAuthority(string $locator,array $r,?int $at=null):array {global $grantValid,$grantCalls;$grantCalls++;uAssert($grantValid,'EXACT_WORKSPACE_GRANT_MISSING');return [];}
function ht(bool $v,string $why):void {global $n;if(!$v)throw new RuntimeException($why);$n++;}
function htDeny(callable $f,string $code):void {try{$f();throw new RuntimeException('Expected '.$code);}catch(WorkspaceUpdateStop $e){ht($e->getMessage()===$code,$code);}}
function htResponse(int $errno=0,int $status=200):array {return ['status'=>$status,'body'=>'safe-body','headers'=>'safe-header','transfer_ok'=>$errno===0,'curl_errno'=>$errno,'curl_error'=>'NOT_FOR_REPORT private-token cookie=secret','limit_error'=>null,'elapsed_ms'=>1];}
try {
 $events=[];$calls=[];$record=static function($e)use(&$events){$events[]=$e;};
 $url='https://oneqaydev.n07.my.id/owner-login?oneqay_ui_probe=secret-probe';
 $good=static function($u,$a,$i)use(&$calls){$calls[]=$i;return htResponse();};
 $v=uHttpsWithRetries($url,'text/html',$good,$record);ht($v['status']===200&&$calls===[1]&&$events===[],'HEALTHY_SINGLE_ATTEMPT');
 foreach([5,6,7,16,18,28,35,52,55,56,81,92]as$errno){$calls=[];$events=[];
  $v=uHttpsWithRetries($url,'text/html',static function($u,$a,$i)use(&$calls,$errno){$calls[]=$i;return htResponse($i===1?$errno:0);},$record);
  ht($v['status']===200&&$calls===[1,2]&&count($events)===2&&$events[1]['outcome']==='RECOVERED','TRANSIENT_RECOVERED_'.$errno);
 }
 ht(!str_contains(json_encode($events),'secret-probe')&&!str_contains(json_encode($events),'private-token')&&!str_contains(json_encode($events),'safe-header')&&!str_contains(json_encode($events),'safe-body'),'NO_QUERY_BODY_HEADER_ERROR_SECRET_LOG');
 foreach([401,403,404,500]as$status){$calls=[];uHttpsWithRetries($url,'application/json',static function($u,$a,$i)use(&$calls,$status){$calls[]=$i;return htResponse(0,$status);},$record);ht($calls===[1],'NO_RETRY_APPLICATION_STATUS_'.$status);}
 foreach([429,502,503,504]as$status){$calls=[];$v=uHttpsWithRetries($url,'text/html',static function($u,$a,$i)use(&$calls,$status){$calls[]=$i;return htResponse(0,$i===1?$status:200);},$record);ht($calls===[1,2]&&$v['status']===200,'TRANSIENT_HTTP_RECOVERED_'.$status);}
 $calls=[];$transfer=static function($u,$a,$i)use(&$calls){$calls[]=$i;return htResponse(28);};htDeny(fn()=>uHttpsWithRetries($url,'text/html',$transfer,$record),'HTTPS_TRANSPORT_FAILED_CURL_28');ht($calls===[1,2],'EXHAUSTED_BOUND_TWO');
 foreach([60,77]as$errno){$calls=[];$transfer=static function($u,$a,$i)use(&$calls,$errno){$calls[]=$i;return htResponse($errno);};htDeny(fn()=>uHttpsWithRetries($url,'text/html',$transfer,$record),'HTTPS_TRANSPORT_FAILED_CURL_'.$errno);ht($calls===[1],'CERTIFICATE_FAILURE_NOT_BYPASSED_'.$errno);}
 foreach(['BODY_LIMIT','HEADER_LIMIT']as$limit){$calls=[];$transfer=static function($u,$a,$i)use(&$calls,$limit){$calls[]=$i;$r=htResponse(23);$r['limit_error']=$limit;return $r;};htDeny(fn()=>uHttpsWithRetries($url,'text/html',$transfer,$record),'HTTPS_RESPONSE_BOUNDS_INVALID');ht($calls===[1],'NO_RETRY_RESPONSE_BOUND_'.$limit);}
 foreach(['https://example.com/','http://oneqaydev.n07.my.id/','https://oneqaydev.n07.my.id.evil/','https://oneqaydev.n07.my.id@evil/','https://api.github.com/other','https://oneqaydev.n07.my.id/owner-login#fragment']as$bad)htDeny(fn()=>uHttpsWithRetries($bad,'text/html',$good,$record),'HTTPS_TARGET_NOT_ALLOWED');
 htDeny(fn()=>uHttpsWithRetries($url,"text/html\r\nAuthorization: secret",$good,$record),'HTTPS_ACCEPT_NOT_ALLOWED');
 $v=uHttpsWithRetries($url,'text/html',static fn()=>htResponse(),static function(){throw new RuntimeException('disk unavailable');});ht($v['status']===200,'SUCCESS_DOES_NOT_DEPEND_ON_DIAGNOSTIC_DISK');
 foreach([1,2]as$i){$o=uHttpsCurlOptions('text/html',$i);ht($o[CURLOPT_SSL_VERIFYPEER]===true&&$o[CURLOPT_SSL_VERIFYHOST]===2&&$o[CURLOPT_FOLLOWLOCATION]===false&&$o[CURLOPT_PROTOCOLS]===CURLPROTO_HTTPS,'STRICT_TLS_NO_REDIRECT_'.$i);}
 $a=uHttpsCurlOptions('text/html',1);$b=uHttpsCurlOptions('text/html',2);ht($a[CURLOPT_TIMEOUT]===25&&$b[CURLOPT_TIMEOUT]===45&&$b[CURLOPT_IPRESOLVE]===CURL_IPRESOLVE_V4&&$b[CURLOPT_HTTP_VERSION]===CURL_HTTP_VERSION_1_1,'BOUNDED_IPV4_HTTP11_FALLBACK');
 // Exercise the real stopped-preflight boundary without a database or production files.
 $r=['source_commit'=>U_SOURCE,'release_id'=>'production-'.substr(U_SOURCE,0,12)];$p=uPaths($r);
 mkdir(dirname($p['release']),0700,true);mkdir(dirname($p['env']),0700,true);mkdir(U_PUBLIC,0700,true);
 $failed=['state'=>'STOPPED_FAIL_CLOSED','error_code'=>'HTTPS_RESPONSE_INVALID','public_switch'=>'NOT_PERFORMED','host_business_acceptance'=>'NOT_VERIFIED','attribution'=>'Lab | zefry'];
 $write=static function($r){uWrite(U_REPORT,json_encode($r));};$write($failed);
 foreach([$p['release'],$p['env'],$p['entry'],U_BACKUP,$op.'/control-plane-installation.json',$op.'/control-worker.php']as$path){file_put_contents($path,'checkpoint');htDeny(fn()=>uRetryStoppedPreflight('123',$r),'RETRY_MUTATION_PRESENT_NO_AUTOMATIC_RECOVERY');unlink($path);ht(is_file(U_REPORT),'MUTATION_DENIAL_PRESERVES_REPORT');}
 $bad=$failed;$bad['public_switch']='PERFORMED';$write($bad);htDeny(fn()=>uRetryStoppedPreflight('123',$r),'RETRY_REPORT_NOT_PREFLIGHT_ONLY');
 $bad=$failed;$bad['extra']=true;$write($bad);htDeny(fn()=>uRetryStoppedPreflight('123',$r),'RETRY_REPORT_NOT_PREFLIGHT_ONLY');
 $write($failed);$grantValid=false;htDeny(fn()=>uRetryStoppedPreflight('123',$r),'EXACT_WORKSPACE_GRANT_MISSING');ht(uJson(U_REPORT)===$failed,'EXPIRED_GRANT_PRESERVES_REPORT');$grantValid=true;
 uRetryStoppedPreflight('123',$r);$saved=glob($op.'/update-result-https-stopped-*.json');ht(!file_exists(U_REPORT)&&count($saved)===1&&uJson($saved[0])===$failed&&$grantCalls===2,'AUTHORIZED_UNTOUCHED_PREFLIGHT_REPORT_PRESERVED');
 echo 'HTTPS_TRANSPORT_RETRY_PASS assertions='.$n.PHP_EOL;
}finally{foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST)as$e){if($e->isDir()&&!$e->isLink())rmdir($e->getPathname());else unlink($e->getPathname());}rmdir($tmp);}
