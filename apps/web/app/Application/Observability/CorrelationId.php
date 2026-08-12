<?php

namespace App\Application\Observability;

final class CorrelationId
{
    private const PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public static function resolve(?string $incoming): string
    {
        if ($incoming !== null && preg_match(self::PATTERN, $incoming) === 1) {
            return $incoming;
        }

        return bin2hex(random_bytes(16));
    }

    private function __construct()
    {
    }
}
