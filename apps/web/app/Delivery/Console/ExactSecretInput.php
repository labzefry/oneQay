<?php

declare(strict_types=1);

namespace App\Delivery\Console;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Question\Question;

// Author by Lab | zefry. Password bytes are never normalized or echoed.
final class ExactSecretInput
{
    public function __construct(private readonly OutputStyle $output)
    {
    }

    public function secret(string $label): mixed
    {
        $question = (new Question($label))
            ->setHidden(true)
            ->setHiddenFallback(false)
            ->setTrimmable(false)
            // Input streams may include Enter; preserve every password space.
            ->setNormalizer(static fn (mixed $value): mixed => is_string($value)
                ? preg_replace('/(?:\r\n|\n|\r)\z/', '', $value)
                : $value);

        return $this->output->askQuestion($question);
    }
}
