<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$workflowPath = $root.'/.github/workflows/production-operator-kit-publication.yml';
$statePath = $root.'/ops/final-shift-close/STATE.json';
$selectionPath = $root.'/ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json';
$contractPath = $root.'/ops/final-shift-close/PRODUCTION_OPERATOR_KIT_PUBLICATION_CONTRACT.json';

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, $message."\n");
        exit(1);
    }
};

$workflow = file_get_contents($workflowPath);
$state = json_decode((string) file_get_contents($statePath), true, 512, JSON_THROW_ON_ERROR);
$selection = json_decode((string) file_get_contents($selectionPath), true, 512, JSON_THROW_ON_ERROR);
$contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);

$assert(is_string($workflow), 'Sprint255 workflow source is unreadable.');
$assert(($state['migration27']['state'] ?? null) === 'EXECUTED', 'Sprint255 requires canonical migration #27 EXECUTED.');
$assert(($state['permission_provisioning']['state'] ?? null) === 'PROVISIONED', 'Sprint255 requires canonical permission PROVISIONED.');
$assert(($state['permission_provisioning']['default_grant'] ?? null) === 'NONE', 'Sprint255 must preserve default grant NONE.');
$assert(($state['feature_activation']['state'] ?? null) === 'ACTIVE', 'Sprint255 requires canonical Final Shift Close ACTIVE.');
$assert(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'Sprint255 crossed deployment authority NO-GO.');
$assert(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Sprint255 crossed Technical Preview NO-GO.');
$assert(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Sprint255 crossed Production activation NO-GO.');
$assert(($state['updater_activation'] ?? null) === 'INACTIVE', 'Sprint255 crossed updater NO-GO.');
$assert(($selection['selection_state'] ?? null) === 'SELECTED_NOT_AUTHORIZED', 'Sprint255 selected-target state mismatch.');
$assert(($selection['selected_target']['environment_id'] ?? null) === 'oneqay-durable-staging-01', 'Sprint255 durable-staging environment mismatch.');
$assert(($selection['selected_target']['runtime_class'] ?? null) === 'durable-staging', 'Sprint255 durable-staging runtime mismatch.');

$assert(($contract['contract_state'] ?? null) === 'PUBLISHES_SECRET_FREE_PRODUCTION_DARK_DEPLOYMENT_OPERATOR_KIT_NOT_DEPLOYED_NOT_ACTIVATED', 'Sprint255 tracked Production operator-kit contract state changed unexpectedly.');
$assert(($contract['authority_boundary']['kit_is_not_deployment_authority'] ?? null) === true, 'Sprint255 kit must not become deployment authority.');
$assert(($contract['authority_boundary']['separate_production_deployment_authority_required'] ?? null) === true, 'Sprint255 must preserve separate Production deployment authority.');
$assert(($contract['authority_boundary']['production_traffic_activation_allowed'] ?? null) === false, 'Sprint255 must preserve Production traffic NO-GO.');

foreach ([
    'workflow_run:',
    'workflows: ["Durable Staging Operator Artifact Publication"]',
    'actions: read',
    'SOURCE_SHA:',
    'Resolve exact-source durable-staging prerequisite',
    'Resolve exact-source Production release candidate',
    'production-release-candidate-publication.yml',
    'durable-staging-release-publication.yml',
    'Timed out waiting for exact-source Production RC publication.',
    'Bind runtime provenance without mutating tracked contract',
    'runtime_successor_binding',
    'exact_source_artifact_pair_required: true',
    'cp /tmp/production-operator-kit-contract-base.json "$contract"',
    'deployment_authority',
    'NOT_GRANTED',
    'production_traffic_activation',
    'NOT_AUTHORIZED',
] as $needle) {
    $assert(str_contains($workflow, $needle), 'Sprint255 workflow missing required successor marker: '.$needle);
}

$assert(!str_contains($workflow, "= NOT_EXECUTED\n"), 'Sprint255 workflow contains predecessor-only migration state.');
$assert(!str_contains($workflow, '10608942942'), 'Sprint255 workflow must not pin historical Production artifact ID.');
$assert(!str_contains($workflow, '10608272778'), 'Sprint255 workflow must not pin historical staging artifact ID.');
$assert(!preg_match('/^\s*php\s+tools\/execute-production-dark-deployment\.php\s+/m', $workflow), 'Sprint255 publication must not execute Production deployment.');

fwrite(STDOUT, "Sprint255 Production operator-kit exact-source successor regression passed.\n");
