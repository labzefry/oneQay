<?php
declare(strict_types=1);
// Author by Lab | zefry. Real outbox/retention SQL and Symfony adapter; synthetic transport never contacts a mail server.
require __DIR__.'/platform-control-mail.php';
$now=time();$db->table('oneqay_mail_messages')->whereIn('status',['queued','retry'])->update(['next_attempt_at'=>$now+86400]);
$cfg=$mail->configuration();unset($cfg['password_configured'],$cfg['version']);$cfg['enabled']=true;$cfg['password']='synthetic-smtp-secret';$mail->configure($cfg,$mail->configuration()['version'],'fixture-owner',$now);
$id=$mail->enqueue('fixture-tenant','delivery-cycle','delivery@example.test','Synthetic delivery','Synthetic outbox message',$now);
$transport=new class implements App\Application\Notification\MailTransport {
 public function send(array $m):array{return ['accepted','SMTP_ACCEPTED'];}
 public function inspect():array{return [];}
};
$worker=new App\Application\Notification\MailWorker($mail,$transport);$cycle=new App\Application\Platform\ControlWorkCycle();
$result=$cycle->run(fn()=>$worker->run(1),fn()=>throw new RuntimeException('Synthetic NOC unavailable'),fn()=>throw new RuntimeException('Synthetic campaign unavailable'));
$assert($result['mail_processed']===1&&$result['failed']&&$result['health']['state']==='failed','SMTP delivers despite independent health/campaign failures');
$assert($db->table('oneqay_mail_messages')->where('id',$id)->value('status')==='accepted','actual persistent outbox moves to accepted, not delivered');
$assert($db->table('oneqay_mail_events')->where('message_id',$id)->where('safe_code','SMTP_ATTEMPT_STARTED')->exists(),'each SMTP attempt appears in delivery events');
$result=$cycle->run(fn()=>throw new RuntimeException('Synthetic SMTP storage failure'),fn()=>['healthy'=>true],fn()=>2);
$assert($result['health']['state']==='completed'&&$result['campaign_recipients_processed']===2,'mail failure cannot silently skip other phases');
$payload=['id'=>str_repeat('f',32),'recipient'=>'recipient@example.test','subject'=>'Synthetic','text'=>'No external send'];
$fake=function(string $stage):App\Infrastructure\Notification\SmtpMailTransport {
 global $mail;
 return new App\Infrastructure\Notification\SmtpMailTransport($mail,static fn($c)=>new class($stage) extends Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport {
  public function __construct(private string $stage){parent::__construct('smtp.example.test',465,true);}
  public function start():void {if($this->stage==='connection')throw new Symfony\Component\Mailer\Exception\TransportException('Synthetic connection failed');if($this->stage==='authentication')throw new Symfony\Component\Mailer\Exception\TransportException('Synthetic authentication failed',535);}
  public function send(Symfony\Component\Mime\RawMessage $message,?Symfony\Component\Mailer\Envelope $envelope=null):?Symfony\Component\Mailer\SentMessage {if($this->stage==='ambiguous')throw new Symfony\Component\Mailer\Exception\TransportException('Synthetic connection lost after DATA');if($this->stage==='temporary')throw new Symfony\Component\Mailer\Exception\TransportException('Synthetic rejection',451);return null;}
  public function stop():void {}
 },static fn($host)=>['203.0.113.10']);
};
$assert($fake('connection')->send($payload)===['temporary','SMTP_CONNECTION_FAILED'],'pre-DATA network failure retries safely');
$assert($fake('authentication')->send($payload)===['permanent','SMTP_535'],'authentication failure produces actionable safe code');
$assert($fake('ambiguous')->send($payload)===['unknown','SMTP_RESULT_UNKNOWN'],'lost DATA acknowledgment never blindly resends');
$assert($fake('temporary')->send($payload)===['temporary','SMTP_451'],'explicit transient SMTP rejection retries');
$assert($fake('accepted')->send($payload)===['accepted','SMTP_ACCEPTED'],'SMTP acceptance never claims inbox delivery');
// Expire only delivery histories and contents; keep active queue and minimal exactly-once tombstones.
$old=$now-259201;$recent=$now-259200;
$db->table('oneqay_mail_messages')->where('id',$id)->update(['updated_at'=>$old]);
$db->table('oneqay_mail_events')->where('message_id',$id)->update(['occurred_at'=>$old]);
$pending=$mail->enqueue('fixture-tenant','retained-pending','pending@example.test','Pending','Must survive retention',$old);
$db->table('oneqay_mail_messages')->where('id',$pending)->update(['updated_at'=>$old,'next_attempt_at'=>$now+86400]);
$boundary=$mail->enqueue('fixture-tenant','retention-boundary','boundary@example.test','Boundary','Retain at exact cutoff',$recent);
$db->table('oneqay_mail_messages')->where('id',$boundary)->update(['status'=>'accepted','updated_at'=>$recent]);
$stats=$mail->prune($now);
$assert($stats['events_removed']>=2&&$stats['message_contents_removed']>=1,'72-hour worker cleanup removes delivery history and encrypted content');
$assert($db->table('oneqay_mail_messages')->where('id',$id)->value('payload')==='','expired message recipient/body removed');
$assert($mail->enqueue('fixture-tenant','delivery-cycle','delivery@example.test','Synthetic delivery','Synthetic outbox message',$now)===$id,'retention keeps dedupe identity and cannot resend an accepted operation');
$assert($db->table('oneqay_mail_messages')->where('id',$pending)->value('payload')!=='','active queues are never deleted by log retention');
$assert($db->table('oneqay_mail_messages')->where('id',$boundary)->value('payload')!=='','exact 72-hour boundary retained');
$assert(!$db->table('oneqay_mail_events')->where('occurred_at','<',$recent)->exists(),'old delivery event rows removed');
$assert($mail->report(1)['retention_days']===3&&$mail->report(1)['sending_state']==='READY','dashboard exposes retention and runnable transport state');
$assert(!in_array($id,array_column($mail->report(1)['messages'],'id'),true),'expired histories excluded from visible reports');
// A damaged encrypted payload must produce a failed report instead of poisoning every subsequent cron.
$broken=$mail->enqueue('fixture-tenant','broken-payload','broken@example.test','Synthetic','Synthetic',$now);
$db->table('oneqay_mail_messages')->where('id',$broken)->update(['payload'=>'not-valid-ciphertext']);
$db->table('oneqay_mail_messages')->where('id',$boundary)->update(['next_attempt_at'=>$now+86400]);
$mail->claim($now);
$assert($db->table('oneqay_mail_messages')->where('id',$broken)->value('safe_code')==='MAIL_PAYLOAD_UNREADABLE','unreadable payload is visible and cannot block queue indefinitely');
echo 'MAIL_DELIVERY_RETENTION_'.$engine.'_PASS assertions='.$n.PHP_EOL;
