<?php
declare(strict_types=1);
namespace App\Application\Notification;
use App\Application\Platform\PlatformViolation;
// Author by Lab | zefry. Bounded RFC3464 reports tied to an unguessable per-message return address.
final readonly class DsnFeedback
{
    public function __construct(private string $mailbox,#[\SensitiveParameter] private string $secret) {}
    public function returnAddress(string $id):string
    {
        if(!preg_match('/\A[0-9a-f]{32}\z/',$id)||strlen($this->secret)<32||!preg_match('/\A([a-z0-9]{1,6})@([a-z0-9.-]+)\z/',$this->mailbox)||!filter_var($this->mailbox,FILTER_VALIDATE_EMAIL))throw new PlatformViolation('INVALID_BOUNCE_CONFIGURATION');
        [$local,$domain]=explode('@',$this->mailbox,2);
        // 96-bit MAC and 128-bit message identity keep the local part within 64 bytes.
        return $local.'+'.$id.'.'.substr(hash_hmac('sha256','bounce|'.$id.'|'.$this->mailbox,$this->secret),0,24).'@'.$domain;
    }
    public function parse(string $raw):array
    {
        if(strlen($raw)>262144 || str_contains($raw,"\0"))throw new PlatformViolation('INVALID_DSN');
        $raw=str_replace("\r\n","\n",$raw);$parts=explode("\n\n",$raw,2);if(count($parts)!==2)throw new PlatformViolation('INVALID_DSN');
        $headers=$this->headers($parts[0]);$type=$headers['content-type']??'';
        if(!preg_match('/\Amultipart\/report\s*;/i',$type)||!preg_match('/report-type\s*=\s*"?delivery-status"?(?:;|\s|$)/i',$type)
            ||!preg_match('/boundary\s*=\s*(?:"([^"\r\n]{1,70})"|([^;\s]{1,70}))/i',$type,$match))throw new PlatformViolation('INVALID_DSN');
        $boundary=$match[1]!==''?$match[1]:$match[2];
        $target=implode(' ',array_intersect_key($headers,array_flip(['to','delivered-to','envelope-to','x-original-to'])));
        if(!preg_match('/\+([0-9a-f]{32})\.([0-9a-f]{24})@/',$target,$identity))throw new PlatformViolation('DSN_TOKEN_REQUIRED');
        $id=$identity[1];$address=$this->returnAddress($id);
        if(!preg_match('/(?:^|[<\s,])'.preg_quote($address,'/').'(?:$|[>\s,])/i',$target))throw new PlatformViolation('DSN_TOKEN_INVALID');
        $reports=[];
        foreach(explode('--'.$boundary,$parts[1]) as $part){
            $section=explode("\n\n",ltrim($part,"\n"),2);if(count($section)!==2)continue;
            $mime=$this->headers($section[0]);
            if(strtolower(trim(explode(';',$mime['content-type']??'')[0]))!=='message/delivery-status')continue;
            if(!in_array(strtolower($mime['content-transfer-encoding']??'7bit'),['7bit','8bit','binary'],true))throw new PlatformViolation('UNSUPPORTED_DSN_ENCODING');
            foreach(preg_split('/\n[ \t]*\n/',trim($section[1])) as $block){
                $fields=$this->headers($block);if(!isset($fields['final-recipient']))continue;
                if(!preg_match('/\Arfc822;\s*(.+)\z/i',$fields['final-recipient'],$recipient)||!filter_var($recipient[1],FILTER_VALIDATE_EMAIL))throw new PlatformViolation('INVALID_DSN_RECIPIENT');
                $action=strtolower($fields['action']??'');$status=$fields['status']??'';
                if(!preg_match('/\A[245]\.[0-9]{1,3}\.[0-9]{1,3}\z/',$status))throw new PlatformViolation('INVALID_DSN_STATUS');
                $outcome=match(true){
                    $action==='failed' && in_array($status,['5.1.0','5.1.1','5.1.2'],true)=>'hard_bounce',
                    $action==='failed' && (str_starts_with($status,'4.') || $status==='5.2.2')=>'soft_bounce',
                    $action==='failed' && str_starts_with($status,'5.')=>'failed',
                    $action==='delayed' && str_starts_with($status,'4.')=>'deferred',
                    $action==='delivered' && str_starts_with($status,'2.')=>'delivered',
                    default=>throw new PlatformViolation('UNSUPPORTED_DSN_ACTION'),
                };
                $reports[]=['event_id'=>'dsn-'.hash('sha256',$raw.'|'.$recipient[1]),'message_id'=>$id,'recipient'=>$recipient[1],'type'=>$outcome];
            }
        }
        // Our queue always sends exactly one envelope recipient.
        if(count($reports)!==1)throw new PlatformViolation('INVALID_DSN_RECIPIENT_COUNT');
        return $reports[0];
    }
    private function headers(string $raw):array
    {
        if(strlen($raw)>32768)throw new PlatformViolation('INVALID_DSN_HEADERS');
        $raw=preg_replace('/\n[ \t]+/',' ',$raw);$result=[];
        foreach(explode("\n",$raw)as$line){
            if(!str_contains($line,':'))continue;
            [$key,$value]=explode(':',$line,2);$key=strtolower(trim($key));
            if(in_array($key,['content-type','content-transfer-encoding','to','delivered-to','envelope-to','x-original-to','final-recipient','action','status'],true)){
                if(isset($result[$key]))throw new PlatformViolation('DUPLICATE_DSN_FIELD');
                $result[$key]=trim($value);
            }
        }
        return $result;
    }
}
