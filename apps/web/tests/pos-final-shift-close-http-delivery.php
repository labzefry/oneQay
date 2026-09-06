<?php

declare(strict_types=1);

// Author by Lab | zefry

$root = dirname(__DIR__);
$controller = file_get_contents($root.'/app/Delivery/Http/Pos/PosShiftCloseController.php');
$provider = file_get_contents($root.'/app/Providers/FinalShiftCloseServiceProvider.php');
$route = file_get_contents($root.'/routes/pos-final-shift-close.php');

if ($controller === false || $provider === false || $route === false) {
    fwrite(STDERR, "Sprint97 HTTP delivery source is unreadable.\n");
    exit(1);
}

$controllerLocks = [
    "private const ALLOWED_FIELDS = ['operation_id'];",
    'new CloseShiftCommand($payload[\'operation_id\'])',
    "SafeErrorEnvelope::make('POS_SHIFT_CLOSE_AUTHORIZATION_DENIED'",
    "SafeErrorEnvelope::make('POS_SHIFT_CLOSE_REJECTED'",
    "'Cache-Control' => 'no-store, private'",
];

foreach ($controllerLocks as $fragment) {
    if (! str_contains($controller, $fragment)) {
        fwrite(STDERR, "Missing Sprint97 controller lock: {$fragment}\n");
        exit(1);
    }
}

$forbiddenRequestFields = [
    "'expected_cash_atomic'",
    "'observed_closing_cash_atomic'",
    "'variance_atomic'",
    "'reviewer_actor'",
    "'review_outcome'",
    "'closed_at_unix'",
    "'closer_actor'",
];

$allowedFieldDeclarationEnd = strpos($controller, 'public function __construct');
if ($allowedFieldDeclarationEnd === false) {
    fwrite(STDERR, "Sprint97 controller structure is invalid.\n");
    exit(1);
}
$inputContract = substr($controller, 0, $allowedFieldDeclarationEnd);
foreach ($forbiddenRequestFields as $field) {
    if (str_contains($inputContract, $field)) {
        fwrite(STDERR, "Sprint97 request contract accepts forbidden authoritative input: {$field}\n");
        exit(1);
    }
}

$providerLocks = [
    'public function boot(): void',
    "['local', 'test', 'ci']",
    "config('database.oneqay_persistence_enabled', false)",
    "config('oneqay.session_control.enabled', false)",
    "config('oneqay.pos_sale_completion.enabled', false)",
    "env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false)",
    "'session.active'",
    "'throttle:5,1'",
    "'throttle:50,60'",
    'RequirePosSessionContextMiddleware::class',
    "group(base_path('routes/pos-final-shift-close.php'))",
];
foreach ($providerLocks as $fragment) {
    if (! str_contains($provider, $fragment)) {
        fwrite(STDERR, "Missing Sprint97 provider delivery lock: {$fragment}\n");
        exit(1);
    }
}

if (! str_contains($route, "Route::post('/pos/shifts/close', PosShiftCloseController::class)")) {
    fwrite(STDERR, "Sprint97 close route is invalid.\n");
    exit(1);
}
if (! str_contains($route, "->name('pos.shifts.close')")) {
    fwrite(STDERR, "Sprint97 close route name is invalid.\n");
    exit(1);
}

foreach (glob($root.'/resources/**/*', GLOB_BRACE) ?: [] as $resource) {
    if (is_file($resource)) {
        $source = file_get_contents($resource);
        if ($source !== false && str_contains($source, 'pos.shifts.close')) {
            fwrite(STDERR, "Sprint97 must not add Final Shift Close UI delivery.\n");
            exit(1);
        }
    }
}

echo "Sprint97 Final Shift Close HTTP delivery regression passed.\n";
