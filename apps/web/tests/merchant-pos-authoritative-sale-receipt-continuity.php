<?php

declare(strict_types=1);

// Author by Lab | zefry

$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/Pos/PosSaleController.php');
$cashier = file_get_contents(__DIR__.'/../resources/js/pages/Pos/Cashier.vue');

foreach (['controller' => $controller, 'cashier' => $cashier] as $label => $source) {
    if (! is_string($source) || $source === '') {
        fwrite(STDERR, "Unable to load Sprint202 {$label} source.\n");
        exit(1);
    }
}

$controllerRequired = [
    "'organization_id' => \$receipt->organizationId()",
    "'device_id' => \$receipt->deviceId()",
    "'lines' => array_map(",
    "'product_id' => \$line->productId()->value()",
    "'quantity' => \$line->quantity()",
    "'unit_price' => [",
    "'line_total' => [",
    "\$receipt->lines()",
    "['Cache-Control' => 'no-store, private']",
];

foreach ($controllerRequired as $needle) {
    if (! str_contains($controller, $needle)) {
        fwrite(STDERR, "Missing Sprint202 authoritative receipt projection: {$needle}\n");
        exit(1);
    }
}

$cashierRequired = [
    'type ReceiptLine',
    'lines: ReceiptLine[]',
    'Authoritative receipt',
    'Financial lines below come from the server-completed sale receipt',
    'receipt.lines',
    'line.line_total',
    'Print receipt',
    'Next sale',
    'window.print()',
    'Receipt correlation:',
];

foreach ($cashierRequired as $needle) {
    if (! str_contains($cashier, $needle)) {
        fwrite(STDERR, "Missing Sprint202 receipt continuity contract: {$needle}\n");
        exit(1);
    }
}

foreach (['localStorage', 'sessionStorage', 'window.open(', 'location.assign('] as $needle) {
    if (str_contains($cashier, $needle)) {
        fwrite(STDERR, "Forbidden Sprint202 cashier persistence/navigation source detected: {$needle}\n");
        exit(1);
    }
}

if (str_contains($controller, 'display_name')) {
    fwrite(STDERR, "Sprint202 controller must not invent display-name authority absent from the durable receipt.\n");
    exit(1);
}

if (! str_contains($cashier, 'catalogNames = new Map(props.catalog.map')) {
    fwrite(STDERR, "Sprint202 cashier must label authoritative receipt lines only from the already-loaded catalog snapshot.\n");
    exit(1);
}

echo "Sprint202 merchant POS authoritative sale receipt continuity regression passed.\n";
