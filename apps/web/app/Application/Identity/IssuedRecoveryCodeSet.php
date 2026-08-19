<?php

declare(strict_types=1);

namespace App\Application\Identity;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class IssuedRecoveryCodeSet
{
    private const CODE_PATTERN = '/\Arq1\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}\z/D';
    private const CODE_COUNT = 8;

    /** @param list<string> $codes */
    public function __construct(#[\SensitiveParameter] private array $codes)
    {
        if (count($this->codes) !== self::CODE_COUNT) {
            throw new InvalidArgumentException('Issued recovery code set is invalid.');
        }

        $seen = [];
        foreach ($this->codes as $code) {
            if (! is_string($code) || preg_match(self::CODE_PATTERN, $code) !== 1 || isset($seen[$code])) {
                throw new InvalidArgumentException('Issued recovery code set is invalid.');
            }
            $seen[$code] = true;
        }
    }

    /** @return list<string> */
    public function codes(): array
    {
        return $this->codes;
    }
}
