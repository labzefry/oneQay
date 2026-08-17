<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RoleIdentifier
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));

        if (
            $canonical === ''
            || strlen($canonical) > 64
            || preg_match('/\A[a-z][a-z0-9_-]*\z/', $canonical) !== 1
            || $canonical === 'platform-superadmin'
            || str_starts_with($canonical, 'platform-')
            || str_starts_with($canonical, 'platform_')
        ) {
            throw new InvalidArgumentException('Role identifier is invalid.');
        }

        return new self($canonical);
    }

    public function value(): string
    {
        return $this->value;
    }
}
