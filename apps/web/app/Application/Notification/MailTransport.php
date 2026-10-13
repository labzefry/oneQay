<?php
declare(strict_types=1);
namespace App\Application\Notification;
// Author by Lab | zefry
interface MailTransport
{
    /** Returns outcome accepted|temporary|permanent|unknown and a safe code. */
    public function send(array $message): array;
    /** Network/domain evidence, never a guarantee of inbox placement. */
    public function inspect(): array;
}
