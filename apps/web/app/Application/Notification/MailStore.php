<?php
declare(strict_types=1);
namespace App\Application\Notification;
// Author by Lab | zefry
interface MailStore
{
    public function enqueue(string $tenant, string $key, string $recipient, string $subject, string $text, int $now): string;
    public function claim(int $now): ?array;
    public function finish(array $message, string $outcome, string $code, int $now): void;
    public function feedback(array $event, int $now): void;
    public function retry(string $id, string $actor, string $reason, bool $acceptDuplicateRisk, int $now): void;
    public function report(int $page): array;
    public function configuration(): array;
    public function configure(array $settings, int $version, string $actor, int $now): void;
    public function heartbeat(int $now): void;
    public function prune(int $now): array;
}
