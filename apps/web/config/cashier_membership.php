<?php
// Author by Lab | zefry. Opt in after qualification of the cashier member schema.
return [
    'enabled' => filter_var(env('ONEQAY_CASHIER_MEMBERSHIP_ENABLED', false), FILTER_VALIDATE_BOOL),
];
