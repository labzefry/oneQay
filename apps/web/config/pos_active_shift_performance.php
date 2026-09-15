<?php

declare(strict_types=1);

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
