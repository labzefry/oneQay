<?php
declare(strict_types=1);
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Infrastructure\Platform\{ControlPlaneCampaigns,ControlPlaneTelemetry};
use App\Application\Notification\MailWorker;
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
// Author by Lab | zefry. Bounded cPanel cron, with application lock against overlapping invocations.
final class ControlPlaneWorkCommand extends Command {
 protected $signature='oneqay:control:work {--limit=20}';
 protected $description='Sample application health and process reviewed campaigns and SMTP outbox.';
 public function handle(ControlPlaneCampaigns $campaigns,ControlPlaneTelemetry $health,MailWorker $mail):int{
  if(!config('platform.enabled')||!config('database.oneqay_persistence_enabled')||!ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return self::FAILURE;
  $lock=@fopen(storage_path('framework/oneqay-control-worker.lock'),'c');if(!$lock)return self::FAILURE;if(!flock($lock,LOCK_EX|LOCK_NB)){fclose($lock);$this->line('{"state":"already_running"}');return self::SUCCESS;}
  try{$health->sample();$audience=$campaigns->work(50);$sent=$mail->run((int)$this->option('limit'));$this->line(json_encode(['at'=>time(),'campaign_recipients_processed'=>$audience,'mail_processed'=>$sent]));return self::SUCCESS;}
  catch(\Throwable){$this->error('CONTROL_WORKER_UNAVAILABLE');return self::FAILURE;}
  finally{flock($lock,LOCK_UN);fclose($lock);}
 }
}
