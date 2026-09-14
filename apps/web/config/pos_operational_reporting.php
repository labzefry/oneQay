<?php

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
