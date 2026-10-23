<?php
// Author by Lab | zefry. Pure state/date/amount rules, isolated from stock-based checkout.
declare(strict_types=1);
namespace App\Application\BusinessExperience;
use App\Application\Platform\PlatformViolation;
final class ServiceWorkflow {
 public const MODULES=['laundry','boarding','homestay'];
 public static function text(mixed $v,int $max):string{if(!is_string($v)||trim($v)===''||mb_strlen($v)>$max||preg_match('/[\x00-\x1f\x7f]/',$v))throw new PlatformViolation('INVALID_FIELDS');return trim($v);}
 public static function amount(mixed $v,bool $zero=false):int{if(!is_int($v)||$v<($zero?0:1)||$v>100000000000)throw new PlatformViolation('INVALID_NUMERIC_VALUE');return $v;}
 public static function date(mixed $v):string{if(!is_string($v)||!preg_match('/\A20[0-9]{2}-[0-9]{2}-[0-9]{2}\z/D',$v)||!(($d=\DateTimeImmutable::createFromFormat('!Y-m-d',$v))&&$d->format('Y-m-d')===$v))throw new PlatformViolation('INVALID_DATE');return $v;}
 public static function total(string $type,int $rate,int $quantity,string $start,string $end):int {
  $start=self::date($start);$end=self::date($end);if($end<$start)throw new PlatformViolation('INVALID_DATE_RANGE');
  if($type==='laundry'){$n=$rate*$quantity;if($n>100000000000000)throw new PlatformViolation('AMOUNT_TOO_LARGE');return intdiv($n+500,1000);}
  if($end===$start)throw new PlatformViolation('INVALID_DATE_RANGE');$days=(int)(new \DateTimeImmutable($start))->diff(new \DateTimeImmutable($end))->days;if($days>3660)throw new PlatformViolation('DATE_RANGE_TOO_LONG');
  return $type==='homestay'?self::amount($rate*$days):$rate;
 }
 public static function nextPeriod(string $period,string $anchor):string{$date=(new \DateTimeImmutable(self::date($period)))->modify('first day of next month');return $date->setDate((int)$date->format('Y'),(int)$date->format('m'),min((int)substr(self::date($anchor),8),(int)$date->format('t')))->format('Y-m-d');}
 public static function transition(string $type,string $from,string $to,bool $paid):void {
  $allowed=$type==='laundry'?['received'=>['washing','cancelled'],'washing'=>['drying'],'drying'=>['ironing'],'ironing'=>['ready'],'ready'=>['collected']]:['reserved'=>['checked_in','cancelled'],'checked_in'=>['checked_out']];
  if(!in_array($to,$allowed[$from]??[],true))throw new PlatformViolation('WORKFLOW_TRANSITION_DENIED',409);
  if(in_array($to,['collected','checked_out'],true)&&!$paid)throw new PlatformViolation('INVOICE_NOT_PAID',409);
 }
}
