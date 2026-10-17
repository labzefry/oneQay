<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
// Author by Lab | zefry. Country hints are trusted only from a direct Cloudflare peer.
final class LocalePreference {
 private const CLOUDFLARE = ['173.245.48.0/20','103.21.244.0/22','103.22.200.0/22','103.31.4.0/22','141.101.64.0/18','108.162.192.0/18','190.93.240.0/20','188.114.96.0/20','197.234.240.0/22','198.41.128.0/17','162.158.0.0/15','104.16.0.0/13','104.24.0.0/14','172.64.0.0/13','131.0.72.0/22','2400:cb00::/32','2606:4700::/32','2803:f800::/32','2405:b500::/32','2405:8100::/32','2a06:98c0::/29','2c0f:f248::/32'];
 public static function country(Request $request): ?string {
  $peer=(string)$request->server('REMOTE_ADDR','');
  if(!filter_var($peer,FILTER_VALIDATE_IP)||!IpUtils::checkIp($peer,self::CLOUDFLARE))return null;
  $country=strtoupper((string)$request->header('CF-IPCountry',''));
  return preg_match('/\A[A-Z]{2}\z/D',$country)&&$country!=='XX'?$country:null;
 }
 public static function initial(Request $request): string {
  $country=self::country($request);
  if($country!==null)return $country==='ID'?'id':'en';
  return str_starts_with(strtolower($request->getLanguages()[0]??''),'id')?'id':'en';
 }
}
