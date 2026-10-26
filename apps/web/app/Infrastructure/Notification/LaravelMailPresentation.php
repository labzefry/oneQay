<?php
declare(strict_types=1);
namespace App\Infrastructure\Notification;
// Author by Lab | zefry. Escaped multipart email with inline, Outlook-compatible layout.
final class LaravelMailPresentation
{
 public static function html(string $subject,string $text,string $publicUrl):string {
  $e=static fn(string $s):string=>htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
  $id=preg_match('/\b(Anda|akun|Verifikasi|kasir|Alasan|LAPORAN)\b/u',$text)===1;
  $lang=$id?'id':'en';$caption=$id?'Ruang kerja untuk bisnis Anda':'Your business workspace';
  $label=$id?'Verifikasi & aktifkan akun':'Verify & activate account';
  $footer=$id?'Email layanan oneQay. Jaga kerahasiaan tautan aktivasi dan informasi akun Anda.':'A oneQay service email. Keep activation links and account information private.';
  $content='';$origin=rtrim($publicUrl,'/');
  foreach(preg_split('/\R/u',$text)?:[]as$line){
   if(str_starts_with($origin,'https://')&&preg_match('~\A'.preg_quote($origin,'~').'/activate-business#token=[a-f0-9]{64}\z~D',trim($line))){
    $url=$e(trim($line));$help=$id?'Jika tombol tidak terbuka, salin tautan ini:':'If the button does not open, copy this link:';
    $content.='<table role="presentation" cellspacing="0" cellpadding="0"><tr><td bgcolor="#3965FA" style="border-radius:10px"><a href="'.$url.'" style="display:inline-block;padding:16px 24px;color:#FFFFFF;text-decoration:none;font-size:16px;font-weight:bold">'.$e($label).' &rarr;</a></td></tr></table><p style="font-size:14px;line-height:22px;word-break:break-all;color:#1B1B39">'.$help.'<br><a href="'.$url.'" style="color:#3965FA">'.$url.'</a></p>';
   }elseif(trim($line)!==''&&!in_array(trim($line),['Copyright oneQay','© oneQay'],true))$content.='<p style="margin:0 0 16px;font-size:16px;line-height:26px;color:#202020">'.$e($line).'</p>';
  }
  $title=$e($subject);$year=gmdate('Y');$caption=$e($caption);$footer=$e($footer);
  return <<<HTML
<!doctype html><html lang="$lang"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>$title</title></head><body style="margin:0;padding:0;background:#E9EBFF;font-family:Arial,Helvetica,sans-serif"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" bgcolor="#E9EBFF"><tr><td align="center" style="padding:32px 16px"><table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#FFFFFF;border:1px solid #99B7FC;border-radius:20px;overflow:hidden"><tr><td bgcolor="#1B1B39" style="padding:28px 32px;color:#FFFFFF"><strong style="font-size:28px;letter-spacing:-1px">one<span style="color:#99B7FC">Qay</span></strong><p style="margin:8px 0 0;font-size:14px;color:#E9EBFF">$caption</p></td></tr><tr><td style="padding:32px"><h1 style="margin:0 0 24px;font-size:26px;line-height:34px;color:#1B1B39">$title</h1>$content</td></tr><tr><td style="padding:24px 32px;border-top:1px solid #EDEDED"><p style="margin:0 0 12px;font-size:14px;line-height:22px;color:#1B1B39">$footer</p><span style="font-size:14px;color:#1B1B39">&copy; $year oneQay</span></td></tr></table></td></tr></table></body></html>
HTML;
 }
}
