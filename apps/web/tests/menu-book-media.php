<?php
// Author by Lab | zefry. Included by menu-book-catalog.php; synthetic scoped fixtures only.
declare(strict_types=1);
$images=$app->make(App\Infrastructure\Business\LaravelMenuBookMedia::class);
$contexts->clear();
try{$images->store('synthetic-invalid-image','menu-media-test');$assert(false,'anonymous upload accepted');}
catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='CONTEXT_REQUIRED','anonymous media upload denied');}
$contexts->setVerified($businessOwner);
foreach(['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',str_repeat('x',3*1024*1024+1)]as$invalid){
 try{$images->store($invalid,'menu-media-test');$assert(false,'invalid image accepted');}
 catch(App\Application\Business\BusinessViolation $e){$assert(in_array($e->reason,['MENU_IMAGE_INVALID','MENU_IMAGE_SIZE_INVALID'],true),'non-raster and oversized uploads denied');}
}
if(!function_exists('imagecreatefromstring')){
 $assert(getenv('REQUIRE_MENU_GD')!=='1','CI must exercise real GD image processing');
 $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR+0AAAAASUVORK5CYII=');
 try{$images->store($png,'menu-media-test');$assert(false,'unavailable image processor accepted');}
 catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_PROCESSING_UNAVAILABLE','missing image processor fails closed');}
 echo "MENU_MEDIA_GD_UNAVAILABLE: successful image lifecycle requires GD-enabled CI\n";
}else{
 $makeImage=static function(int $red):string{$im=imagecreatetruecolor(4,3);imagefill($im,0,0,imagecolorallocate($im,$red,70,90));ob_start();imagepng($im);$png=ob_get_clean();imagedestroy($im);return $png;};
 $original=$makeImage(80).'synthetic-private-metadata';
 $stored=$images->store($original,'menu-media-test');$image=$images->privateImage($stored['id']);
 $assert($image!==null&&$image['mime']==='image/png'&&!str_contains($image['bytes'],'synthetic-private-metadata'),'re-encoded image strips appended private metadata');
 $assert(getimagesizefromstring($image['bytes'])[0]===4,'normalized image remains decodable');
 $assert($images->store($original,'menu-media-test')===$stored&&$db->table('oneqay_menu_book_media')->where($scope)->count()===1,'same normalized image is stored once');
 $assert($db->table('oneqay_business_events')->where('tenant_id',$scope['tenant_id'])->where('action','menu.media.upload')->count()===1,'deduplicated upload has one audit event');
 $contexts->setVerified($otherOwner);
 $assert($images->privateImage($stored['id'])===null,'other business cannot retrieve private image by known ID');
 try{$menu->savePublication('foreign-cover-test-001',['title'=>'Other','description'=>'','cover_id'=>$stored['id'],'action'=>'draft','version'=>0],'menu-media-test');$assert(false,'foreign cover assigned');}
 catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_NOT_FOUND','foreign cover cannot be selected');}
 $contexts->setVerified($businessOwner);
 $private=$controller->media($previewRequest,$stored['id']);
 $assert($private->getStatusCode()===200&&$private->headers->get('X-Content-Type-Options')==='nosniff'&&str_contains($private->headers->get('Cache-Control'),'no-store'),'private image response prevents sniffing and caching');
 $file=tmpfile();fwrite($file,$original);$filePath=stream_get_meta_data($file)['uri'];
 $uploadRequest=Illuminate\Http\Request::create('/pos/business/menu-book/media','POST',[],[],['image'=>new Illuminate\Http\UploadedFile($filePath,'untrusted.php','application/x-php',null,true)]);
 $uploadResponse=$controller->uploadMedia($uploadRequest);fclose($file);
 $assert($uploadResponse->getStatusCode()===200&&$uploadResponse->getData(true)['record']['id']===$stored['id'],'multipart upload uses decoded content, never submitted filename or MIME');
 $version=$menu->publication()['version'];
 $menu->savePublication('cover-draft-test-001',['title'=>'Cover menu','description'=>'','cover_id'=>$stored['id'],'action'=>'draft','version'=>$version++],'menu-media-test');
 $preview=json_decode($controller->preview($previewRequest)->getContent(),true);
 $assert($preview['props']['cover_url']==='/pos/business/menu-book/media/'.$stored['id'],'saved draft uses private cover route');
 config(['platform.business_domains_enabled'=>true]);
 try{$public->cover($requestPublic,$stored['id']);$assert(false,'unpublished cover served');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'unpublished cover not public');}
 $menu->savePublication('cover-publish-test-001',['title'=>'Cover menu','description'=>'','cover_id'=>$stored['id'],'action'=>'publish','version'=>$version++],'menu-media-test');
 $publicImage=$hostMiddleware->handle($requestPublic,fn($r)=>$public->cover($r,$stored['id']));
 $assert($publicImage->getContent()===$image['bytes']&&$publicImage->headers->get('Cross-Origin-Resource-Policy')==='same-origin','active verified host serves only its published raster image');
 $publicData=json_decode($public->show($requestPublic)->getContent(),true);
 $assert($publicData['props']['cover_url']==='/menu/media/'.$stored['id'],'public menu exposes only public selected-cover URL');
 $replacement=$images->store($makeImage(160),'menu-media-test');
 $menu->savePublication('cover-replace-draft-001',['title'=>'Cover menu','description'=>'','cover_id'=>$replacement['id'],'action'=>'draft','version'=>$version++],'menu-media-test');
 $assert($public->cover($requestPublic,$stored['id'])->getStatusCode()===200,'draft replacement does not alter published cover');
 foreach([[$requestPublic,$replacement['id']],[$otherRequest,$stored['id']],[$noHost,$stored['id']]]as[$req,$imageId]){
  try{$public->cover($req,$imageId);$assert(false,'unselected or foreign cover exposed');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'cover requires selected snapshot and verified business host');}
 }
 $suspended=clone $requestPublic;$suspended->attributes->set('oneqay.host_business',array_replace($b,['status'=>'suspended']));
 try{$public->cover($suspended,$stored['id']);$assert(false,'suspended cover served');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'suspension blocks public image');}
 $menu->savePublication('cover-remove-publish-001',['title'=>'Cover menu','description'=>'','cover_id'=>null,'action'=>'publish','version'=>$version++],'menu-media-test');
 try{$public->cover($requestPublic,$stored['id']);$assert(false,'removed cover remains public');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'publishing removal revokes old image');}
 // Compatibility: omission of the new field preserves the draft cover for older clients.
 $menu->savePublication('cover-restore-draft-001',['title'=>'Cover menu','description'=>'','cover_id'=>$stored['id'],'action'=>'draft','version'=>$version++],'menu-media-test');
 $menu->savePublication('cover-legacy-client-001',['title'=>'Cover menu','description'=>'','action'=>'publish','version'=>$version++],'menu-media-test');
 $assert($public->cover($requestPublic,$stored['id'])->getStatusCode()===200,'old client can publish without discarding saved cover');
 $menu->savePublication('cover-unpublish-test-001',['title'=>'Cover menu','description'=>'','action'=>'unpublish','version'=>$version++],'menu-media-test');
 try{$public->cover($requestPublic,$stored['id']);$assert(false,'cover served after unpublish');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'unpublication revokes image');}
 // Quota is checked under the same organization lock as inserts; failed writes roll back audit/operation state.
 $db->table('oneqay_menu_book_media')->where($scope)->where('id',$stored['id'])->update(['bytes'=>20*1024*1024]);
 try{$images->store($makeImage(220),'menu-media-test');$assert(false,'quota exceeded');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_QUOTA_EXCEEDED','scoped image quota enforced');}
 $assert($db->table('oneqay_menu_book_media')->where($scope)->count()===2,'quota rejection persists no extra image');
 $db->table('oneqay_menu_book_media')->where($scope)->where('id',$stored['id'])->update(['bytes'=>$stored['bytes'],'payload'=>'invalid-base64']);
 $assert($images->privateImage($stored['id'])===null,'damaged stored bytes are never served');
 $db->table('oneqay_menu_book_media')->where($scope)->where('id',$stored['id'])->update(['payload'=>base64_encode($image['bytes'])]);
 $settingsReset=(new App\Infrastructure\Platform\ControlPlaneBusinessReset($db,$app->make('encrypter')))->preview(array_replace($b,['status'=>'suspended','updated_at'=>time()-120]),'settings');
 $tables=array_column($settingsReset['entries'],'table');$assert(in_array('oneqay_menu_book_media',$tables,true)&&array_search('oneqay_menu_book_publication',$tables,true)<array_search('oneqay_menu_book_media',$tables,true),'settings reset removes publication before private media');

 $listing=$images->listing();$assert($listing['used_bytes']===$stored['bytes']+$replacement['bytes']&&count($listing['items'])===2,'gallery reports scoped usage without image payloads');
 $assert(!str_contains(json_encode($listing),'payload')&&!str_contains(json_encode($listing),'sha256'),'gallery does not return private stored bytes');
 try{$images->remove('remove-selected-cover-001',$stored['id'],'menu-media-test');$assert(false,'selected cover deleted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_IN_USE','draft and retained snapshot cover protected from deletion');}
 $contexts->setVerified($otherOwner);$assert($images->listing()['items']===[],'second tenant gallery is independent');
 try{$images->remove('remove-foreign-image-001',$replacement['id'],'menu-media-test');$assert(false,'foreign image deleted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_NOT_FOUND','cross-tenant image deletion denied');}
 $contexts->setVerified($businessOwner);$removed=$images->remove('remove-unused-image-001',$replacement['id'],'menu-media-test');
 $assert($removed['deleted']&&$images->remove('remove-unused-image-001',$replacement['id'],'menu-media-test')===$removed&&$images->privateImage($replacement['id'])===null,'unused media deletion is durable and idempotent');
 $assert($images->store($makeImage(160),'menu-media-test')===$replacement,'deleted image may be uploaded again without stale operation replay');
 $count=$db->table('oneqay_menu_book_media')->where($scope)->count();$dummy=[];
 for($i=$count;$i<100;$i++){$fake=bin2hex(random_bytes(16));$dummy[]=$fake;$db->table('oneqay_menu_book_media')->insert($scope+['id'=>$fake,'sha256'=>str_repeat('0',64),'mime'=>'image/png','bytes'=>1,'payload'=>'AA==','created_at'=>time()]);}
 try{$images->store($makeImage(200),'menu-media-test');$assert(false,'image count limit bypassed');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_COUNT_EXCEEDED','small image flood limited per outlet');}
 $db->table('oneqay_menu_book_media')->where($scope)->whereIn('id',$dummy)->delete();
 $large=imagecreatetruecolor(4001,1);ob_start();imagepng($large);$largePng=ob_get_clean();imagedestroy($large);
 try{$images->store($largePng,'menu-media-test');$assert(false,'pixel dimension exceeded');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_IMAGE_INVALID','image dimension bound enforced before decode');}
 $contexts->clear();try{$images->remove('anonymous-remove-image',$stored['id'],'menu-media-test');$assert(false,'anonymous deletion permitted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='CONTEXT_REQUIRED','anonymous deletion denied');}
 $contexts->clear();$assert($controller->media($previewRequest,$stored['id'])->getStatusCode()===403,'anonymous private image read denied');$contexts->setVerified($businessOwner);
 config(['platform.business_domains_enabled'=>false]);
}
