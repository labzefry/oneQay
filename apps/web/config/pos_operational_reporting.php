<?php

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
    'sale_history_enabled' => filter_var(
        env('ONEQAY_POS_SALE_HISTORY_WORKSPACE_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
