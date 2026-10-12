<?php

declare(strict_types=1);

namespace App\Application\Business;

// Author by Lab | zefry
final class BusinessInput
{
    public const MAX_ATOMIC = 9000000000000000;

    public static function fields(array $input, array $required, array $optional = []): void
    {
        if (array_diff($required, array_keys($input)) !== [] || array_diff(array_keys($input), [...$required, ...$optional]) !== []) {
            throw new BusinessViolation('INVALID_FIELDS');
        }
    }

    public static function text(mixed $value, int $max = 160, bool $empty = false): string
    {
        if (!is_string($value) || strlen($value) > $max || preg_match('//u', $value) !== 1 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            throw new BusinessViolation('INVALID_TEXT');
        }
        $value = trim($value);
        if (!$empty && $value === '') throw new BusinessViolation('REQUIRED_FIELD');
        return $value;
    }

    public static function id(mixed $value): string
    {
        $value = self::text($value, 64);
        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{0,63}\z/', $value) !== 1) throw new BusinessViolation('INVALID_IDENTIFIER');
        return $value;
    }

    public static function integer(mixed $value, int $min = 0, int $max = self::MAX_ATOMIC): int
    {
        if (is_int($value)) $value = (string) $value;
        if (!is_string($value) || preg_match('/\A(?:0|[1-9][0-9]{0,15})\z/', $value) !== 1) throw new BusinessViolation('INVALID_NUMBER');
        $n = (int) $value;
        if ($n < $min || $n > $max) throw new BusinessViolation('NUMBER_OUT_OF_RANGE');
        return $n;
    }

    public static function add(int $left, int $right): int
    {
        if ($left < 0 || $right < 0 || $left > self::MAX_ATOMIC - $right) throw new BusinessViolation('AMOUNT_TOO_LARGE');
        return $left + $right;
    }

    public static function multiply(int $amount, int $quantity): int
    {
        if ($quantity < 1 || $amount < 0 || $amount > intdiv(self::MAX_ATOMIC, $quantity)) throw new BusinessViolation('AMOUNT_TOO_LARGE');
        return $amount * $quantity;
    }

    public static function date(mixed $value): string
    {
        $value = self::text($value, 10);
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($parsed === false || $parsed->format('Y-m-d') !== $value) throw new BusinessViolation('INVALID_DATE');
        return $value;
    }

    public static function currency(mixed $value): string
    {
        if (!is_string($value) || preg_match('/\A[A-Z]{3}\z/', $value) !== 1) throw new BusinessViolation('INVALID_CURRENCY');
        return $value;
    }

    public static function boolean(mixed $value): bool
    {
        if (!is_bool($value)) throw new BusinessViolation('INVALID_BOOLEAN');
        return $value;
    }

    public static function rows(mixed $rows, int $max = 100): array
    {
        if (!is_array($rows) || !array_is_list($rows) || count($rows) < 1 || count($rows) > $max) throw new BusinessViolation('INVALID_LINES');
        foreach ($rows as $row) if (!is_array($row)) throw new BusinessViolation('INVALID_LINES');
        return $rows;
    }

    public static function fingerprint(array $value): string
    {
        $sort = static function (array $v) use (&$sort): array {
            if (!array_is_list($v)) ksort($v);
            foreach ($v as &$item) if (is_array($item)) $item = $sort($item);
            return $v;
        };
        return hash('sha256', json_encode($sort($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
