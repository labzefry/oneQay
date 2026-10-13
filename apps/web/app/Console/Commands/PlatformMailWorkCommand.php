<?php
declare(strict_types=1);
namespace App\Console\Commands;
use App\Application\Notification\MailWorker;
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
use Illuminate\Console\Command;
// Author by Lab | zefry
final class PlatformMailWorkCommand extends Command
{
    protected $signature='oneqay:mail:work {--limit=20}';
    protected $description='Process a bounded persistent transactional SMTP queue for cPanel cron.';
    public function handle(MailWorker $worker):int
    {
        if(!config('platform.enabled') || !config('database.oneqay_persistence_enabled') || !ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return self::FAILURE;
        try{$count=$worker->run((int)$this->option('limit'));$this->line(json_encode(['processed'=>$count,'at'=>time()]));return self::SUCCESS;}
        catch(\Throwable){$this->error('MAIL_WORKER_STORAGE_OR_CONFIGURATION_FAILURE');return self::FAILURE;}
    }
}
