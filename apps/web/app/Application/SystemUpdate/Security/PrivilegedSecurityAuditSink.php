<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

// Author by Lab | zefry
interface PrivilegedSecurityAuditSink
{
    public function record(PrivilegedSecurityAuditEvent $event): void;
}
