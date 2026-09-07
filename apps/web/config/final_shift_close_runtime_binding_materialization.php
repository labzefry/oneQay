<?php

// Author by Lab | zefry
return [
    // Sprint123 registers source wiring only. Delivery remains fail-closed unless explicitly armed.
    'enabled' => filter_var(
        env('ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),

    // No token is provisioned by source. Empty token keeps route delivery fail-closed.
    'token' => env('ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN', ''),
];
