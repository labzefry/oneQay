<?php
declare(strict_types=1);
namespace App\Application\Website;
use App\Application\Platform\PlatformViolation;
// Author by Lab | zefry. Strict typed blocks; no scripts, styles, embeds or executable images.
final class WebsiteContent {
    public static function slug(mixed $v):string {if(!is_string($v)||!preg_match('/\A[a-z][a-z0-9-]{0,63}\z/',$v))throw new PlatformViolation('INVALID_PAGE');return $v;}
    public static function text(mixed $v,int $max=500):string {if(!is_string($v)||strlen($v)>$max||preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/',$v))throw new PlatformViolation('INVALID_CONTENT');return trim($v);}
    public static function link(mixed $v):string {$v=self::text($v,150);if(!preg_match('~\A(?:/|/register|/business-login|/pages/[a-z][a-z0-9-]{0,63}|\#[a-z][a-z0-9-]{0,63})\z~',$v))throw new PlatformViolation('INVALID_LINK');return $v;}
    public static function rich(mixed $v):string {
        $html=self::text($v,12000);if($html==='')return '';$dom=new \DOMDocument();$old=libxml_use_internal_errors(true);
        try{$dom->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>',LIBXML_NONET|LIBXML_NOERROR|LIBXML_NOWARNING);}finally{libxml_clear_errors();libxml_use_internal_errors($old);}
        $walk=function(\DOMNode $n,int $depth=0)use(&$walk):string{
            if($depth>30)throw new PlatformViolation('CONTENT_TOO_DEEP');
            if($n instanceof \DOMText)return htmlspecialchars($n->textContent,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
            if(!$n instanceof \DOMElement)return '';$tag=strtolower($n->tagName);
            if(in_array($tag,['script','style','iframe','object','svg','math','template','form','input'],true))return '';
            $inside='';foreach($n->childNodes as $child)$inside.=$walk($child,$depth+1);
            if(!in_array($tag,['p','br','strong','b','em','i','u','ul','ol','li','h3','h4','blockquote','a'],true))return $inside;
            if($tag==='br')return '<br>';
            if($tag==='a'){try{$href=self::link($n->getAttribute('href'));}catch(PlatformViolation){return $inside;}return '<a href="'.htmlspecialchars($href,ENT_QUOTES,'UTF-8').'">'.$inside.'</a>';}
            return '<'.$tag.'>'.$inside.'</'.$tag.'>';
        };
        $body=$dom->getElementsByTagName('body')->item(0);return $body===null?'':$walk($body);
    }
    public static function document(array $input,WebsiteStore $store):array {
        if(array_diff(array_keys($input),['id','en']))throw new PlatformViolation('INVALID_CONTENT');$out=[];
        foreach(['id','en']as$locale){$v=$input[$locale]??null;if(!is_array($v)||array_diff(array_keys($v),['title','description','blocks']))throw new PlatformViolation('INVALID_CONTENT');$blocks=$v['blocks']??null;if(!is_array($blocks)||!array_is_list($blocks)||count($blocks)<1||count($blocks)>24)throw new PlatformViolation('INVALID_BLOCKS');$rows=[];$ids=[];
            foreach($blocks as$b){if(!is_array($b)||array_diff(array_keys($b),['id','type','tone','eyebrow','title','body','image','alt','button','href','items']))throw new PlatformViolation('INVALID_BLOCK');$id=self::slug($b['id']??null);if(isset($ids[$id]))throw new PlatformViolation('DUPLICATE_BLOCK');$ids[$id]=true;
                $type=$b['type']??null;$tone=$b['tone']??'light';if(!in_array($type,['hero','features','split','text','faq','cta','pricing'],true)||!in_array($tone,['light','soft','dark'],true))throw new PlatformViolation('INVALID_BLOCK');
                $image=self::text($b['image']??'',32);if($image!==''&&(!preg_match('/\A[a-f0-9]{32}\z/',$image)||!$store->hasMedia($image)))throw new PlatformViolation('INVALID_IMAGE');
                $items=$b['items']??[];if(!is_array($items)||!array_is_list($items)||count($items)>16)throw new PlatformViolation('INVALID_ITEMS');$clean=[];
                foreach($items as$item){if(!is_array($item)||array_diff(array_keys($item),['title','body','href','icon']))throw new PlatformViolation('INVALID_ITEMS');$icon=self::text($item['icon']??'store',40);if(!preg_match('/\A[a-z][a-z0-9-]{0,39}\z/',$icon))throw new PlatformViolation('INVALID_ICON');$clean[]=['title'=>self::text($item['title']??'',160),'body'=>self::text($item['body']??'',1200),'href'=>self::link($item['href']??'/register'),'icon'=>$icon];}
                $rows[]=['id'=>$id,'type'=>$type,'tone'=>$tone,'eyebrow'=>self::text($b['eyebrow']??'',100),'title'=>self::text($b['title']??'',220),'body'=>self::rich($b['body']??''),'image'=>$image,'alt'=>self::text($b['alt']??'',220),'button'=>self::text($b['button']??'',60),'href'=>self::link($b['href']??'/register'),'items'=>$clean];
            }
            $out[$locale]=['title'=>self::text($v['title']??'',160),'description'=>self::text($v['description']??'',320),'blocks'=>$rows];if($out[$locale]['title']==='')throw new PlatformViolation('TITLE_REQUIRED');
        }
        if(strlen(json_encode($out,JSON_THROW_ON_ERROR))>100000)throw new PlatformViolation('CONTENT_TOO_LARGE',413);return $out;
    }
}
