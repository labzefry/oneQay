<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Routing\Router;

// Author by Lab | zefry

require __DIR__.'/../vendor/autoload.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$environment = [
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('w', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://127.0.0.1',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_TECHNICAL_PREVIEW_ENABLED' => 'false',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_POS_SALE_VOID_ENABLED' => 'true',
    'ONEQAY_POS_SALE_CASH_REFUND_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_PREPARATION_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED' => 'true',
    'ONEQAY_POS_INVENTORY_BASELINE_ENABLED' => 'true',
    'ONEQAY_POS_OPERATIONS_HUB_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_START_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_CASHIER_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED' => 'true',
    'ONEQAY_POS_SALE_HISTORY_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_SALE_CORRECTION_WORKSPACE_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED' => 'true',
    'ONEQAY_POS_CASH_VARIANCE_RECONCILIATION_ENABLED' => 'true',
    'ONEQAY_POS_INVENTORY_REPLENISHMENT_ENABLED' => 'true',
    'ONEQAY_POS_INVENTORY_ACCOUNTABILITY_ENABLED' => 'true',
    'ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED' => 'true',
    'ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_HISTORY_PERFORMANCE_ENABLED' => 'true',
];

foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';

/** @var Kernel $console */
$console = $app->make(Kernel::class);
$console->bootstrap();

/** @var Router $router */
$router = $app->make('router');
$routes = $router->getRoutes();

$expectedRoutes = [
    'pos.operations.hub',
    'pos.catalog-inventory.setup',
    'pos.inventory.replenishment.workspace',
    'pos.inventory.replenishment.record',
    'pos.inventory.accountability.workspace',
    'pos.shift-start.workspace',
    'pos.cashier.workspace',
    'pos.reporting.sales-summary',
    'pos.reporting.sales-history',
    'pos.reporting.active-shift-performance',
    'pos.reporting.product-performance',
    'pos.sales.corrections.workspace',
];

foreach ($expectedRoutes as $routeName) {
    $route = $routes->getByName($routeName);
    $assert($route !== null, "Sprint198 missing delivered POS route {$routeName}.");

    $middleware = $route->gatherMiddleware();
    $assert(in_array('session.active', $middleware, true), "Sprint198 route {$routeName} lost active-session enforcement.");
    $assert(
        in_array(RequirePosSessionContextMiddleware::class, $middleware, true),
        "Sprint198 route {$routeName} lost verified POS session context enforcement.",
    );
}

$assert(
    $routes->getByName('pos.reporting.shift-history') === null,
    'Sprint198 must preserve the Shift History Final Shift Close prerequisite while canonical Final Shift Close remains inactive.',
);
$assert(
    $routes->getByName('pos.shifts.reconciliation.workspace') === null
        && $routes->getByName('pos.shifts.reconciliation.explanation') === null
        && $routes->getByName('pos.shifts.reconciliation.review') === null,
    'Sprint198 must preserve Cash Variance Reconciliation Final Shift Close prerequisites while canonical Final Shift Close remains inactive.',
);
$assert(
    $routes->getByName('pos.shifts.close.page') === null,
    'Sprint198 must not activate Final Shift Close delivery.',
);
$assert(
    $routes->getByName('preview.index') === null,
    'Sprint198 must not activate Technical Preview routes.',
);

$bootstrapProviders = (string) file_get_contents(__DIR__.'/../bootstrap/providers.php');
$assert(
    substr_count($bootstrapProviders, 'App\\Providers\\PosOperationsHubServiceProvider::class') === 1,
    'Sprint198 bootstrap must register exactly one POS delivery aggregate.',
);

$aggregateSource = (string) file_get_contents(__DIR__.'/../app/Providers/PosOperationsHubServiceProvider.php');
foreach ([
    'PosCatalogInventorySetupWorkspaceServiceProvider::class',
    'PosShiftStartWorkspaceServiceProvider::class',
    'PosCashierWorkspaceServiceProvider::class',
    'PosSaleCorrectionWorkspaceServiceProvider::class',
    'PosOperationalReportingServiceProvider::class',
    'PosCashVarianceReconciliationWorkspaceServiceProvider::class',
    'PosShiftHistoryPerformanceWorkspaceServiceProvider::class',
    'PosActiveShiftPerformanceWorkspaceServiceProvider::class',
    'PosProductSalesPerformanceWorkspaceServiceProvider::class',
    'PosInventoryAccountabilityWorkspaceServiceProvider::class',
    'PosInventoryReplenishmentWorkspaceServiceProvider::class',
] as $provider) {
    $assert(
        substr_count($aggregateSource, $provider) === 1,
        "Sprint198 delivery aggregate registration invalid for {$provider}.",
    );
}

$defaultOffConfigs = [
    'pos_operations_hub.php' => 'ONEQAY_POS_OPERATIONS_HUB_ENABLED',
    'pos_shift_start_workspace.php' => 'ONEQAY_POS_SHIFT_START_WORKSPACE_ENABLED',
    'pos_cashier_workspace.php' => 'ONEQAY_POS_CASHIER_WORKSPACE_ENABLED',
    'pos_operational_reporting.php' => 'ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED',
    'pos_sale_correction_workspace.php' => 'ONEQAY_POS_SALE_CORRECTION_WORKSPACE_ENABLED',
    'pos_catalog_inventory_setup.php' => 'ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED',
    'pos_cash_variance_reconciliation.php' => 'ONEQAY_POS_CASH_VARIANCE_RECONCILIATION_ENABLED',
    'pos_inventory_replenishment.php' => 'ONEQAY_POS_INVENTORY_REPLENISHMENT_ENABLED',
    'pos_inventory_accountability.php' => 'ONEQAY_POS_INVENTORY_ACCOUNTABILITY_ENABLED',
    'pos_product_sales_performance.php' => 'ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED',
    'pos_active_shift_performance.php' => 'ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED',
    'pos_shift_history_performance.php' => 'ONEQAY_POS_SHIFT_HISTORY_PERFORMANCE_ENABLED',
];

foreach ($defaultOffConfigs as $configFile => $environmentKey) {
    $source = (string) file_get_contents(__DIR__.'/../config/'.$configFile);
    $pattern = "/env\\('".preg_quote($environmentKey, '/')."',\\s*false\\)/s";
    $assert(
        preg_match($pattern, $source) === 1,
        "Sprint198 config {$configFile} no longer defaults {$environmentKey} to false.",
    );
}

$statePath = __DIR__.'/../../../ops/final-shift-close/STATE.json';
$state = json_decode((string) file_get_contents($statePath), true, 64, JSON_THROW_ON_ERROR);
$assert(($state['migration27']['state'] ?? null) === 'NOT_EXECUTED', 'Sprint198 crossed migration #27 NO-GO.');
$assert(($state['permission_provisioning']['state'] ?? null) === 'NONE', 'Sprint198 crossed permission provisioning NO-GO.');
$assert(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'Sprint198 crossed Final Shift Close activation NO-GO.');
$assert(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'Sprint198 crossed deployment NO-GO.');
$assert(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Sprint198 crossed Technical Preview NO-GO.');
$assert(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Sprint198 crossed Production NO-GO.');
$assert(($state['updater_activation'] ?? null) === 'INACTIVE', 'Sprint198 crossed updater NO-GO.');

fwrite(STDOUT, "Sprint198 POS business workspace delivery integration regression passed.\n");
