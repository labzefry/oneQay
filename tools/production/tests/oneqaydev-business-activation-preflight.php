<?php
declare(strict_types=1);
// Author by Lab | zefry. Source-only negative tests; no real host/DB/traffic.
require_once dirname(__DIR__).'/cpanel/inspect-oneqaydev-business-activation.php';

use RuntimeException as Stop;
$checks = 0;
function check(bool $ok, string $name): void {
    global $checks;
    ++$checks;
    if (!$ok) { fwrite(STDERR, 'FAIL:'.$name."\n"); exit(1); }
}

$env = OneQayDevBusinessPreflight::readEnv("APP_ENV=production\nONEQAY_PERSISTENCE_ENABLED='true'\nONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED=false\n");
check($env['APP_ENV'] === 'production', 'env parsing');
check($env['ONEQAY_PERSISTENCE_ENABLED'] === 'true', 'quoted env');
check(OneQayDevBusinessPreflight::truthy('TrUe'), 'truth');
check(!OneQayDevBusinessPreflight::truthy('false'), 'false');
check(!OneQayDevBusinessPreflight::truthy(''), 'missing is false');
try {
    OneQayDevBusinessPreflight::readEnv("APP_ENV=production\nAPP_ENV=ci\n");
    check(false, 'duplicate must fail');
} catch (Stop $e) {
    check($e->getMessage() === 'DUPLICATE_ENV_KEY', 'duplicate code');
}
$missing = OneQayDevBusinessPreflight::featureReadiness([]);
check($missing['blocked'] && count($missing['missing_features']) >= 9, 'missing flags deny');
$flags = array_fill_keys($missing['missing_features'], 'true');
$ready = OneQayDevBusinessPreflight::featureReadiness($flags);
check(!$ready['blocked'] && !$ready['missing_features'], 'explicit features ready');
$flags['ONEQAY_POS_SALE_COMPLETION_ENABLED'] = 'false';
check(OneQayDevBusinessPreflight::featureReadiness($flags)['blocked'], 'cash feature off denies');
check(OneQayDevBusinessPreflight::run(['inspector','activate']) === 64, 'activate command unavailable');

$script = file_get_contents(dirname(__DIR__).'/cpanel/inspect-oneqaydev-business-activation.php');
check(is_string($script), 'source readable');
foreach ([
    'HEALTH_BRIDGE_SHA256', 'APPLIED_PRIVATE_EVIDENCE', 'PREACTIVATION_BLOCKED',
    'READ_ONLY_DATABASE_ATTESTATION_FAILED', 'BUSINESS_FLAG_PREMATURELY_ENABLED',
    'UPGRADE_EVIDENCE_INVALID', 'READ_ONLY_SELECT_PASS', 'NOT_EXECUTED',
] as $guard) {
    check(str_contains($script, $guard), 'guard '.$guard);
}
check(!preg_match('/\b(?:INSERT|UPDATE|DELETE|TRUNCATE|ALTER|DROP)\s+(?:INTO|FROM|TABLE)\b/i', $script),
    'no mutation SQL');
check(!str_contains($script, "file_put_contents(") && !str_contains($script, "rename(")
    && !str_contains($script, "symlink(") && !preg_match('/\b(?:exec|shell_exec|system|proc_open|passthru)\s*\(/', $script),
    'inspector cannot mutate runtime or spawn commands');

echo 'oneqaydev_business_activation_readonly_preflight_pass:'.$checks."\n";
