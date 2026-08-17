<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PermissionIdentifier
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));
        $containsIdentitySegment = false;

        foreach (explode('.', $canonical) as $segment) {
            if (str_starts_with($segment, 'tenant_') || str_starts_with($segment, 'user_')) {
                $containsIdentitySegment = true;
                break;
            }
        }

        if (
            strlen($canonical) > 96
            || preg_match('/\A[a-z][a-z0-9_-]*(?:\.[a-z][a-z0-9_-]*)+\z/', $canonical) !== 1
            || $containsIdentitySegment
            || str_starts_with($canonical, 'platform.')
        ) {
            throw new InvalidArgumentException('Permission identifier is invalid.');
        }

        return new self($canonical);
    }

    public function value(): string
    {
        return $this->value;
    }
}
