<?php

declare(strict_types=1);

require __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableStagingDeliveryGate.php';

use App\Application\Pos\FinalShiftCloseDurableStagingDeliveryGate;

// Author by Lab | zefry

function expectTrue(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, $message.PHP_EOL);
        exit(1);
    }
}

$source = str_repeat('a', 40);
$artifact = str_repeat('b', 64);
$release = [
    'payload_metadata_version' => 1,
    'product' => 'oneQay',
    'release_id' => 'durable-staging-'.substr($source, 0, 12),
    'environment' => 'DURABLE_STAGING',
    'required_runtime_class' => 'durable-staging',
    'production' => false,
    'synthetic_fixture_runtime' => false,
    'production_data_allowed' => false,
    'source_commit' => $source,
];

$gate = new FinalShiftCloseDurableStagingDeliveryGate();

expectTrue(
    $gate->allows($release, 'durable-staging', $source, $artifact, true, true, true, true),
    'Qualified durable-staging delivery must be allowed.',
);

foreach ([
    ['preview', $source, $artifact, true, true, true, true, 'preview runtime'],
    ['production', $source, $artifact, true, true, true, true, 'production runtime'],
    ['durable-staging', str_repeat('c', 40), $artifact, true, true, true, true, 'source mismatch'],
    ['durable-staging', $source, 'bad', true, true, true, true, 'artifact identity'],
    ['durable-staging', $source, $artifact, false, true, true, true, 'persistence disabled'],
    ['durable-staging', $source, $artifact, true, false, true, true, 'session control disabled'],
    ['durable-staging', $source, $artifact, true, true, false, true, 'sale completion disabled'],
    ['durable-staging', $source, $artifact, true, true, true, false, 'feature flag disabled'],
] as [$runtime, $runningSource, $runningArtifact, $persistence, $session, $sale, $feature, $label]) {
    expectTrue(
        ! $gate->allows($release, $runtime, $runningSource, $runningArtifact, $persistence, $session, $sale, $feature),
        "Gate must fail closed for {$label}.",
    );
}

$badRelease = $release;
$badRelease['production'] = true;
expectTrue(! $gate->allows($badRelease, 'durable-staging', $source, $artifact, true, true, true, true), 'Production-marked release must fail closed.');

$badRelease = $release;
$badRelease['synthetic_fixture_runtime'] = true;
expectTrue(! $gate->allows($badRelease, 'durable-staging', $source, $artifact, true, true, true, true), 'Synthetic runtime release must fail closed.');

$badRelease = $release;
$badRelease['release_id'] = 'durable-staging-'.str_repeat('f', 12);
expectTrue(! $gate->allows($badRelease, 'durable-staging', $source, $artifact, true, true, true, true), 'Release/source identity mismatch must fail closed.');

$repo = dirname(__DIR__, 3);
$provider = file_get_contents(__DIR__.'/../app/Providers/FinalShiftCloseDurableStagingDeliveryServiceProvider.php');
$aggregate = file_get_contents(__DIR__.'/../app/Providers/PosOperationsHubServiceProvider.php');
$legacyProvider = file_get_contents(__DIR__.'/../app/Providers/FinalShiftCloseServiceProvider.php');
$bootstrap = file_get_contents(__DIR__.'/../bootstrap/app.php');
$state = json_decode((string) file_get_contents($repo.'/ops/final-shift-close/STATE.json'), true, 32, JSON_THROW_ON_ERROR);
$selection = json_decode((string) file_get_contents($repo.'/ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json'), true, 32, JSON_THROW_ON_ERROR);

expectTrue(is_string($provider), 'Durable-staging delivery provider source is required.');
expectTrue(is_string($aggregate), 'POS aggregate provider source is required.');
expectTrue(str_contains($provider, 'DurableStagingMerchantCoreBridge::armedFor($runtimeClass)'), 'Provider must require the existing durable-staging bridge to be armed.');
expectTrue(str_contains($provider, "dirname(base_path(), 2).DIRECTORY_SEPARATOR.'RELEASE.json'"), 'Provider must bind delivery to packaged RELEASE.json.');
expectTrue(str_contains($provider, 'is_link($path)'), 'Provider must reject symlinked release metadata.');
expectTrue(str_contains($provider, 'RELEASE_METADATA_MAX_BYTES'), 'Provider must bound release metadata size.');
expectTrue(str_contains($provider, "env('ONEQAY_RUNNING_SOURCE_COMMIT', '')"), 'Provider must bind running source identity.');
expectTrue(str_contains($provider, "env('ONEQAY_RUNNING_ARTIFACT_SHA256', '')"), 'Provider must require running artifact identity.');
expectTrue(str_contains($provider, "env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false)"), 'Provider must retain explicit feature activation flag.');
expectTrue(str_contains($provider, "'session.active'"), 'Provider must retain active-session middleware.');
expectTrue(str_contains($provider, 'RequirePosSessionContextMiddleware::class'), 'Provider must retain POS session context middleware.');
expectTrue(str_contains($aggregate, '$this->app->register(FinalShiftCloseDurableStagingDeliveryServiceProvider::class);'), 'Durable-staging provider must be registered through the existing POS aggregate.');
expectTrue(str_contains($aggregate, "'GET /pos/shifts/close'"), 'Durable-staging compatibility bridge must allow the Final Shift Close page request.');
expectTrue(str_contains($aggregate, "'POST /pos/shifts/close'"), 'Durable-staging compatibility bridge must allow the Final Shift Close mutation request.');
expectTrue(! str_contains((string) $bootstrap, 'FinalShiftCloseDurableStagingDeliveryServiceProvider'), 'Historical bootstrap boundary must remain unchanged.');
expectTrue(str_contains((string) $legacyProvider, "['local', 'test', 'ci']"), 'Historical local/test/CI delivery boundary must remain intact.');
expectTrue(! str_contains((string) $legacyProvider, "'durable-staging'"), 'Historical provider must not be widened directly.');

expectTrue(($state['migration27']['state'] ?? null) === 'EXECUTED', 'Migration #27 canonical state must remain EXECUTED.');
expectTrue(($state['permission_provisioning']['state'] ?? null) === 'PROVISIONED', 'Permission canonical state must remain PROVISIONED.');
expectTrue(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'Feature must remain INACTIVE during Sprint240 engineering.');
expectTrue(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'Deployment authority must remain NOT_GRANTED.');
expectTrue(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Technical Preview must remain NOT_AUTHORIZED.');
expectTrue(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Production must remain NOT_AUTHORIZED.');
expectTrue(($state['updater_activation'] ?? null) === 'INACTIVE', 'Updater must remain INACTIVE.');

expectTrue(($selection['selection_state'] ?? null) === 'SELECTED_NOT_AUTHORIZED', 'Selected target state must remain SELECTED_NOT_AUTHORIZED.');
expectTrue(($selection['selected_target']['environment_id'] ?? null) === 'oneqay-durable-staging-01', 'Selected environment must remain unchanged.');
expectTrue(($selection['selected_target']['runtime_class'] ?? null) === 'durable-staging', 'Selected runtime class must remain durable-staging.');

echo "Sprint240 durable-staging delivery gate regression passed.\n";
