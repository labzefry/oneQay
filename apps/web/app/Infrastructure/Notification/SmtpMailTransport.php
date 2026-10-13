<?php
declare(strict_types=1);
namespace App\Infrastructure\Notification;
use App\Application\Notification\MailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\{Address, Email};
// Author by Lab | zefry. Mandatory TLS; credentials and SMTP transcripts never enter reports.
final readonly class SmtpMailTransport implements MailTransport
{
    public function __construct(private LaravelMailStore $store) {}
    public function send(array $message): array
    {
        $c=$this->store->transportConfiguration();
        if(($c['enabled']??false)!==true)return ['temporary','SMTP_PAUSED'];
        $addresses=$this->addresses($c['host']);
        if($addresses===[])return ['temporary','SMTP_PUBLIC_DNS_REQUIRED'];
        // Pin the validated address to avoid DNS rebinding, while verifying the configured peer name.
        $transport=new EsmtpTransport((str_contains($addresses[0],':')?'['.$addresses[0].']':$addresses[0]),(int)$c['port'],(int)$c['port']===465);
        $transport->setUsername($c['username'])->setPassword($c['password'])->setRequireTls(true);
        $transport->getStream()->setTimeout(10);
        $transport->getStream()->setStreamOptions(['ssl'=>['peer_name'=>$c['host'],'verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false,'SNI_enabled'=>true,'crypto_method'=>STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT]]);
        $domain=substr($c['from'],strrpos($c['from'],'@')+1);
        $email=(new Email())->from(new Address($c['from'],$c['from_name']))->to($message['recipient'])->subject($message['subject'])->text($message['text']);
        $email->getHeaders()->addIdHeader('Message-ID','oneqay-'.$message['id'].'@'.$domain);
        $email->getHeaders()->addTextHeader('X-OneQay-Message-ID',$message['id']);
        try {
            $transport->send($email);
            return ['accepted','SMTP_ACCEPTED'];
        } catch (TransportExceptionInterface $e) {
            // An explicit SMTP negative reply is safe to classify. Connection loss
            // after DATA is ambiguous and must never trigger blind duplicate delivery.
            $code=(int)$e->getCode();
            if($code>=400 && $code<500)return ['temporary','SMTP_'.$code];
            if($code>=500 && $code<600) {
                $unknownRecipient=preg_match('/\b5\.1\.1\b/',$e->getMessage())===1;
                return ['permanent',$unknownRecipient?'SMTP_5.1.1':'SMTP_'.$code];
            }
            return ['unknown','SMTP_RESULT_UNKNOWN'];
        } finally { try{$transport->stop();}catch(\Throwable){} }
    }
    public function inspect(): array
    {
        $c=$this->store->configuration();
        if($c['host']==='' || $c['from']==='')return ['configured'=>false,'checked_at'=>time()];
        $domain=substr($c['from'],strrpos($c['from'],'@')+1);
        $txt=static function(string $name):array {
            $records=@dns_get_record($name,DNS_TXT);
            return $records===false?[]:array_column($records,'txt');
        };
        $spf=array_values(array_filter($txt($domain),fn($r)=>str_starts_with($r,'v=spf1 ')));
        $dkim=$txt($c['dkim_selector'].'._domainkey.'.$domain);
        $dmarc=array_values(array_filter($txt('_dmarc.'.$domain),fn($r)=>str_starts_with($r,'v=DMARC1;')));
        return ['configured'=>true,'checked_at'=>time(),'smtp_public_dns'=>$this->addresses($c['host'])!==[],
            'spf_record_present'=>count($spf)===1,'dkim_record_present'=>count(array_filter($dkim,fn($r)=>preg_match('/(?:^|;)\s*p=\s*[^;\s]+/',$r)===1))>0,
            'dmarc_record_present'=>count($dmarc)===1,'domain'=>$domain,'tls_required'=>true,
            'verification_scope'=>'DNS presence only; SPF authorization, DKIM signing/alignment, PTR and inbox placement require provider evidence'];
    }
    private function addresses(string $host): array
    {
        $records=@dns_get_record($host,DNS_A | DNS_AAAA);if($records===false)return [];
        $addresses=[];
        foreach($records as $record){
            $ip=$record['ip']??$record['ipv6']??null;
            if($ip===null)continue;
            if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)===false)return [];
            $addresses[]=$ip;
        }
        return $addresses;
    }
}
