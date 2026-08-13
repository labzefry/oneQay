<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use InvalidArgumentException;
use OverflowException;

// Author by Lab | zefry
final readonly class Money
{
    private function __construct(
        private int $atomicUnits,
        private string $currency,
        private int $scale,
    ) {
    }

    public static function fromAtomicUnits(int $atomicUnits, string $currency, int $scale): self
    {
        $canonicalCurrency = strtoupper(trim($currency));

        if ($atomicUnits < 0) {
            throw new InvalidArgumentException('Money amount must not be negative.');
        }

        if (preg_match('/\A[A-Z]{3}\z/', $canonicalCurrency) !== 1) {
            throw new InvalidArgumentException('Money currency must be a three-letter code.');
        }

        if ($scale < 0 || $scale > 6) {
            throw new InvalidArgumentException('Money scale must be between zero and six.');
        }

        return new self($atomicUnits, $canonicalCurrency, $scale);
    }

    public function atomicUnits(): int
    {
        return $this->atomicUnits;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function scale(): int
    {
        return $this->scale;
    }

    public function multiply(int $quantity): self
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Money multiplication requires a positive quantity.');
        }

        if ($this->atomicUnits !== 0 && $quantity > intdiv(PHP_INT_MAX, $this->atomicUnits)) {
            throw new OverflowException('Money multiplication exceeds the supported integer range.');
        }

        return new self($this->atomicUnits * $quantity, $this->currency, $this->scale);
    }

    public function add(self $other): self
    {
        $this->assertCompatible($other);

        if ($other->atomicUnits > PHP_INT_MAX - $this->atomicUnits) {
            throw new OverflowException('Money addition exceeds the supported integer range.');
        }

        return new self(
            $this->atomicUnits + $other->atomicUnits,
            $this->currency,
            $this->scale,
        );
    }

    public function subtract(self $other): self
    {
        $this->assertCompatible($other);

        if ($other->atomicUnits > $this->atomicUnits) {
            throw new InvalidArgumentException('Money subtraction must not produce a negative amount.');
        }

        return new self(
            $this->atomicUnits - $other->atomicUnits,
            $this->currency,
            $this->scale,
        );
    }

    public function compare(self $other): int
    {
        $this->assertCompatible($other);

        return $this->atomicUnits <=> $other->atomicUnits;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && $this->scale === $other->scale
            && $this->atomicUnits === $other->atomicUnits;
    }

    public function canonicalFingerprintPart(): string
    {
        return $this->currency.':'.$this->scale.':'.$this->atomicUnits;
    }

    private function assertCompatible(self $other): void
    {
        if ($this->currency !== $other->currency || $this->scale !== $other->scale) {
            throw new InvalidArgumentException('Money currency and scale must match.');
        }
    }
}
