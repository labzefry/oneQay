<?php

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_SALE_CORRECTION_WORKSPACE_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
