<?php
declare(strict_types=1);
namespace App\Console\Commands;
use App\Application\Notification\{DsnFeedback,MailStore};
use App\Application\Platform\PlatformViolation;
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
use Illuminate\Console\Command;
// Author by Lab | zefry. cPanel email pipe: no stdout, no raw message logging, no outbound mail.
final class PlatformMailBounceCommand extends Command
{
    protected $signature='oneqay:mail:bounce';
    protected $description='Read one bounded token-bound RFC3464 report from trusted cPanel email piping.';
    public function handle(MailStore $mail):int
    {
        if(!config('platform.enabled') || !config('database.oneqay_persistence_enabled') || !ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return 75;
        try{
            $raw=stream_get_contents(STDIN,262145);if($raw===false)return 75;
            $parser=new DsnFeedback((string)config('platform.bounce_address',''),(string)config('platform.feedback_secret',''));
            $mail->feedback($parser->parse($raw),time());return 0;
        }catch(PlatformViolation){return 0;} // Discard invalid/forged input without generating bounce loops.
        catch(\Throwable){return 75;} // EX_TEMPFAIL: the MTA can retry a storage outage.
    }
}
