<?php

declare(strict_types=1);

// Read-only oneQayDev Business OS host probe acceptance, without network/DB.
// Author by Lab | zefry.
require __DIR__.'/../cpanel/inspect-oneqaydev-business-os-upgrade.php';

$assert = static function (bool $valid, string $message): void {
    if (! $valid) {
        fwrite(STDERR, "FAIL: ".$message.PHP_EOL);
        exit(1);
    }
};

$root = sys_get_temp_dir().'/oneqay-business-os-host-probe-'.bin2hex(random_bytes(8));
$private = $root.'/private';
$public = $root.'/public';
$release = $private.'/releases/production-e93b7fef42cb';
$reportPath = $private.'/oneqaydev-language-update-e93b7fef42cb/update-result.json';
$entry = $public.'/oneqay-business-e93b7fef42cb-ui.php';
$routing = $public.'/.htaccess';

foreach ([$root, $private, $public, $private.'/releases', $release, dirname($reportPath)] as $directory) {
    $assert(mkdir($directory, 0700), 'Could not create temporary fixture directory.');
}

try {
    file_put_contents($entry, '<?php /* known signed public entry fixture */');
    file_put_contents($routing, 'RewriteRule ^ oneqay-business-e93b7fef42cb-ui.php [L]');
    file_put_contents($release.'/RELEASE.json', json_encode([
        'source_commit' => OneQayDevBusinessOsHostPreflight::CURRENT_SOURCE,
        'release_id' => 'production-e93b7fef42cb',
    ], JSON_THROW_ON_ERROR));
    $report = [
        'state' => 'BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING',
        'source_commit' => OneQayDevBusinessOsHostPreflight::CURRENT_SOURCE,
        'release_id' => 'production-e93b7fef42cb',
        'business_traffic' => 'ON_PRESERVED',
        'bridge_sha256' => hash_file('sha256', $entry),
        'routing_sha256' => hash_file('sha256', $routing),
    ];
    file_put_contents($reportPath, json_encode($report, JSON_THROW_ON_ERROR));
    // No database, network client, environment file or write operation is invoked by the probe.
    $probe = new OneQayDevBusinessOsHostPreflight($private, $public);
    $good = $probe->inspect();
    $assert($good['state'] === 'HOST_FILE_BINDINGS_CONSISTENT_PREDEPLOYMENT', 'Valid source-binding fixture did not qualify.');
    foreach (['database_mutated', 'merchant_mutated', 'production_traffic_mutated', 'staging_mutated', 'deployment_executed', 'live_https_verified', 'business_acceptance_verified'] as $field) {
        $assert($good[$field] === false, 'Read-only inspection claimed write/access authority.');
    }
    $assert($good['previous_source_commit'] !== $good['candidate_source_commit'], 'Probe confused current and next source.');

    file_put_contents($entry, '<?php /* tampered public entry */');
    $assert($probe->inspect()['state'] === 'STOPPED_FAIL_CLOSED_HOST_STATE_UNQUALIFIED', 'Public entry tamper was accepted.');
    file_put_contents($entry, '<?php /* known signed public entry fixture */');

    $report['source_commit'] = OneQayDevBusinessOsHostPreflight::TARGET_SOURCE;
    file_put_contents($reportPath, json_encode($report, JSON_THROW_ON_ERROR));
    $assert(! $probe->inspect()['checks']['previous_host_update_report'], 'Stale/mismatched source report accepted.');
    $report['source_commit'] = OneQayDevBusinessOsHostPreflight::CURRENT_SOURCE;
    file_put_contents($reportPath, json_encode($report, JSON_THROW_ON_ERROR));

    mkdir($private.'/releases/production-d15f2fc3f9aa', 0700);
    $assert(! $probe->inspect()['checks']['candidate_release_not_preexisting'], 'Pre-existing candidate was incorrectly considered pristine.');
    rmdir($private.'/releases/production-d15f2fc3f9aa');

    unlink($entry);
    symlink($routing, $entry);
    $assert(! $probe->inspect()['checks']['public_entry_integrity'], 'Symlink public entry was incorrectly trusted.');

    fwrite(STDOUT, "oneQay Business OS host preflight: positive, tamper, stale-source, pre-existing-release and symlink tests passed.\n");
} finally {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iterator as $item) {
        if ($item->isDir() && ! $item->isLink()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }
    rmdir($root);
}
