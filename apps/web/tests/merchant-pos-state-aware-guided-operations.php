<?php

declare(strict_types=1);

// Author by Lab | zefry

use App\Application\Pos\PosMerchantOperationsReadinessSnapshot;
require_once __DIR__.'/../app/Application/Pos/PosMerchantOperationsReadinessSnapshot.php';

$ready = new PosMerchantOperationsReadinessSnapshot(
    PosMerchantOperationsReadinessSnapshot::STATE_CASHIER_READY,
    'cashier',
    12,
    8,
    true,
    true,
);

if ($ready->state() !== 'cashier_ready'
    || $ready->recommendedKey() !== 'cashier'
    || $ready->catalogItemCount() !== 12
    || $ready->sellableItemCount() !== 8
    || $ready->shiftActive() !== true
    || $ready->openingCashReady() !== true) {
    fwrite(STDERR, "Sprint201 readiness snapshot did not preserve valid state.\n");
    exit(1);
}

try {
    new PosMerchantOperationsReadinessSnapshot(
        PosMerchantOperationsReadinessSnapshot::STATE_GUIDANCE_UNAVAILABLE,
        'cashier',
        null,
        null,
        null,
        null,
    );
    fwrite(STDERR, "Unavailable guidance accepted a recommendation.\n");
    exit(1);
} catch (\InvalidArgumentException) {
}

$service = file_get_contents(__DIR__.'/../app/Application/Pos/ViewPosMerchantOperationsReadiness.php');
$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/Pos/PosOperationsHubController.php');
$provider = file_get_contents(__DIR__.'/../app/Providers/PosOperationsHubServiceProvider.php');
$vue = file_get_contents(__DIR__.'/../resources/js/pages/Pos/OperationsHub.vue');

foreach ([
    'service' => $service,
    'controller' => $controller,
    'provider' => $provider,
    'vue' => $vue,
] as $label => $source) {
    if (! is_string($source) || $source === '') {
        fwrite(STDERR, "Unable to load Sprint201 {$label} source.\n");
        exit(1);
    }
}

$serviceRequired = [
    "isset(\$delivered['catalog_inventory'])",
    "isset(\$delivered['shift_start'])",
    "isset(\$delivered['cashier'])",
    "isset(\$delivered['sales_summary'])",
    'catch (PosTransactionViolation)',
    'STATE_GUIDANCE_UNAVAILABLE',
    'STATE_SETUP_REQUIRED',
    'STATE_SHIFT_REQUIRED',
    'STATE_CASHIER_READY',
    'STATE_REVIEW_AVAILABLE',
];
foreach ($serviceRequired as $needle) {
    if (! str_contains($service, $needle)) {
        fwrite(STDERR, "Missing Sprint201 readiness service contract: {$needle}\n");
        exit(1);
    }
}

$controllerRequired = [
    'ViewPosMerchantOperationsReadiness',
    "'readiness' => [",
    "'recommended_key' => \$readiness->recommendedKey()",
    "static fn (array \$destination): string => \$destination['key']",
];
foreach ($controllerRequired as $needle) {
    if (! str_contains($controller, $needle)) {
        fwrite(STDERR, "Missing Sprint201 controller contract: {$needle}\n");
        exit(1);
    }
}

$providerRequired = [
    'ViewPosMerchantOperationsReadiness::class',
    'ViewPosCatalogInventorySetupWorkspace::class',
    'ViewPosShiftStartWorkspace::class',
    'ViewPosCashierWorkspace::class',
];
foreach ($providerRequired as $needle) {
    if (! str_contains($provider, $needle)) {
        fwrite(STDERR, "Missing Sprint201 provider contract: {$needle}\n");
        exit(1);
    }
}

$vueRequired = [
    'type MerchantOperationsReadiness',
    'props.readiness.recommended_key === null',
    'candidate.key === props.readiness.recommended_key',
    'Operating readiness',
    'No guarded next action is being recommended.',
    'State-aware guidance is read-only',
];
foreach ($vueRequired as $needle) {
    if (! str_contains($vue, $needle)) {
        fwrite(STDERR, "Missing Sprint201 guided UX contract: {$needle}\n");
        exit(1);
    }
}

foreach (['localStorage', 'sessionStorage', 'permission = true', 'can_mutate'] as $needle) {
    if (str_contains($service.$controller.$vue, $needle)) {
        fwrite(STDERR, "Forbidden Sprint201 source detected: {$needle}\n");
        exit(1);
    }
}

if (str_contains($vue, "props.destinations[0] ?? null")
    || str_contains($vue, "primaryPriority")) {
    fwrite(STDERR, "Sprint201 retained route-order fallback guidance instead of state-aware recommendation.\n");
    exit(1);
}

echo "Sprint201 merchant POS state-aware guided operations regression passed.\n";


// Sprint203 bounded durable staging merchant-core bridge preservation.
$sprint203Required = [
    'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    'DurableStagingMerchantCoreBridge',
    'DurableStagingMerchantCoreRequestBridge',
    'DurableStagingMerchantContextBootstrapBridgeCommand',
    'oneqay:merchant-context:bootstrap-staging',
    "config(['oneqay.runtime_class' => 'ci'])",
    "'GET /'",
    "'POST /auth/login'",
    "'GET /pos'",
    "'POST /pos/catalog/preparation'",
    "'POST /pos/inventory/baseline'",
    "'POST /pos/shifts/open'",
    "'POST /pos/shifts/opening-cash'",
    "'POST /pos/sales'",
    'merchantEntryContextComplete()',
    'stagingCoreDeliveryEnabled',
];

foreach ($sprint203Required as $needle) {
    if (! str_contains($provider, $needle)) {
        fwrite(STDERR, "Missing Sprint203 durable staging bridge contract: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "'pos.sales.void'",
    "'pos.sales.cash-refund'",
    "'pos.shifts.closing-cash'",
    "'pos.shifts.close'",
    "['local', 'test', 'ci', 'staging']",
    "['local', 'test', 'ci', 'production']",
] as $forbidden) {
    if (str_contains($provider, $forbidden)) {
        fwrite(STDERR, "Forbidden Sprint203 staging bridge expansion detected: {$forbidden}\n");
        exit(1);
    }
}

$historicalRuntimeGuards = [
    'Infrastructure/Persistence/LaravelPersistenceTransaction.php',
    'Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php',
    'Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php',
    'Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php',
    'Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php',
    'Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
    'Infrastructure/Pos/LaravelCatalogPreparationRepository.php',
    'Infrastructure/Pos/LaravelInventoryBaselineRepository.php',
    'Infrastructure/Pos/LaravelShiftOpeningRepository.php',
    'Infrastructure/Pos/LaravelShiftOpeningCashRepository.php',
    'Infrastructure/Pos/LaravelPosCatalogInventorySetupWorkspaceRepository.php',
    'Infrastructure/Pos/LaravelPosShiftStartWorkspaceRepository.php',
    'Infrastructure/Pos/LaravelPosCashierWorkspaceRepository.php',
];

foreach ($historicalRuntimeGuards as $relative) {
    $source = file_get_contents(__DIR__.'/../app/'.$relative);
    if (! is_string($source)
        || ! str_contains($source, "['local', 'test', 'ci']")
        || str_contains($source, "['local', 'test', 'ci', 'staging']")) {
        fwrite(STDERR, "Sprint203 altered historical runtime guard unexpectedly: {$relative}\n");
        exit(1);
    }
}

echo "Sprint203 bounded durable staging merchant core bridge regression passed.\n";


// Sprint204 durable staging readiness attestation delivery.
$sprint204Required = [
    "QUALIFIABLE_RUNTIME_CLASS = 'durable-staging'",
    'readinessEndpointArmedFor',
    '/internal/oneqay/durable-runtime/readiness',
    'DurableStagingRuntimeReadinessAttestationController',
    'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
    'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
    'ONEQAY_RUNNING_SOURCE_COMMIT',
    'ONEQAY_RUNNING_ARTIFACT_SHA256',
    'ONEQAY_DURABLE_STAGING_AUTHENTICATED_CONFIGURATION_CHANNEL',
    'ONEQAY_DURABLE_STAGING_READ_BEFORE_WRITE_READ_AFTER',
    'ONEQAY_DURABLE_STAGING_VERIFIED_FLAG_ROLLBACK',
    "'non_mutating_health_attestation_supported' => true",
    "'activation_authority_binding' => self::ACTIVATION_AUTHORITY_BINDING",
    "'secrets_embedded' => false",
    "hash_equals(\$expectedToken, \$providedToken)",
    "'Cache-Control' => 'no-store, private'",
];

foreach ($sprint204Required as $needle) {
    if (! str_contains($provider, $needle)) {
        fwrite(STDERR, "Missing Sprint204 readiness attestation contract: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "'runtime_class' => 'staging'",
    "'runtime_class' => 'production'",
    'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN' . "' =>",
] as $forbidden) {
    if (str_contains($provider, $forbidden)) {
        fwrite(STDERR, "Forbidden Sprint204 readiness attestation source detected: {$forbidden}\n");
        exit(1);
    }
}

echo "Sprint204 durable staging readiness attestation delivery regression passed.\n";
