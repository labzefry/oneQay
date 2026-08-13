<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class Cart
{
    /** @var list<CartLine> */
    private array $lines;

    /** @param list<CartLine> $lines */
    private function __construct(array $lines)
    {
        if ($lines === [] || count($lines) > 100) {
            throw new InvalidArgumentException('Cart must contain between one and 100 lines.');
        }

        $seenProducts = [];
        foreach ($lines as $line) {
            if (! $line instanceof CartLine) {
                throw new InvalidArgumentException('Cart contains an invalid line.');
            }

            $productId = $line->productId()->value();
            if (isset($seenProducts[$productId])) {
                throw new InvalidArgumentException('Cart must not contain duplicate product lines.');
            }

            $seenProducts[$productId] = true;
        }

        $this->lines = array_values($lines);
    }

    /** @param list<CartLine> $lines */
    public static function fromLines(array $lines): self
    {
        return new self($lines);
    }

    /** @return list<CartLine> */
    public function lines(): array
    {
        return $this->lines;
    }

    public function canonicalFingerprintPart(): string
    {
        return implode(',', array_map(
            static fn (CartLine $line): string => $line->canonicalFingerprintPart(),
            $this->lines,
        ));
    }
}
