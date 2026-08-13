<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ProductId
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));

        if ($canonical === '' || strlen($canonical) > 64) {
            throw new InvalidArgumentException('Product identifier is required and must not exceed 64 characters.');
        }

        if (preg_match('/\A[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?\z/', $canonical) !== 1) {
            throw new InvalidArgumentException('Product identifier format is invalid.');
        }

        return new self($canonical);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
