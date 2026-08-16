<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Security;

use App\Application\SystemUpdate\Security\PrivilegedSecurityAuditEvent;
use App\Application\SystemUpdate\Security\PrivilegedSecurityAuditSink;
use Psr\Log\LoggerInterface;

// Author by Lab | zefry
final readonly class SafeLogPrivilegedSecurityAuditSink implements PrivilegedSecurityAuditSink
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function record(PrivilegedSecurityAuditEvent $event): void
    {
        $this->logger->notice('oneqay.privileged_update_security', $event->safeContext());
    }
}
