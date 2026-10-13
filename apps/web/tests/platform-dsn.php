<?php
// Author by Lab | zefry. Pure offline RFC3464 and token binding fixtures.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
use App\Application\Notification\DsnFeedback;
use App\Application\Platform\PlatformViolation;
$n=0;$assert=function(bool $ok,string $why)use(&$n){++$n;if(!$ok)throw new RuntimeException($why);};
$reject=function(callable $f,string $why)use($assert){try{$f();}catch(PlatformViolation){$assert(true,$why);return;}$assert(false,$why);};
$dsn=new DsnFeedback('bounce@example.test',str_repeat('s',40));$id=str_repeat('a',32);$address=$dsn->returnAddress($id);
$assert(strlen(explode('@',$address)[0])===64,'RFC local-part length');
$make=fn(string $action,string $status):string=>"From: MAILER-DAEMON@example.test\r\nTo: <$address>\r\nContent-Type: multipart/report;\r\n report-type=delivery-status; boundary=\"test-boundary\"\r\n\r\n--test-boundary\r\nContent-Type: text/plain\r\n\r\nDelivery result\r\n--test-boundary\r\nContent-Type: message/delivery-status\r\n\r\nReporting-MTA: dns; smtp.example.test\r\n\r\nFinal-Recipient: rfc822; recipient@example.test\r\nAction: $action\r\nStatus: $status\r\n\r\n--test-boundary--\r\n";
foreach([['failed','5.1.1','hard_bounce'],['failed','5.2.2','soft_bounce'],['failed','4.2.2','soft_bounce'],['failed','5.7.1','failed'],['delayed','4.2.0','deferred'],['delivered','2.0.0','delivered']]as[$action,$status,$type]){
 $event=$dsn->parse($make($action,$status));$assert($event['type']===$type,'Correct DSN classification');$assert($event['message_id']===$id && $event['recipient']==='recipient@example.test','message recipient binding');
}
$raw=$make('failed','5.1.1');$assert($dsn->parse($raw)===$dsn->parse($raw),'stable feedback identity');
$reject(fn()=>$dsn->parse(str_replace($address,'bounce+'.str_repeat('b',32).'.'.str_repeat('0',24).'@example.test',$raw)),'forged token denied');
$reject(fn()=>$dsn->parse(str_replace($address,str_replace('@example.test','@evil.test',$address),$raw)),'wrong bounce domain denied');
$reject(fn()=>$dsn->parse(str_replace('message/delivery-status','text/plain',$raw)),'plain text email is not a DSN');
$reject(fn()=>$dsn->parse(str_replace('Status: 5.1.1','Status: 5.1.1'."\r\n".'Status: 2.0.0',$raw)),'duplicate status denied');
$reject(fn()=>$dsn->parse($make('delivered','5.1.1')),'inconsistent DSN action denied');
$reject(fn()=>$dsn->parse(str_repeat('x',262145)),'oversize DSN denied');
echo 'PLATFORM_DSN_PASS assertions='.$n.PHP_EOL;
