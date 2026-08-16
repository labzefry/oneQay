<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSecretPresenceProbe;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSecretReference;

// Author by Lab | zefry
final class LaravelSystemUpdateSecretPresenceProbe implements SystemUpdateSecretPresenceProbe
{
    public function available(SystemUpdateSecretReference $reference): bool
    {
        if ($reference->safeName() !== SystemUpdateSecretReference::APP_KEY) {
            return false;
        }

        $value = config('app.key');

        return is_string($value)
            && trim($value) !== ''
            && ! str_contains($value, 'REPLACE_WITH_');
    }
}
