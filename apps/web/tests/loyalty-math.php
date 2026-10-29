<?php
// Author by Lab | zefry. Standalone arithmetic contract; no checkout or persistence implied.
declare(strict_types=1);
require __DIR__.'/../app/Application/BusinessLoyalty/LoyaltyMath.php';
use App\Application\BusinessLoyalty\LoyaltyMath as L;
$n=0;$assert=static function(bool $v)use(&$n):void{if(!$v)throw new RuntimeException('LOYALTY_MATH_ASSERTION_FAILED');$n++;};
$reject=static function(callable $f)use($assert):void{try{$f();}catch(InvalidArgumentException){$assert(true);return;}$assert(false);};
$assert(L::percentageDiscount(19999,1250,100000)===2499);
$assert(L::percentageDiscount(100000,5000,10000)===10000);
$assert(L::percentageDiscount(1000000000000,10000,1000000000000)===1000000000000);
$assert(L::earnedPoints(9999,10000,2)===0);
$assert(L::earnedPoints(29999,10000,2)===4);
$assert(L::redeemDiscount(200,300,100,5000,12000)===10000);
foreach([fn()=>L::percentageDiscount(-1,100,100),fn()=>L::percentageDiscount(100,10001,100),fn()=>L::earnedPoints(100,0,1),fn()=>L::earnedPoints(1000000000000,1,1000000000),fn()=>L::redeemDiscount(301,300,100,5000,20000),fn()=>L::redeemDiscount(150,300,100,5000,20000),fn()=>L::redeemDiscount(300,300,100,5000,14999),fn()=>L::redeemDiscount(0,300,100,5000,20000)] as $f)$reject($f);
echo "LOYALTY_MATH_PASS assertions=$n\n";
