<?php

declare(strict_types=1);

// Author by Lab | zefry

final class CpanelFixedPublicBridgeException extends RuntimeException
{
}

function cpanelBridgeFail(string $code): never
{
    throw new CpanelFixedPublicBridgeException($code);
}

function cpanelBridgeRemoveTree(string $path): void
{
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        if (! @unlink($path)) {
            cpanelBridgeFail('public_bridge_remove_failed');
        }
        return;
    }
    $items = scandir($path);
    if (! is_array($items)) {
        cpanelBridgeFail('public_bridge_scan_failed');
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        cpanelBridgeRemoveTree($path.'/'.$item);
    }
    if (! @rmdir($path)) {
        cpanelBridgeFail('public_bridge_directory_remove_failed');
    }
}

function cpanelBridgeCopyTree(string $source, string $destination): void
{
    if (! is_dir($source) || is_link($source)) {
        cpanelBridgeFail('public_build_source_invalid');
    }
    if (file_exists($destination) || is_link($destination)) {
        cpanelBridgeFail('public_build_destination_occupied');
    }
    if (! mkdir($destination, 0755)) {
        cpanelBridgeFail('public_build_destination_create_failed');
    }

    $items = scandir($source);
    if (! is_array($items)) {
        cpanelBridgeFail('public_build_source_scan_failed');
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $from = $source.'/'.$item;
        $to = $destination.'/'.$item;
        if (is_link($from)) {
            cpanelBridgeFail('public_build_symlink_forbidden');
        }
        if (is_dir($from)) {
            cpanelBridgeCopyTree($from, $to);
            continue;
        }
        if (! is_file($from) || ! copy($from, $to)) {
            cpanelBridgeFail('public_build_copy_failed');
        }
        @chmod($to, 0644);
    }
}

/** @return array<string,mixed> */
function cpanelBridgeInstall(string $documentRoot, string $activePointer, string $releaseDirectory, string $privateBackupRoot, bool $bindDirectRelease = false): array
{
    if (! is_dir($documentRoot) || ! is_writable($documentRoot)) {
        cpanelBridgeFail('public_document_root_not_writable');
    }
    if (str_contains($documentRoot, "\0") || str_contains($activePointer, "\0") || str_contains($releaseDirectory, "\0")) {
        cpanelBridgeFail('public_bridge_path_invalid');
    }

    $htaccess = $documentRoot.'/.htaccess';
    if (! is_file($htaccess) || is_link($htaccess) || ! is_readable($htaccess)) {
        cpanelBridgeFail('public_htaccess_unavailable');
    }
    $rawHtaccess = file_get_contents($htaccess);
    if (! is_string($rawHtaccess)
        || stripos($rawHtaccess, 'RewriteEngine On') === false
        || preg_match('/RewriteRule\s+\^\s+index\.php\b/i', $rawHtaccess) !== 1
    ) {
        cpanelBridgeFail('public_rewrite_to_index_unverified');
    }

    $sourceBuild = $releaseDirectory.'/apps/web/public/build';
    if (! is_dir($sourceBuild) || is_link($sourceBuild)) {
        cpanelBridgeFail('public_build_source_missing');
    }

    if (! is_dir($privateBackupRoot) || ! is_writable($privateBackupRoot)
        || str_starts_with($privateBackupRoot.'/', $documentRoot.'/')
    ) {
        cpanelBridgeFail('private_public_bridge_backup_root_invalid');
    }

    $suffix = bin2hex(random_bytes(8));
    $indexPath = $documentRoot.'/index.php';
    $buildPath = $documentRoot.'/build';
    $backupDir = rtrim($privateBackupRoot, '/').'/.oneqay-public-bridge-'.$suffix;
    $indexBackup = $backupDir.'/index.php';
    $indexNext = $documentRoot.'/.oneqay-index-next-'.$suffix;
    $buildBackup = $documentRoot.'/.oneqay-build-backup-'.$suffix;
    $buildNext = $documentRoot.'/.oneqay-build-next-'.$suffix;

    if (! mkdir($backupDir, 0700)) {
        cpanelBridgeFail('private_public_bridge_backup_create_failed');
    }

    $indexExisted = file_exists($indexPath) || is_link($indexPath);
    $buildExisted = file_exists($buildPath) || is_link($buildPath);

    if ($indexExisted && (! is_file($indexPath) || is_link($indexPath))) {
        cpanelBridgeFail('public_index_existing_shape_invalid');
    }
    if ($buildExisted && (! is_dir($buildPath) || is_link($buildPath))) {
        cpanelBridgeFail('public_build_existing_shape_invalid');
    }

    $servingRoot = $bindDirectRelease ? $releaseDirectory : $activePointer;
    $appRootLiteral = var_export($servingRoot.'/apps/web', true);
    $bridge = <<<'PHP'
<?php

declare(strict_types=1);

// Author by Lab | zefry

define('LARAVEL_START', microtime(true));

$appRoot = realpath(__ONEQAY_ACTIVE_APP_ROOT__);
if (! is_string($appRoot) || ! is_dir($appRoot)) {
    http_response_code(503);
    exit;
}

if (file_exists($maintenance = $appRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appRoot.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $appRoot.'/bootstrap/app.php';

$app->handleRequest(\Illuminate\Http\Request::capture());
PHP;
    $bridge = str_replace('__ONEQAY_ACTIVE_APP_ROOT__', $appRootLiteral, $bridge)."\n";

    try {
        if (file_put_contents($indexNext, $bridge, LOCK_EX) !== strlen($bridge)) {
            cpanelBridgeFail('public_index_next_write_failed');
        }
        @chmod($indexNext, 0644);
        cpanelBridgeCopyTree($sourceBuild, $buildNext);

        if ($indexExisted && ! copy($indexPath, $indexBackup)) {
            cpanelBridgeFail('public_index_backup_failed');
        }
        if ($indexExisted) {
            @chmod($indexBackup, 0600);
        }

        if ($buildExisted && ! rename($buildPath, $buildBackup)) {
            cpanelBridgeFail('public_build_backup_failed');
        }
        if (! rename($buildNext, $buildPath)) {
            if ($buildExisted && is_dir($buildBackup)) {
                @rename($buildBackup, $buildPath);
            }
            cpanelBridgeFail('public_build_activate_failed');
        }
        if (! rename($indexNext, $indexPath)) {
            cpanelBridgeFail('public_index_activate_failed');
        }
        @chmod($indexPath, 0644);

        $readback = file_get_contents($indexPath);
        if (! is_string($readback) || ! hash_equals(hash('sha256', $bridge), hash('sha256', $readback))) {
            cpanelBridgeFail('public_index_readback_failed');
        }
        if (! is_dir($buildPath) || ! is_file($buildPath.'/manifest.json')) {
            cpanelBridgeFail('public_build_readback_failed');
        }

        return [
            'document_root' => $documentRoot,
            'index_path' => $indexPath,
            'build_path' => $buildPath,
            'backup_dir' => $backupDir,
            'index_backup' => $indexBackup,
            'build_backup' => $buildBackup,
            'index_existed' => $indexExisted,
            'build_existed' => $buildExisted,
            'bridge_sha256' => hash('sha256', $bridge),
        ];
    } catch (Throwable $failure) {
        if (is_file($indexNext)) @unlink($indexNext);
        if (is_dir($buildNext)) {
            try { cpanelBridgeRemoveTree($buildNext); } catch (Throwable) {}
        }

        try {
            if ($indexExisted && is_file($indexBackup)) {
                @copy($indexBackup, $indexPath);
                @chmod($indexPath, 0644);
            } elseif (! $indexExisted && is_file($indexPath)) {
                @unlink($indexPath);
            }

            if (is_dir($buildPath) && is_dir($buildBackup)) {
                cpanelBridgeRemoveTree($buildPath);
                @rename($buildBackup, $buildPath);
            } elseif (! $buildExisted && is_dir($buildPath)) {
                cpanelBridgeRemoveTree($buildPath);
            } elseif ($buildExisted && ! is_dir($buildPath) && is_dir($buildBackup)) {
                @rename($buildBackup, $buildPath);
            }
        } catch (Throwable) {
        }

        if (is_file($indexBackup)) @unlink($indexBackup);
        if (is_dir($backupDir)) @rmdir($backupDir);
        throw $failure;
    }
}

/** @param array<string,mixed> $state */
function cpanelBridgeRestore(array $state): void
{
    $indexPath = (string) ($state['index_path'] ?? '');
    $buildPath = (string) ($state['build_path'] ?? '');
    $indexBackup = (string) ($state['index_backup'] ?? '');
    $buildBackup = (string) ($state['build_backup'] ?? '');
    $indexExisted = ($state['index_existed'] ?? null) === true;
    $buildExisted = ($state['build_existed'] ?? null) === true;

    if ($indexPath === '' || $buildPath === '') {
        cpanelBridgeFail('public_bridge_state_invalid');
    }

    if ($indexExisted) {
        if (! is_file($indexBackup) || is_link($indexBackup)) {
            cpanelBridgeFail('public_index_backup_unavailable');
        }
        $indexRestore = dirname($indexPath).'/.oneqay-index-restore-'.bin2hex(random_bytes(8));
        try {
            if (! copy($indexBackup, $indexRestore)) {
                cpanelBridgeFail('public_index_restore_stage_failed');
            }
            @chmod($indexRestore, 0644);
            if (! rename($indexRestore, $indexPath)) {
                cpanelBridgeFail('public_index_restore_failed');
            }
        } finally {
            if (is_file($indexRestore)) @unlink($indexRestore);
        }
    } elseif ((file_exists($indexPath) || is_link($indexPath)) && ! @unlink($indexPath)) {
        cpanelBridgeFail('public_index_remove_failed');
    }

    if (is_dir($buildPath)) {
        cpanelBridgeRemoveTree($buildPath);
    } elseif (file_exists($buildPath) || is_link($buildPath)) {
        cpanelBridgeFail('public_build_current_shape_invalid');
    }

    if ($buildExisted) {
        if (! is_dir($buildBackup) || is_link($buildBackup) || ! rename($buildBackup, $buildPath)) {
            cpanelBridgeFail('public_build_restore_failed');
        }
    }
}

/** @param array<string,mixed> $state */
function cpanelBridgeFinalize(array $state): void
{
    $backupDir = (string) ($state['backup_dir'] ?? '');
    $indexBackup = (string) ($state['index_backup'] ?? '');
    $buildBackup = (string) ($state['build_backup'] ?? '');
    if ($indexBackup !== '' && is_file($indexBackup)) {
        @unlink($indexBackup);
    }
    if ($backupDir !== '' && is_dir($backupDir)) {
        @rmdir($backupDir);
    }
    if ($buildBackup !== '' && is_dir($buildBackup)) {
        cpanelBridgeRemoveTree($buildBackup);
    }
}
