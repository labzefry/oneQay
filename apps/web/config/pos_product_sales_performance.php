<?php

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
