<?php
// Author by Lab | zefry. Exercise the actual verifier against a public homepage and a separate owner login.
declare(strict_types=1);
$source=file_get_contents(__DIR__.'/../cpanel/update-oneqaydev-pos-enterprise.php');
$tmp=sys_get_temp_dir().'/oneqay-owner-route-'.bin2hex(random_bytes(8));mkdir($tmp,0700);
$source=preg_replace("~const U_ROOT = '[^']+';~",'const U_ROOT = '.var_export($tmp.'/private',true).';',$source,1);
$source=preg_replace('~const L_APP = [^;]+;~','const L_APP = '.var_export($tmp.'/previous-app',true).';',$source,1);
$source=str_replace('function uHttps(', 'function fixtureUnusedHttps(', $source);
file_put_contents($tmp.'/operator.php',$source);require $tmp.'/operator.php';
$active=[];$requests=[];$badOwner=false;$badBrowser=false;$badTrace=false;$n=0;
function oqVerifyHttps(string $path,string $want):void {if(!in_array([$path,$want],[['/health/live','ok'],['/health/ready','ready']],true))throw new RuntimeException('Unexpected health probe');}
function bEntryEvidence(array $response):array {return $response['fixture_entry'];}
function uHttps(string $url,string $accept):array {
 global $active,$requests,$badOwner,$badBrowser,$badTrace;$path=parse_url($url,PHP_URL_PATH);$requests[]=$path;$p=uPaths($active);
 $status=str_starts_with($path,'/platform')||str_starts_with($path,'/pos')||str_starts_with($path,'/api/pos/')?401:200;
 $entry=['status'=>$status,'entry_phase'=>'native-gate-passed','missing_php_capabilities'=>[],'merchant_entry_enabled'=>false,'login_context_present'=>false,'secure_session_cookie'=>true];
 $body='<html lang="id"><div>Website/Page</div></html>';
 if($path==='/owner-login'){$entry['merchant_entry_enabled']=true;$entry['login_context_present']=!$badOwner;$body='<html lang="id"><div>Foundation</div></html>';}
 if($path==='/register')$body='<html lang="id"><div>Website/Register</div></html>';
 if(str_starts_with($path,'/build/'))$body='synthetic-asset';
 $headers='X-OneQay-Business-Bridge: '.$p['bridge_id']."\r\n";
 if($accept==='text/html'&&in_array($path,['/pos/desktop','/platform/control/overview'],true)){
  $status=$badBrowser?401:303;$headers.='Location: '.($path==='/pos/desktop'?'/business-login':'/owner-login')."?reason=session-expired\r\n";$body='';
 }
 if(str_starts_with($path,'/oneqay-page-not-found-')){$status=404;$body='<html><svg></svg>PAGE_HTTP_404</html>';if(!$badTrace)$headers.="X-Correlation-ID: abcdef01-2345-6789-abcd-0123456789ab\r\n";}
 return ['status'=>$status,'headers'=>$headers,'body'=>$body,'fixture_entry'=>$entry];
}
$ok=static function(bool $value,string $why)use(&$n):void{if(!$value)throw new RuntimeException($why);++$n;};
try{
 foreach([L_SOURCE,U_SOURCE]as$commit){
  $active=['source_commit'=>$commit,'release_id'=>'production-'.substr($commit,0,12)];$p=uPaths($active);mkdir($p['app'].'/public/build/assets',0700,true);
  file_put_contents($p['app'].'/public/build/manifest.json',json_encode(['resources/js/app.ts'=>['file'=>'assets/app.js','css'=>['assets/app.css']]]));
  foreach(['app.js','app.css']as$file)file_put_contents($p['app'].'/public/build/assets/'.$file,'synthetic-asset');
  $requests=[];$badOwner=false;$evidence=uPublicVerify($active);
  $ok($requests[0]==='/owner-login','Both website predecessor and candidate must probe owner-login');
  $ok($evidence['merchant_entry']['login_context_present']===true,'Owner evidence must contain the private login context');
  if($commit===U_SOURCE){
   $ok($evidence['/']['login_context_exposed']===false,'Public homepage keeps its independent no-context check');
   $ok($evidence['browser/pos/desktop']['login_redirect']===true&&$evidence['browser/platform/control/overview']['login_redirect']===true,'Both browser login contexts verified');
   $ok($evidence['friendly_404']['correlation_header']===true,'Traceable illustrated 404 verified');
   $badBrowser=true;try{uPublicVerify($active);throw new RuntimeException('Raw browser JSON accepted');}catch(WorkspaceUpdateStop $e){$ok($e->getMessage()==='BROWSER_SESSION_REDIRECT_INVALID','Browser session regression rejected');}$badBrowser=false;
   $badTrace=true;try{uPublicVerify($active);throw new RuntimeException('Untraceable error accepted');}catch(WorkspaceUpdateStop $e){$ok($e->getMessage()==='FRIENDLY_ERROR_HTTPS_INVALID','Missing NOC reference rejected');}$badTrace=false;
  }
  $badOwner=true;
  try{uPublicVerify($active);throw new RuntimeException('Missing owner context accepted');}
  catch(WorkspaceUpdateStop $e){$ok($e->getMessage()==='NEW_MERCHANT_HTTPS_INVALID','Missing owner context still fails closed');}
 }
 echo 'POS_ENTERPRISE_HTTPS_PASS assertions='.$n.PHP_EOL;
}finally{
 $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
 foreach($iterator as$item){if($item->isDir())rmdir($item->getPathname());else unlink($item->getPathname());}rmdir($tmp);
}
