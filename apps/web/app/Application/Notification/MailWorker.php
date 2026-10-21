<?php
declare(strict_types=1);
namespace App\Application\Notification;
// Author by Lab | zefry. Short cron invocation, no resident worker or Redis required.
final readonly class MailWorker
{
    public function __construct(private MailStore $store, private MailTransport $transport) {}
    public function run(int $limit = 20): int
    {
        $processed = 0;
        $deadline = time() + 45;
        $this->store->heartbeat(time());
        $this->store->prune(time());
        while ($processed < max(1, min(20, $limit)) && time() < $deadline) {
            $message = $this->store->claim(time());
            if ($message === null) break;
            try { $result = $this->transport->send($message); }
            catch (\Throwable) { $result = ['unknown', 'TRANSPORT_RESULT_UNKNOWN']; }
            // A crash here leaves an expired lease. Recovery marks it unknown;
            // it never silently resends a potentially accepted message.
            $this->store->finish($message, $result[0], $result[1], time());
            ++$processed;
        }
        $this->store->heartbeat(time());
        return $processed;
    }
}
