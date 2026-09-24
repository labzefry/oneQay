<?php

declare(strict_types=1);

// Author by Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint250 regression failed: '.$message);
    }
};

$workflowPath = __DIR__.'/../../../.github/workflows/final-shift-close-feature-activation.yml';
$statePath = __DIR__.'/../../../ops/final-shift-close/STATE.json';

$workflow = (string) file_get_contents($workflowPath);
$state = json_decode((string) file_get_contents($statePath), true, 64, JSON_THROW_ON_ERROR);

$appendNeedle = <<<'NEEDLE'
jq -c '.workflow_runs[]' <<<"$page_json" >> "$runs_file"
NEEDLE;
$resolverNeedle = <<<'NEEDLE'
jq -rs --arg name "$workflow" '[.[] | select(.name == $name)][0].conclusion // "missing"]' "$runs_file"
NEEDLE;

$assert(str_contains($workflow, 'runs_file="$(mktemp)"'), 'activation workflow does not aggregate exact-head runs across pages');
$assert(str_contains($workflow, 'per_page=100&page=$page'), 'activation workflow does not request explicit workflow-run pages');
$assert(str_contains($workflow, 'page=$((page + 1))'), 'activation workflow does not advance the pagination cursor');
$assert(str_contains($workflow, '(( page <= 20 ))'), 'activation workflow pagination is not bounded fail-closed');
$assert(str_contains($workflow, $appendNeedle), 'activation workflow does not append page runs to the bounded aggregate');
$assert(str_contains($workflow, "jq -rs --arg name \"\$workflow\" '[.[] | select(.name == \$name)][0].conclusion // \"missing\"' \"\$runs_file\""), 'activation workflow does not resolve required workflow conclusions from the full aggregate');
$assert(! str_contains($workflow, 'actions/runs?head_sha=$TARGET_HEAD&event=pull_request&per_page=100")'), 'legacy first-page-only exact-head workflow query remains');

$required = ['Governance Required Checks', 'PHP Foundation Regression', 'M7.1 Application Regression'];
$page1 = [];
for ($i = 0; $i < 100; $i++) {
    $page1[] = ['name' => 'Historical Regression '.$i, 'conclusion' => 'failure'];
}
$page1[0] = ['name' => 'Governance Required Checks', 'conclusion' => 'success'];
$page1[1] = ['name' => 'PHP Foundation Regression', 'conclusion' => 'success'];
$page2 = [
    ['name' => 'M7.1 Application Regression', 'conclusion' => 'success'],
];

$aggregate = array_merge($page1, $page2);
$resolve = static function (array $runs, string $name): string {
    foreach ($runs as $run) {
        if (($run['name'] ?? null) === $name) {
            return (string) ($run['conclusion'] ?? 'missing');
        }
    }
    return 'missing';
};

foreach ($required as $name) {
    $assert($resolve($aggregate, $name) === 'success', 'paginated fixture failed to resolve '.$name);
}
$assert($resolve($page1, 'M7.1 Application Regression') === 'missing', 'fixture no longer reproduces the first-page-only failure');

$newestFirst = [
    ['name' => 'M7.1 Application Regression', 'conclusion' => 'success'],
    ['name' => 'M7.1 Application Regression', 'conclusion' => 'failure'],
];
$assert($resolve($newestFirst, 'M7.1 Application Regression') === 'success', 'newest-first run resolution drifted');

$assert(($state['migration27']['state'] ?? null) === 'EXECUTED', 'migration #27 state drifted');
$assert(($state['permission_provisioning']['state'] ?? null) === 'PROVISIONED', 'permission provisioning state drifted');
$assert(($state['permission_provisioning']['default_grant'] ?? null) === 'NONE', 'permission default grant drifted');
$assert(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'engineering correction activated Final Shift Close');
$assert(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'deployment authority drifted');
$assert(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Technical Preview authority drifted');
$assert(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Production authority drifted');
$assert(($state['updater_activation'] ?? null) === 'INACTIVE', 'updater state drifted');

fwrite(STDOUT, "Sprint250 Final Shift Close activation workflow pagination regression passed.\n");
