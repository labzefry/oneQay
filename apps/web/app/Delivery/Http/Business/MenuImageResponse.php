<?php
// Author by Lab | zefry. Sanitized raster images only; publication revocation is not cached.
declare(strict_types=1);
namespace App\Delivery\Http\Business;
final class MenuImageResponse
{
 public static function make(array $image):\Illuminate\Http\Response
 {
  return response($image['bytes'],200,[
   'Content-Type'=>$image['mime'],
   'Content-Length'=>(string)strlen($image['bytes']),
   'Content-Disposition'=>'inline; filename="menu-cover.'.($image['mime']==='image/png'?'png':'jpg').'"',
   'Cache-Control'=>'no-store, private',
   'X-Content-Type-Options'=>'nosniff',
   'Content-Security-Policy'=>"default-src 'none'; sandbox",
   'Cross-Origin-Resource-Policy'=>'same-origin',
   'X-Robots-Tag'=>'noindex',
  ]);
 }
}
