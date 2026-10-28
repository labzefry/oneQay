<?php
// Author by Lab | zefry.
declare(strict_types=1);
namespace App\Application\BusinessExperience;
use App\Application\Platform\PlatformViolation;
final class BusinessDomain {
 public const TYPES=['retail','food','services','other','laundry','boarding','homestay'];
 public static function slug(mixed $value):string {
  if(!is_string($value)||preg_match('/\A[a-z][a-z0-9-]{2,39}\z/D',$value)!==1||str_ends_with($value,'-')||str_contains($value,'--')||in_array($value,['www','api','app','admin','owner','platform','mail','smtp','ftp','cpanel','webmail','autodiscover','support','assets','static','dev','test','staging','register','login','oneqay','status','billing','security','localhost'],true))throw new PlatformViolation('SUBDOMAIN_INVALID',422);
  return $value;
 }
 public static function root(string $url):string {
  $parts=parse_url($url);$host=strtolower($parts['host']??'');
  if(($parts['scheme']??'')!=='https'||(isset($parts['port'])||isset($parts['user']))||isset($parts['pass'])||!preg_match('/\A[a-z0-9.-]+\.[a-z]{2,}\z/D',$host)||filter_var($host,FILTER_VALIDATE_IP)||strlen($host)>200||!in_array($parts['path']??'',['','/'],true)||isset($parts['query'])||isset($parts['fragment']))throw new PlatformViolation('BUSINESS_DOMAIN_NOT_CONFIGURED',503);
  if(strlen($host)>253)throw new PlatformViolation('BUSINESS_DOMAIN_NOT_CONFIGURED',503);
  foreach(explode('.',$host)as$label)if(!preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/D',$label))throw new PlatformViolation('BUSINESS_DOMAIN_NOT_CONFIGURED',503);
  return $host;
 }
 public static function type(mixed $type):string {if(!is_string($type)||!in_array($type,self::TYPES,true))throw new PlatformViolation('BUSINESS_TYPE_INVALID',422);return $type;}
 public static function commercial(string $type):bool{return in_array($type,['retail','food','services','other'],true);}
}
