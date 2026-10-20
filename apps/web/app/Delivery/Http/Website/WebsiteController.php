<?php
declare(strict_types=1);
namespace App\Delivery\Http\Website;
use App\Application\Website\{WebsiteManagement,WebsiteStore,WebsiteContent};
use App\Application\Platform\{PlatformOwnerAccess,PlatformViolation};
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Author by Lab | zefry
final readonly class WebsiteController {
    public function __construct(private WebsiteManagement $site,private WebsiteStore $store,private PlatformOwnerAccess $access){}
    public function home(Request $r):mixed{return $this->page($r,'home');}
    public function page(Request $r,string $slug):mixed{return $this->respond($r,function()use($r,$slug){if($slug==='navigation')abort(404);$doc=$this->store->published(WebsiteContent::slug($slug));abort_if($doc===null,404);return Inertia::render('Website/Page',['document'=>$doc,'navigation'=>$this->store->published('navigation'),'preview'=>false])->toResponse($r);});}
    public function registration(Request $r):mixed{return $this->respond($r,fn()=>Inertia::render('Website/Register',['navigation'=>$this->store->published('navigation')])->toResponse($r));}
    public function resend(Request $r):mixed{return $this->respond($r,fn()=>response()->json($this->site->resend($this->payload($r,1024)),202));}
    public function register(Request $r):mixed{return $this->respond($r,fn()=>response()->json($this->site->register($this->payload($r,4096)),202));}
    public function editor(Request $r,string $slug='home'):mixed{return $this->respond($r,fn()=>Inertia::render('Website/Editor',$this->site->editor($slug))->toResponse($r));}
    public function command(Request $r,string $slug):mixed{return $this->respond($r,fn()=>response()->json(['status'=>'saved','record'=>$this->site->command($slug,$this->payload($r,120000))]));}
    public function preview(Request $r,string $slug):mixed{return $this->respond($r,function()use($r,$slug){$p=$this->site->preview($slug);if($r->query('format')==='json')return response()->json($p);return Inertia::render('Website/Page',['document'=>$p['document'],'navigation'=>$this->store->draft('navigation')['document'],'preview'=>true])->toResponse($r);});}
    public function upload(Request $r):mixed{return $this->respond($r,function()use($r){$this->access->require();$file=$r->file('image');if($file===null||!$file->isValid()||$file->getSize()>3*1024*1024)throw new PlatformViolation('INVALID_IMAGE');return response()->json(['status'=>'saved','record'=>$this->site->upload(file_get_contents($file->getRealPath()))]);});}
    public function media(Request $r,string $id):mixed{$preview=$r->is('platform/*');if($preview)$this->access->require();$image=$this->store->media($id,$preview);abort_if($image===null,404);return response($image['bytes'],200,['Content-Type'=>$image['mime'],'X-Content-Type-Options'=>'nosniff','Content-Security-Policy'=>"default-src 'none'",'Cache-Control'=>'no-store','Content-Disposition'=>'inline; filename="'.$id.'"']);}
    private function payload(Request $r,int $max):array{if(strlen($r->getContent())>$max)throw new PlatformViolation('REQUEST_TOO_LARGE',413);return $r->except('_token');}
    private function respond(Request $r,callable $work):mixed{try{$res=$work();$res->headers->set('Cache-Control','no-store, private');$res->headers->set('Referrer-Policy','same-origin');if($r->is('platform/*'))$res->headers->set('X-Robots-Tag','noindex, nofollow');return $res;}catch(PlatformViolation $e){$code=$e->reason;$status=$e->status;}catch(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){throw $e;}catch(\Throwable $e){\Illuminate\Support\Facades\Log::error('Website operation failed',['exception_class'=>$e::class]);$code='WEBSITE_OPERATION_UNAVAILABLE';$status=503;}return response()->json(SafeErrorEnvelope::make($code,(string)$r->attributes->get('oneqay.correlation_id','correlation-missing')),$status,['Cache-Control'=>'no-store']);}
}
