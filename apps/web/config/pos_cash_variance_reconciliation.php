<?php

declare(strict_types=1);

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_CASH_VARIANCE_RECONCILIATION_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
