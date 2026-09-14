<?php

// Author by Lab | zefry
return [
    'enabled' => filter_var(
        env('ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),
];
