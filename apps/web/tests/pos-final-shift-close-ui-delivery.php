<?php

declare(strict_types=1);

// Author by Lab | zefry
$root = dirname(__DIR__);
$controller = file_get_contents($root.'/app/Delivery/Http/Pos/PosShiftClosePageController.php');
$route = file_get_contents($root.'/routes/pos-final-shift-close.php');
$page = file_get_contents($root.'/resources/js/pages/Pos/ShiftClose.vue');
$workspace = file_get_contents($root.'/app/Application/Pos/ViewShiftClosureWorkspace.php');

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint99 Final Shift Close UI regression failed: '.$case);
    }
};

$assert(is_string($controller) && is_string($route) && is_string($page), 'source readable');
$assert(str_contains($workspace, 'FinalShiftClosePermission::identifier()'), 'workspace retains canonical close permission');
$assert(str_contains($workspace, '$this->policy->require(') && str_contains($controller, '$this->workspace->view()'), 'workspace requires durable authorization');
$assert(str_contains($controller, "abort(403, 'Final Shift Close access denied.')"), 'authorization fails closed');
$assert(str_contains($controller, "Inertia::render('Pos/ShiftClose'"), 'authorized page render');
$assert(str_contains($controller, "'activation_state' => 'DORMANT_FAIL_CLOSED'"), 'page activation boundary');
$assert(str_contains($route, "Route::get('/pos/shifts/close', PosShiftClosePageController::class)"), 'GET page route');
$assert(str_contains($route, "->name('pos.shifts.close.page')"), 'GET page route name');
$assert(str_contains($route, "Route::post('/pos/shifts/close', PosShiftCloseController::class)"), 'canonical POST route preserved');
$assert(str_contains($route, "->name('pos.shifts.close')"), 'canonical POST route name preserved');
$assert(str_contains($page, "import axios, { AxiosError } from 'axios'"), 'JSON delivery uses axios');
$assert(str_contains($page, 'crypto.randomUUID()'), 'stable client operation ID seed');
$mutationPayload = 'axios.post(props.delivery.post_url,{operation_id:closeId.value},{headers:';
$assert(str_contains($page, $mutationPayload), 'mutation payload contains exactly operation ID');
$assert(! str_contains($page, 'reviewer_actor_identity_id'), 'UI cannot submit reviewer identity');
$assert(! str_contains($page, 'closer_actor_identity_id'), 'UI cannot submit closer identity');
$assert(str_contains($page, 'closed.value'), 'successful close disables second UI mutation');
$assert(str_contains($controller, "'activation_state' => 'DORMANT_FAIL_CLOSED'"), 'dormant delivery metadata preserved');
$assert(str_contains($controller, "'production_ready' => false"), 'production authority not granted by UI');

echo "Sprint99 Final Shift Close UI delivery regression passed.\n";
