<?php

declare(strict_types=1);

// Business OS integration acceptance: zero fantasy metrics, no capability escalation.
// Author by Lab | zefry.
require_once __DIR__.'/../app/Application/Pos/PosOperationalSalesSummary.php';
require_once __DIR__.'/../app/Application/BusinessIntelligence/BusinessCommandCenter.php';

use App\Application\BusinessIntelligence\BusinessCommandCenter;
use App\Application\Pos\PosOperationalSalesSummary;

$assert = static function (bool $ok, string $why): void {
    if (! $ok) {
        fwrite(STDERR, "FAIL: {$why}\n");
        exit(1);
    }
};

$restricted = BusinessCommandCenter::reporting(null, 'restricted');
$assert($restricted['status'] === 'restricted' && $restricted['counters'] === null, 'Denied data must not masquerade as zero.');
$assert($restricted['gross_totals'] === [] && $restricted['recent_sales'] === [], 'Denied data leaks.');
$unavailable = BusinessCommandCenter::reporting(null, 'unavailable');
$assert($unavailable['status'] === 'unavailable' && $unavailable['counters'] === null, 'Unavailable cannot become fabricated success.');

$summary = new PosOperationalSalesSummary(
    'tenant-a', 'org-a', 'outlet-a', 8, 2, 1,
    [['currency' => 'IDR', 'scale' => 0, 'gross_atomic' => 180000]],
    [['sale_id' => 'sale-test', 'completed_at_unix' => 1700000000,
      'total_atomic' => 25000, 'currency' => 'IDR', 'scale' => 0,
      'tender_category' => 'CASH', 'state' => 'COMPLETED']],
);
$actual = BusinessCommandCenter::reporting($summary, 'available');
$assert($actual['counters']['completed_sales'] === 8, 'Real counters must pass through unchanged.');
$assert($actual['gross_totals'][0]['gross_atomic'] === 180000, 'Real atomic currency must not be recast or invented.');
$assert($actual['recent_sales'][0]['sale_id'] === 'sale-test', 'Recent facts changed.');
$assert(! array_key_exists('net_profit', $actual), 'Profit cannot be invented from POS gross.');

$denied = BusinessCommandCenter::capabilities([]);
$assert(count($denied) === 10, 'Business capability registry unexpectedly incomplete.');
foreach ($denied as $module) {
    $assert($module['url'] === null && $module['status'] === 'not_available', 'Undelivered module was exposed.');
}
$allowed = BusinessCommandCenter::capabilities([[
    'key' => 'cashier', 'title' => 'Cashier', 'description' => 'POS',
    'url' => '/pos/cashier', 'category' => 'SELL',
]]);
$assert($allowed[0]['status'] === 'available' && $allowed[0]['url'] === '/pos/cashier', 'Existing scoped POS destination not exposed.');
$assert($allowed[1]['status'] === 'not_available', 'POS access escalated into inventory.');
$assert($allowed[5]['url'] === null, 'POS access escalated into CRM.');
$unsafe = BusinessCommandCenter::capabilities([[
    'key' => 'cashier', 'title' => 'Cashier', 'description' => 'POS',
    'url' => 'https://other.example/pos/cashier', 'category' => 'SELL',
]]);
$assert($unsafe[0]['url'] === null, 'External destination accepted.');

$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/Pos/PosOperationsHubController.php');
$assert(is_string($controller) && str_contains($controller, '$snapshot->canReporting()')
    && str_contains($controller, "in_array('sales_summary'")
    && str_contains($controller, '$this->sales->view()'), 'Reporting was not gated by permission and route.');
$hub = file_get_contents(__DIR__.'/../resources/js/pages/Pos/OperationsHub.vue');
$assert(is_string($hub) && str_contains($hub, 'Business Command Center')
    && str_contains($hub, 'not AI-generated advice')
    && str_contains($hub, 'not net revenue or profit'), 'Business OS misstates intelligence readiness.');

fwrite(STDOUT, "Business OS Command Center acceptance passed.\n");
