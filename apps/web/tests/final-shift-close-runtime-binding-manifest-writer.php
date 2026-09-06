<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Infrastructure\Pos\FilesystemFinalShiftCloseRuntimeBindingManifestWriter;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Final Shift Close runtime binding manifest writer regression failed: {$case}");
    }
};

$expectFailure = static function (callable $attempt, string $case) use ($assert): void {
    try {
        $attempt();
        $assert(false, $case);
    } catch (InvalidArgumentException|RuntimeException) {
        $assert(true, $case);
    }
};

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        @chmod($path, 0600);
        @unlink($path);
        return;
    }
    @chmod($path, 0700);
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            $removeTree($path.DIRECTORY_SEPARATOR.$entry);
        }
    }
    @rmdir($path);
};

$payload = static fn (): array => [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'environment_id' => 'isolated-durable-stage-01',
    'runtime_class' => 'durable-stage',
    'exact_running_source_commit' => str_repeat('1', 40),
    'exact_running_artifact_sha256' => str_repeat('2', 64),
    'readiness_attestation_sha256' => str_repeat('3', 64),
    'selection_fingerprint_sha256' => str_repeat('4', 64),
    'trusted_ingestion' => [
        'run_id' => 101,
        'run_attempt' => 2,
        'ingestion_fingerprint_sha256' => str_repeat('5', 64),
    ],
    'secrets_embedded' => false,
];

$root = sys_get_temp_dir().'/oneqay-fsc-runtime-binding-writer-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);
chmod($root, 0700);

try {
    $target = $root.'/final-shift-close-runtime-binding.json';
    $manifest = new FinalShiftCloseRuntimeBindingManifest($payload());
    $writer = new FilesystemFinalShiftCloseRuntimeBindingManifestWriter($target);

    $writer->write($manifest);
    $assert(is_file($target) && ! is_link($target), 'WRITE-001 regular target written');
    $assert((fileperms($target) & 0777) === 0600, 'WRITE-002 target owner-only');
    $assert(file_get_contents($target) === $manifest->toCanonicalJson(), 'WRITE-003 canonical JSON persisted');

    $firstHash = hash_file('sha256', $target);
    $writer->write($manifest);
    $assert(hash_file('sha256', $target) === $firstHash, 'WRITE-004 deterministic idempotent rewrite');
    $assert(count(glob($target.'.tmp.*') ?: []) === 0, 'WRITE-005 no temporary files remain');
    $assert(is_file($target.'.lock') && (fileperms($target.'.lock') & 0777) === 0600, 'WRITE-006 private lock file');

    $preview = $payload();
    $preview['runtime_class'] = 'preview';
    $expectFailure(static fn () => new FinalShiftCloseRuntimeBindingManifest($preview), 'MANIFEST-001 Preview runtime rejected');

    $secretBearing = $payload();
    $secretBearing['secrets_embedded'] = true;
    $expectFailure(static fn () => new FinalShiftCloseRuntimeBindingManifest($secretBearing), 'MANIFEST-002 secret-bearing manifest rejected');

    $extra = $payload();
    $extra['unexpected'] = 'forbidden';
    $expectFailure(static fn () => new FinalShiftCloseRuntimeBindingManifest($extra), 'MANIFEST-003 extra field rejected');

    $permissive = $root.'/permissive';
    mkdir($permissive, 0755);
    chmod($permissive, 0755);
    $expectFailure(
        static fn () => (new FilesystemFinalShiftCloseRuntimeBindingManifestWriter($permissive.'/binding.json'))->write($manifest),
        'FILESYSTEM-001 permissive parent rejected',
    );

    $symlinkTarget = $root.'/symlink-target.json';
    file_put_contents($symlinkTarget, "{}\n");
    chmod($symlinkTarget, 0600);
    $symlink = $root.'/symlink.json';
    symlink($symlinkTarget, $symlink);
    $expectFailure(
        static fn () => (new FilesystemFinalShiftCloseRuntimeBindingManifestWriter($symlink))->write($manifest),
        'FILESYSTEM-002 target symlink rejected',
    );

    $relative = new FilesystemFinalShiftCloseRuntimeBindingManifestWriter('relative-binding.json');
    $expectFailure(static fn () => $relative->write($manifest), 'FILESYSTEM-003 relative path rejected');

    fwrite(STDOUT, "Final Shift Close runtime binding manifest writer regression passed.\n");
} finally {
    $removeTree($root);
}
