<?php

declare(strict_types=1);

// Author by Lab | zefry

$source = file_get_contents(__DIR__.'/../resources/js/pages/Pos/OperationsHub.vue');
if (! is_string($source) || $source === '') {
    fwrite(STDERR, "Unable to load OperationsHub.vue.\n");
    exit(1);
}

$required = [
    "const technicalContextOpen = ref(false);",
    "const primaryDestination = computed<Destination | null>",
    "const groupedDestinations = computed(() =>",
    "const laneCount = computed(() =>",
    "const primaryPriority = ['catalog_inventory', 'shift_start', 'cashier', 'sales_summary'];",
    ':href="primaryDestination.url"',
    ':href="destination.url"',
    'Suggested from currently delivered routes only.',
    'Guidance ranks delivered routes only;',
    'Account & security',
    'props.security.can_logout',
];

foreach ($required as $needle) {
    if (! str_contains($source, $needle)) {
        fwrite(STDERR, "Missing Sprint200 guided-home contract: {$needle}\n");
        exit(1);
    }
}

$forbidden = [
    'localStorage',
    'sessionStorage',
    "fetch('/pos",
    'window.location.href = destination',
    'permission = true',
    'can_mutate',
];

foreach ($forbidden as $needle) {
    if (str_contains($source, $needle)) {
        fwrite(STDERR, "Forbidden Sprint200 guided-home source detected: {$needle}\n");
        exit(1);
    }
}

if (substr_count($source, ':href="destination.url"') !== 1) {
    fwrite(STDERR, "Workspace navigation must continue to use exactly one destination.url binding.\n");
    exit(1);
}

if (substr_count($source, ':href="primaryDestination.url"') !== 1) {
    fwrite(STDERR, "Guided primary action must use exactly one server-delivered destination URL.\n");
    exit(1);
}

if (! str_contains($source, "new Set(props.destinations.map((destination) => destination.category)).size")) {
    fwrite(STDERR, "Business-lane summary must derive only from delivered destinations.\n");
    exit(1);
}

echo "Sprint200 merchant POS guided operations home source contract: PASS\n";
