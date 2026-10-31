<?php
// Author by Lab | zefry. Policy tests; no account, mail or checkout mutation.
declare(strict_types=1);
require __DIR__.'/../app/Application/BusinessLoyalty/LoyaltyLots.php';
use App\Application\BusinessLoyalty\LoyaltyLots as L;
try {
$n=0;
$assert=static function(bool $ok)use(&$n):void{if(!$ok)throw new RuntimeException('LOYALTY_LOTS_ASSERTION_FAILED');++$n;};
$reject=static function(callable $fn,string $code)use($assert):void{try{$fn();}catch(InvalidArgumentException $e){$assert($e->getMessage()===$code);return;}$assert(false);};
$t=static fn(string $s):int=>(new DateTimeImmutable($s,new DateTimeZone('Asia/Jakarta')))->getTimestamp();
$now=$t('2026-10-10 12:00:00');
$lot=static fn(string $id,int $remaining,string $expiry):array=>['id'=>$id,'tenant_id'=>'tenant-a','member_id'=>'member-a','earned'=>100,'remaining'=>$remaining,'earned_at'=>$t('2026-10-01 08:00:00'),'expires_at'=>$t($expiry)];
$lots=[$lot('later',80,'2026-10-30 12:00:00'),$lot('soon',40,'2026-10-15 12:00:00'),$lot('middle',60,'2026-10-20 12:00:00'),$lot('next-month',30,'2026-11-05 12:00:00'),$lot('expired',25,'2026-10-09 12:00:00')];
$summary=L::summary($lots,'tenant-a','member-a',$now,new DateTimeZone('Asia/Jakarta'));
$assert($summary['balance']===210);$assert($summary['earned_this_month']===500);$assert($summary['expiring_this_month']===180);
$assert(array_column($summary['reminders'],'days')===[5,10,20]);
$assert(L::allocate($lots,'tenant-a','member-a',$now,70,false)===[['lot_id'=>'soon','points'=>40],['lot_id'=>'middle','points'=>30]]);
$assert(L::allocate(array_reverse($lots),'tenant-a','member-a',$now,70,false)===L::allocate($lots,'tenant-a','member-a',$now,70,false));
$reject(fn()=>L::allocate($lots,'tenant-a','member-a',$now,211,false),'LOYALTY_INSUFFICIENT_POINTS');
$reject(fn()=>L::allocate($lots,'tenant-a','member-a',$now,10,true),'LOYALTY_PROMOTION_COMBINATION_DENIED');
$reject(fn()=>L::allocate($lots,'tenant-b','member-a',$now,10,false),'LOYALTY_LOT_SCOPE_MISMATCH');
$reject(fn()=>L::allocate($lots,'tenant-a','member-b',$now,10,false),'LOYALTY_LOT_SCOPE_MISMATCH');
$reject(fn()=>L::allocate([$lots[0],$lots[0]],'tenant-a','member-a',$now,10,false),'LOYALTY_LOT_ID_INVALID');
$reject(fn()=>L::allocate($lots,'tenant-a','member-a',$now,0,false),'LOYALTY_REDEMPTION_INVALID');
$assert(L::expiresAt($now,30)===$now+30*86400);
$reject(fn()=>L::expiresAt($now,0),'LOYALTY_EXPIRY_INVALID');
$reject(fn()=>L::expiresAt(PHP_INT_MAX,30),'LOYALTY_EXPIRY_INVALID');
$exact=[$lot('exact',100,'2026-10-10 12:00:00')];
$assert(L::summary($exact,'tenant-a','member-a',$now,new DateTimeZone('Asia/Jakarta'))['balance']===0);
$reject(fn()=>L::allocate($exact,'tenant-a','member-a',$now,1,false),'LOYALTY_INSUFFICIENT_POINTS');
$future=$lots[0];$future['earned_at']=$now+1;
$reject(fn()=>L::summary([$future],'tenant-a','member-a',$now,new DateTimeZone('UTC')),'LOYALTY_LOT_VALUE_INVALID');
$bad=$lots[0];$bad['remaining']=101;
$reject(fn()=>L::summary([$bad],'tenant-a','member-a',$now,new DateTimeZone('UTC')),'LOYALTY_LOT_VALUE_INVALID');
$bad['remaining']='20';
$reject(fn()=>L::summary([$bad],'tenant-a','member-a',$now,new DateTimeZone('UTC')),'LOYALTY_LOT_VALUE_INVALID');
$monthEnd=$t('2026-10-31 12:00:00');
$assert(L::summary($lots,'tenant-a','member-a',$monthEnd,new DateTimeZone('Asia/Jakarta'))['reminders']===[]);
$nov=L::summary($lots,'tenant-a','member-a',$t('2026-11-01 12:00:00'),new DateTimeZone('Asia/Jakarta'));
$assert($nov['balance']===30&&$nov['earned_this_month']===0&&$nov['expiring_this_month']===30);
// Calendar-day reminders must not become four days across a daylight-saving boundary.
$ny=new DateTimeZone('America/New_York');$before=(new DateTimeImmutable('2026-03-05 12:00:00',$ny))->getTimestamp();
$dst=['id'=>'dst','tenant_id'=>'tenant-a','member_id'=>'member-a','earned'=>10,'remaining'=>10,'earned_at'=>$before-86400,'expires_at'=>(new DateTimeImmutable('2026-03-10 12:00:00',$ny))->getTimestamp()];
$assert(L::summary([$dst],'tenant-a','member-a',$before,$ny)['reminders'][0]['days']===5);
$assert(L::summary([],'tenant-a','member-a',$now,new DateTimeZone('UTC'))['balance']===0);
echo "LOYALTY_LOTS_PASS assertions=$n\n";
}catch(Throwable $e){fwrite(STDERR,"LOYALTY_LOTS_FAILED\n");exit(1);}
