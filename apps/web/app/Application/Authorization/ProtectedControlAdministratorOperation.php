<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ProtectedControlAdministratorOperation
{
    public const DELEGATE = 'control.administrator.delegate';
    public const REVOKE = 'control.administrator.revoke';

    private function __construct(private string $value)
    {
    }

    public static function delegate(): self
    {
        return new self(self::DELEGATE);
    }

    public static function revoke(): self
    {
        return new self(self::REVOKE);
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));
        if (! in_array($canonical, [self::DELEGATE, self::REVOKE], true)) {
            throw new InvalidArgumentException('Protected control administrator operation is invalid.');
        }

        return new self($canonical);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isDelegate(): bool
    {
        return $this->value === self::DELEGATE;
    }

    public function isRevoke(): bool
    {
        return $this->value === self::REVOKE;
    }
}
