<?php
// Author by Lab | zefry. Versioned create-only data repair; never an authenticated-user impersonation.
declare(strict_types=1);
use App\Application\BusinessExperience\BusinessDomain;
use App\Application\Platform\PlatformStore;
use App\Application\Notification\MailStore;
use App\Infrastructure\BusinessExperience\LaravelBusinessDomains;
use Illuminate\Database\Connection;

function ldNeed(bool $ok,string $code):void {if(!$ok)throw new RuntimeException($code);}
function ldHash(array $row):string {ksort($row);return hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));}
function ldState(Connection $db,string $id):array {
 $b=$db->table('oneqay_platform_businesses')->where('id',$id)->lockForUpdate()->first();
 $p=$db->table('oneqay_business_profiles')->where('business_id',$id)->lockForUpdate()->first();
 ldNeed($b!==null&&$p!==null&&$b->status==='active'&&$b->tenant_id===$p->tenant_id,'LEGACY_SCOPE_INVALID');
 BusinessDomain::type($p->business_type);
 return [(array)$b,(array)$p];
}
function ldPlan(Connection $db,LaravelBusinessDomains $domains,array $binding):array {
 return $db->transaction(function()use($db,$domains,$binding){
  (new App\Infrastructure\Identity\LaravelAccountEmailRegistry($db))->lock();
  $rows=$db->table('oneqay_platform_businesses as b')->join('oneqay_business_profiles as p','p.business_id','=','b.id')->where('b.status','active')->whereNotExists(function($q){$q->selectRaw('1')->from('oneqay_business_domains as d')->whereColumn('d.business_id','b.id');})->limit(2)->get(['b.id']);
  ldNeed(count($rows)===1,'EXACTLY_ONE_LEGACY_BUSINESS_REQUIRED');[$b,$p]=ldState($db,$rows[0]->id);
  $base=trim(substr(Illuminate\Support\Str::slug($b['name']),0,34),'-');
  if(!preg_match('/\A[a-z][a-z0-9-]{2,33}\z/D',$base))$base='bisnis-'.substr(hash('sha256',$b['id']),0,12);
  $slug=null;for($n=0;$n<100;$n++){$candidate=$base.($n===0?'':(string)($n+1));try{if($domains->available($candidate)){$slug=$candidate;break;}}catch(App\Application\Platform\PlatformViolation){}}
  ldNeed(is_string($slug),'NO_SAFE_DOMAIN_CANDIDATE');
  return ['repair'=>'legacy-business-domain-v1','binding'=>$binding,'business_id'=>$b['id'],'tenant_id'=>$b['tenant_id'],'business_hash'=>ldHash($b),'profile_hash'=>ldHash($p),'profile'=>$p,'slug'=>$slug,'root'=>$domains->root(),'created_at'=>time()];
 });
}
/** Called only after the CLI verifies the exact fresh native owner grant. No HTTP exposure. */
function ldApply(Connection $db,LaravelBusinessDomains $domains,PlatformStore $platform,MailStore $mail,array $plan,callable $backup):array {
 $digest=ldHash($plan);$actor='operator.labzefry';$operation='legacy-domain-'.$digest;
 ldNeed(($plan['repair']??null)==='legacy-business-domain-v1'&&$plan['root']===$domains->root(),'PLAN_SCOPE_CHANGED');
 BusinessDomain::slug($plan['slug']);
 $result=$platform->once($actor,$operation,['plan_sha256'=>$digest],function()use($db,$domains,$platform,$mail,$plan,$backup,$digest,$actor,$operation){
  [$b,$p]=ldState($db,$plan['business_id']);
  ldNeed($b['tenant_id']===$plan['tenant_id']&&ldHash($b)===$plan['business_hash']&&ldHash($p)===$plan['profile_hash'],'STALE_LEGACY_PLAN');
  ldNeed(!$db->table('oneqay_business_domains')->where('business_id',$b['id'])->exists(),'DOMAIN_HISTORY_ALREADY_EXISTS');
  ldNeed($domains->available($plan['slug']),'DOMAIN_CANDIDATE_TAKEN');
  $backup(['repair'=>'legacy-business-domain-v1','plan_sha256'=>$digest,'profile'=>$p,'domain_history'=>[],'business_hash'=>ldHash($b)]);
  $now=time();$db->table('oneqay_business_domains')->insert(['slug'=>$plan['slug'],'business_id'=>$b['id'],'active'=>true,'created_at'=>$now,'retired_at'=>null]);
  ldNeed($db->table('oneqay_business_profiles')->where('business_id',$b['id'])->where('tenant_id',$b['tenant_id'])->where('version',$p['version'])->update(['version'=>(int)$p['version']+1])===1,'PROFILE_VERSION_CHANGED');
  $url='https://'.$plan['slug'].'.'.$plan['root'];
  ldNeed($domains->hostBusiness($plan['slug'].'.'.$plan['root'])['id']===$b['id'],'DOMAIN_RESOLUTION_MISMATCH');
  $mail->enqueue($b['tenant_id'],$operation,$b['email'],'Alamat bisnis oneQay Anda / Your oneQay business address',"Halo pemilik bisnis,\nAlamat khusus bisnis Anda kini tersedia: $url\nGunakan akun dan pengaturan keamanan yang sama untuk masuk. Data dan jenis usaha Anda tetap tersimpan.\nJika memerlukan perubahan alamat, hubungi pemilik platform.\n\nYour business address is now available above. Your existing account, security settings and business data remain unchanged.",$now);
  $platform->audit($actor,'business.domain_backfilled',$b['id'],'legacy-business-domain-v1 plan '.$digest,$now);
  return ['state'=>'LEGACY_DOMAIN_CREATED','plan_sha256'=>$digest,'url'=>$url,'profile_version'=>(int)$p['version']+1,'mail'=>'QUEUED_NOT_INBOX_VERIFIED','business_status'=>'PRESERVED','credentials'=>'UNCHANGED','business_type'=>'UNCHANGED','schema'=>'UNCHANGED','at'=>$now];
 });
 // Replays never resurrect a subsequently renamed/retired domain or suspended account.
 [$b,$p]=ldState($db,$plan['business_id']);
 ldNeed($b['tenant_id']===$plan['tenant_id']&&$domains->profile($b['tenant_id'])['url']===$result['url'],'APPLIED_DOMAIN_CHANGED');
 return $result;
}
function ldRead(string $file):array {ldNeed(is_file($file)&&!is_link($file)&&filesize($file)<65536,'PRIVATE_JSON_INVALID');$v=json_decode(file_get_contents($file),true,32,JSON_THROW_ON_ERROR);ldNeed(is_array($v)&&!array_is_list($v),'PRIVATE_JSON_INVALID');return $v;}
function ldWrite(string $file,array $data):void {
 ldNeed(!is_link($file),'PRIVATE_FILE_LINK_DENIED');$raw=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";$stage=$file.'.stage-'.bin2hex(random_bytes(8));$mask=umask(0077);
 try{$h=fopen($stage,'x');}finally{umask($mask);}ldNeed(is_resource($h)&&chmod($stage,0600),'PRIVATE_WRITE_FAILED');
 try{ldNeed(fwrite($h,$raw)===strlen($raw)&&fflush($h),'PRIVATE_WRITE_FAILED');fclose($h);$h=null;ldNeed(rename($stage,$file),'PRIVATE_PROMOTION_FAILED');}finally{if(is_resource($h))fclose($h);if(is_file($stage))unlink($stage);}
}
function ldGrant(string $id,array $plan,int $pr):array {
 ldNeed(preg_match('/\A[1-9][0-9]{6,18}\z/D',$id)===1&&$pr>990,'OWNER_COMMENT_REQUIRED');
 $ch=curl_init('https://api.github.com/repos/labzefry/oneQay/issues/comments/'.$id);
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Accept: application/vnd.github+json','User-Agent: oneQay-Legacy-Domain-Repair']]);
 $raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);ldNeed($status===200&&is_string($raw)&&strlen($raw)<30000,'OWNER_FETCH_FAILED');$c=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
 ldNeed(($c['user']['login']??null)==='labzefry'&&($c['user']['type']??null)==='User'&&($c['author_association']??null)==='OWNER'&&($c['issue_url']??null)==='https://api.github.com/repos/labzefry/oneQay/issues/'.$pr,'OWNER_IDENTITY_DENIED');
 $at=strtotime($c['created_at']??'');ldNeed(is_int($at)&&($c['created_at']??null)===($c['updated_at']??null)&&time()>=$at&&time()<$at+3600,'FRESH_OWNER_GRANT_REQUIRED');
 $expected="ONEQAYDEV LEGACY DOMAIN REPAIR AUTHORIZATION\nPLAN_SHA256: ".ldHash($plan)."\nOPERATOR_SHA256: ".$plan['binding']['operator_sha256']."\nTARGET: https://oneqaydev.n07.my.id\nCREATE_MISSING_DOMAIN_ONLY: ONE_ACTIVE_EXISTING_PROFILE\nSCHEMA_CHANGE: NONE\nPRESERVE_DATA_STATUS_TYPE_MFA_CREDENTIALS_PERMISSIONS: REQUIRED\nWINDOW_SECONDS: 3600\nAuthor by Lab | zefry";
 ldNeed(hash_equals($expected,str_replace("\r\n","\n",$c['body']??'')),'EXACT_OWNER_GRANT_REQUIRED');return ['comment_id'=>(int)$id,'expires_at'=>$at+3600];
}
function ldRun(array $argv):array {
 ldNeed(PHP_SAPI==='cli'&&realpath(__DIR__)===__DIR__&&!is_link(__FILE__),'PRIVATE_CLI_REQUIRED');$mode=$argv[1]??'';ldNeed(in_array($mode,['inspect','apply','verify'],true),'COMMAND_INVALID');
 $config=ldRead(__DIR__.'/binding.json');ldNeed(hash_equals($argv[2]??'',ldHash($config)),'CONFIGURATION_BINDING_CHANGED');
 $package=$config['deployment_package'];ldNeed(preg_match('~\A/home/pekd7254/oneqay-production-test/oneqaydev-workspace-successor-[a-f0-9]{12}\z~D',$package)===1&&__DIR__==='/home/pekd7254/oneqay-production-test/legacy-domain-v1','PRIVATE_TARGET_REQUIRED');
 ldNeed(is_file($package.'/update.php')&&!is_link($package.'/update.php')&&hash_equals($config['deployment_operator_sha256'],hash_file('sha256',$package.'/update.php')),'DEPLOYMENT_OPERATOR_CHANGED');require $package.'/update.php';
 nRequest();$receipt=nJson($package.'/update-result.json');foreach($config['active_receipt']as$key=>$value)ldNeed(($receipt[$key]??null)===$value,'ACTIVE_RECEIPT_CHANGED');nVerifyActive($receipt);
 nHash($package.'/release-files.sha256',$config['inventory_sha256']);nInventory($package.'/release-files.sha256',N_ROOT.'/releases',nRelease(),6426,false);
 require nApp().'/vendor/autoload.php';$app=require nApp().'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
 ldNeed(App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===$config['active_receipt']['source']&&config('platform.business_domains_enabled')===true&&config('platform.public_url')==='https://oneqaydev.n07.my.id','ACTIVE_RUNTIME_REQUIRED');
 $db=$app->make('db')->connection();ldNeed($db->getDriverName()==='mysql','MYSQL_REQUIRED');$domains=$app->make(LaravelBusinessDomains::class);ldNeed($domains->root()==='n07.my.id','BUSINESS_ROOT_CHANGED');
 $binding=['configuration_sha256'=>ldHash($config),'operator_sha256'=>hash_file('sha256',__FILE__),'source'=>$config['active_receipt']['source']];
 if($mode==='inspect'){
  ldNeed(!file_exists(__DIR__.'/result.json'),'REPAIR_ALREADY_RECORDED');$current=ldPlan($db,$domains,$binding);if(is_file(__DIR__.'/plan.json')){$plan=ldRead(__DIR__.'/plan.json');$compare=$plan;$compare['created_at']=$current['created_at'];ldNeed($compare===$current,'EXISTING_PLAN_CHANGED');}else{$plan=$current;ldWrite(__DIR__.'/plan.json',$plan);}
  return ['state'=>'READY_CREATE_ONE_MISSING_DOMAIN','plan_sha256'=>ldHash($plan),'operator_sha256'=>$binding['operator_sha256'],'url'=>'https://'.$plan['slug'].'.'.$plan['root'],'schema_change'=>false,'at'=>time()];
 }
 $plan=ldRead(__DIR__.'/plan.json');ldNeed($plan['binding']===$binding,'PLAN_BINDING_CHANGED');
 if($mode==='apply'){$grant=ldGrant($argv[3]??'',$plan,$config['authorization_pr']);ldNeed(time()<$grant['expires_at'],'AUTHORITY_EXPIRED');
  $result=ldApply($db,$domains,$app->make(PlatformStore::class),$app->make(MailStore::class),$plan,function(array $before){$file=__DIR__.'/preimage.json';if(is_file($file))ldNeed(ldRead($file)===$before,'BACKUP_PREIMAGE_CHANGED');else ldWrite($file,$before);ldNeed(ldRead($file)===$before,'BACKUP_VERIFY_FAILED');});
  ldWrite(__DIR__.'/result.json',$result+['grant'=>$grant]);
 }else $result=ldRead(__DIR__.'/result.json');
 // Anonymous HTTPS acceptance is separate from a real owner login or SMTP inbox test.
 [$b,$p]=ldState($db,$plan['business_id']);ldNeed($b['tenant_id']===$plan['tenant_id']&&$domains->profile($plan['tenant_id'])['url']===$result['url'],'APPLIED_DOMAIN_CHANGED');
 $schema=nSchema($db);ldNeed(count($schema['ddl'])===108&&count($schema['receipts'])===41,'DATABASE_SHAPE_CHANGED');
 $http=[];foreach(['/business-login'=>200,'/owner-login'=>404]as$path=>$code){$http[$path]=nDomainHttp($plan['slug'].'.'.$plan['root'],$path);ldNeed($http[$path]['status']===$code,'DOMAIN_HTTPS_ACCEPTANCE_PENDING');}
 $unknown=nDomainHttp('oneqay-check-'.bin2hex(random_bytes(6)).'.'.$plan['root'],'/business-login');ldNeed($unknown['status']===404,'UNKNOWN_DOMAIN_NOT_DENIED');
 return $result+['acceptance'=>'ANONYMOUS_HTTPS_VERIFIED','https'=>$http,'unknown_host'=>'HTTP_404','authenticated_login'=>'NOT_TESTED','smtp_inbox'=>'NOT_TESTED'];
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){try{$result=ldRun($argv);ldWrite(__DIR__.'/'.(($argv[1]??'')==='inspect'?'inspect-result.json':'acceptance-result.json'),$result);fwrite(STDOUT,'ONEQAY|'.$result['state']."\n");}catch(Throwable $e){$code=$e instanceof RuntimeException&&preg_match('/\A[A-Z0-9_]+\z/D',$e->getMessage())?$e->getMessage():'LEGACY_DOMAIN_REPAIR_STOPPED';ldWrite(__DIR__.'/error.json',['state'=>'STOPPED','code'=>$code,'at'=>time()]);fwrite(STDERR,'ONEQAY|'.$code."\n");exit(1);}}
