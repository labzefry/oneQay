<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;

// Author by Lab | zefry
final readonly class SystemUpdateSecretReference
{
    public const APP_KEY = 'APP_KEY';

    private function __construct(private string $name)
    {
    }

    public static function appKey(): self
    {
        return new self(self::APP_KEY);
    }

    public static function fromName(string $name): self
    {
        $canonical = strtoupper(trim($name));

        if ($canonical !== self::APP_KEY) {
            throw new SystemUpdateControlPlaneViolation('shared_secret_reference_forbidden');
        }

        return new self($canonical);
    }

    public function safeName(): string
    {
        return $this->name;
    }
}
