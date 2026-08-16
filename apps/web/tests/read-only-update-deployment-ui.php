<?php

declare(strict_types=1);

// Author by Lab | zefry
$root = dirname(__DIR__);
$pagePath = $root.'/resources/js/pages/System/UpdateDeployment.vue';
$controllerPath = $root.'/app/Delivery/Http/SystemUpdate/SystemUpdatePageController.php';
$routePath = $root.'/routes/web.php';
$configPath = $root.'/config/oneqay.php';
$statusPath = $root.'/app/Application/SystemUpdate/SystemUpdateControlPlaneStatus.php';

$files = [$pagePath, $controllerPath, $routePath, $configPath, $statusPath];
foreach ($files as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "Missing updater UI contract file: {$file}\n");
        exit(1);
    }
}

$page = (string) file_get_contents($pagePath);
$controller = (string) file_get_contents($controllerPath);
$routes = (string) file_get_contents($routePath);
$config = (string) file_get_contents($configPath);
$status = (string) file_get_contents($statusPath);

$requiredPageMarkers = [
    'System',
    'Update &amp; Deployment',
    'READ_ONLY',
    'Installation controls are locked',
    'deployment_authorized',
    'activation_supported',
    'schema_change_supported',
    'Lab | zefry',
];

foreach ($requiredPageMarkers as $marker) {
    if (! str_contains($page, $marker)) {
        fwrite(STDERR, "Missing read-only UI marker: {$marker}\n");
        exit(1);
    }
}

$forbiddenInteractivePatterns = [
    'useForm(',
    'axios',
    'fetch(',
    '@click',
    '<form',
    '.post(',
    '.put(',
    '.patch(',
    '.delete(',
    '/system/update/check',
    '/system/update/install',
];

foreach ($forbiddenInteractivePatterns as $pattern) {
    if (str_contains($page, $pattern)) {
        fwrite(STDERR, "Read-only updater UI contains forbidden interactive pattern: {$pattern}\n");
        exit(1);
    }
}

if (preg_match_all('/<button\b[^>]*\bdisabled\b[^>]*>/i', $page) < 2) {
    fwrite(STDERR, "Read-only updater UI must render both action indicators as disabled buttons.\n");
    exit(1);
}

if (! str_contains($controller, "Inertia::render('System/UpdateDeployment'")) {
    fwrite(STDERR, "Updater page controller must render the governed System/UpdateDeployment page.\n");
    exit(1);
}

if (! str_contains($controller, '->status()->toSafeArray()')) {
    fwrite(STDERR, "Updater page controller must consume only the safe control-plane status DTO.\n");
    exit(1);
}

foreach (['checkAvailability(', 'requestInstall('] as $forbiddenControllerCall) {
    if (str_contains($controller, $forbiddenControllerCall)) {
        fwrite(STDERR, "Updater page controller must remain read-only: {$forbiddenControllerCall}\n");
        exit(1);
    }
}

if (! str_contains($routes, "Route::get('/system/update', SystemUpdatePageController::class)->name('system-update.page');")) {
    fwrite(STDERR, "Missing read-only updater page route.\n");
    exit(1);
}

if (! str_contains($config, "'install_enabled' => false")) {
    fwrite(STDERR, "Updater installation must remain hard-disabled in source.\n");
    exit(1);
}

if (preg_match('/ONEQAY_SYSTEM_UPDATE_INSTALL|env\([^)]*INSTALL/i', $config) === 1) {
    fwrite(STDERR, "Updater install flag must not gain an environment override.\n");
    exit(1);
}

foreach ([
    "'schema_change_supported' => false",
    "'activation_supported' => false",
    "'deployment_authorized' => false",
] as $safeBoundary) {
    if (! str_contains($status, $safeBoundary)) {
        fwrite(STDERR, "Missing safe updater boundary: {$safeBoundary}\n");
        exit(1);
    }
}

echo "Read-only Update & Deployment UI regression passed.\n";
