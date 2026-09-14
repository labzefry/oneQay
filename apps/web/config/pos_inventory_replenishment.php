<?php

declare(strict_types=1);

// Author by Lab | zefry
return [
    'enabled' => filter_var(env('ONEQAY_POS_INVENTORY_REPLENISHMENT_ENABLED', false), FILTER_VALIDATE_BOOL),
];
