<?php

declare(strict_types=1);

namespace App\Application\Identity;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class IssuedPrivilegedTotpRecoveryCodeSet
{
    /** @var list<string> */
    private array $codes;

    /** @param list<string> $codes */
    public function __construct(array $codes)
    {
        if (count($codes) !== 8 || count(array_unique($codes)) !== 8) {
            throw new InvalidArgumentException('Privileged TOTP recovery code set is invalid.');
        }
        foreach ($codes as $code) {
            if (! is_string($code) || preg_match('/\Amq1\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}\z/D', $code) !== 1) {
                throw new InvalidArgumentException('Privileged TOTP recovery code set is invalid.');
            }
        }
        $this->codes = array_values($codes);
    }

    /** @return list<string> */
    public function codes(): array
    {
        return $this->codes;
    }
}
