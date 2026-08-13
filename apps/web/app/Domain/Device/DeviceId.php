<?php

namespace App\Domain\Device;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class DeviceId
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));

        if ($canonical === '' || strlen($canonical) > 96) {
            throw new InvalidArgumentException('Device identifier is required and must not exceed 96 characters.');
        }

        if (preg_match('/\A[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?\z/', $canonical) !== 1) {
            throw new InvalidArgumentException('Device identifier format is invalid.');
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
