<?php
// Author by Lab | zefry. Owner branding uses the validated raster media store, no executable SVG.
declare(strict_types=1);
namespace App\Delivery\Http\BusinessExperience;
use Illuminate\Http\Request;
use App\Application\Platform\{PlatformOwnerAccess,PlatformStore,PlatformViolation};
final class BrandingController {
 public function read():mixed{app(PlatformOwnerAccess::class)->require();$r=app('db')->table('oneqay_platform_branding')->where('id',1)->first();return response()->json(['version'=>(int)($r->version??0),'url'=>$r?->media_id?'/site/media/'.$r->media_id:null]);}
 public function save(Request $r):mixed{
  $a=app(PlatformOwnerAccess::class)->require();$file=$r->file('image');abort_unless($file&&$file->isValid()&&$file->getSize()<=1024*1024&&$r->input('reviewed')==='true'&&ctype_digit((string)$r->input('version')),422);$bytes=file_get_contents($file->getRealPath());$image=app(\App\Application\Website\WebsiteManagement::class)->upload($bytes);$version=(int)$r->input('version');
  try{app(PlatformStore::class)->once($a->identityId()->value(),'branding-'.$version.'-'.substr(hash('sha256',$bytes),0,32),['version'=>$version,'media'=>$image['id']],function()use($version,$image,$a){$db=app('db')->connection();$db->table('oneqay_mail_settings')->where('id',1)->lockForUpdate()->firstOrFail();$row=$db->table('oneqay_platform_branding')->where('id',1)->first();if((int)($row->version??0)!==$version)throw new PlatformViolation('STALE_VERSION',409);$db->table('oneqay_platform_branding')->updateOrInsert(['id'=>1],['media_id'=>$image['id'],'version'=>$version+1]);app(PlatformStore::class)->audit($a->identityId()->value(),'website.branding',str_repeat('0',32),'Owner published reviewed application logo',time());return ['saved'=>true];});return $this->read();}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status);}
 }
}
