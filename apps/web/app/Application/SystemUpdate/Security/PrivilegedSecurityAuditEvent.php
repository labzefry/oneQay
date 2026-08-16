<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PrivilegedSecurityAuditEvent
{
    private const ACTIONS = ['step_up', 'authorize_install'];
    private const OUTCOMES = ['granted', 'denied'];

    private function __construct(
        private string $action,
        private string $outcome,
        private ?string $identityId,
        private int $occurredAtUnix,
        private ?string $failureCode,
    ) {
    }

    public static function granted(
        string $action,
        PlatformIdentityId $identityId,
        int $occurredAtUnix,
    ): self {
        return self::make($action, 'granted', $identityId->value(), $occurredAtUnix, null);
    }

    public static function denied(
        string $action,
        ?string $identityId,
        int $occurredAtUnix,
        string $failureCode,
    ): self {
        return self::make($action, 'denied', $identityId, $occurredAtUnix, $failureCode);
    }

    /** @return array<string, int|string|null> */
    public function safeContext(): array
    {
        return [
            'event' => 'oneqay.privileged_update_security',
            'action' => $this->action,
            'outcome' => $this->outcome,
            'identity_id' => $this->identityId,
            'capability' => PrivilegedUpdateCapability::INSTALL,
            'occurred_at_unix' => $this->occurredAtUnix,
            'failure_code' => $this->failureCode,
        ];
    }

    private static function make(
        string $action,
        string $outcome,
        ?string $identityId,
        int $occurredAtUnix,
        ?string $failureCode,
    ): self {
        if (! in_array($action, self::ACTIONS, true)) {
            throw new InvalidArgumentException('Privileged audit action is invalid.');
        }

        if (! in_array($outcome, self::OUTCOMES, true)) {
            throw new InvalidArgumentException('Privileged audit outcome is invalid.');
        }

        $canonicalIdentity = null;
        if ($identityId !== null) {
            $canonicalIdentity = PlatformIdentityId::fromString($identityId)->value();
        }

        if ($occurredAtUnix <= 0) {
            throw new InvalidArgumentException('Privileged audit timestamp is invalid.');
        }

        if ($failureCode !== null && preg_match('/\A[A-Z0-9_]{3,64}\z/', $failureCode) !== 1) {
            throw new InvalidArgumentException('Privileged audit failure code is invalid.');
        }

        return new self($action, $outcome, $canonicalIdentity, $occurredAtUnix, $failureCode);
    }
}
