<?php

declare(strict_types=1);

namespace App\Application\Identity;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class IdentityAuthenticationEligibilityMutationId
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $canonical = strtolower(trim($value));
        if (preg_match('/\A[a-z0-9][a-z0-9_-]{0,63}\z/', $canonical) !== 1) {
            throw new InvalidArgumentException('Identity authentication eligibility mutation identifier is invalid.');
        }

        return new self($canonical);
    }

    public function value(): string
    {
        return $this->value;
    }
}
