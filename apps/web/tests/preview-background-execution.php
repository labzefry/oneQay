<?php

declare(strict_types=1);

use App\Infrastructure\Background\PreviewFilesystemQueue;

require __DIR__.'/../vendor/autoload.php';

// Attribution: Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$expectException = static function (callable $callback, string $class, string $message) use ($assert): void {
    try {
        $callback();
        $assert(false, $message);
    } catch (\Throwable $exception) {
        $assert($exception instanceof $class, $message.' (unexpected exception class)');
    }
};

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $removeTree($path.'/'.$entry);
    }
    @rmdir($path);
};

$root = sys_get_temp_dir().'/oneqay-preview-background-'.bin2hex(random_bytes(8));
$public = $root.'/public';
$private = $root.'/private/queue';
mkdir($public, 0750, true);

$secret = 'M75_BG_SECRET_SHOULD_NOT_APPEAR_91e3';
putenv('APP_KEY='.$secret);
$_ENV['APP_KEY'] = $secret;
$_SERVER['APP_KEY'] = $secret;

try {
    $policy = PreviewFilesystemQueue::policy();
    $assert($policy['runtime_class'] === 'preview', 'queue must be Preview-only');
    $assert($policy['storage'] === 'private-filesystem', 'queue must use private filesystem storage');
    $assert($policy['max_jobs_per_invocation'] === 1, 'worker must process at most one job per invocation');
    $assert($policy['max_attempts'] === 3, 'retry count must be bounded');
    $assert($policy['arbitrary_payload_allowed'] === false, 'arbitrary queue payloads must be forbidden');
    $assert($policy['concurrent_workers_allowed'] === false, 'concurrent workers must be denied');
    $assert($policy['orphan_recovery'] === true, 'orphaned processing jobs must be recoverable');

    $expectException(
        fn () => new PreviewFilesystemQueue($public.'/queue', 'preview', $public),
        \RuntimeException::class,
        'queue path under public root must fail closed',
    );

    $deniedPath = $root.'/private/denied';
    $denied = new PreviewFilesystemQueue($deniedPath, 'production', $public);
    $expectException(
        fn () => $denied->enqueue('M75-BG-Denied_0001'),
        \RuntimeException::class,
        'non-Preview runtime must deny enqueue',
    );
    $assert(! is_dir($deniedPath), 'denied runtime must not create queue storage');

    $queue = new PreviewFilesystemQueue($private, 'preview', $public);

    $expectException(
        fn () => $queue->enqueue('bad!'),
        \InvalidArgumentException::class,
        'unsafe job id must be rejected',
    );
    $expectException(
        fn () => $queue->enqueue('M75-BG-Type_0001', 'business.order'),
        \InvalidArgumentException::class,
        'non-qualification job type must be rejected',
    );

    $created = $queue->enqueue('M75-BG-Noop_0001');
    $assert($created['state'] === 'pending' && $created['created'] === true, 'noop job must enqueue once');
    $duplicate = $queue->enqueue('M75-BG-Noop_0001');
    $assert($duplicate['state'] === 'pending' && $duplicate['created'] === false, 'duplicate enqueue must be idempotent');

    $done = $queue->workOne();
    $assert($done['state'] === 'done', 'noop job must complete');
    $assert($done['attempts'] === 1, 'noop job must complete in one attempt');
    $assert($queue->status('M75-BG-Noop_0001')['state'] === 'done', 'completed job must remain terminal');
    $assert($queue->enqueue('M75-BG-Noop_0001')['created'] === false, 'terminal job must not be enqueued twice');
    $assert($queue->workOne()['state'] === 'idle', 'worker must become idle after pending work is exhausted');

    $queue->enqueue('M75-BG-Lock_0001');
    $lockHandle = fopen($queue->workerLockPath(), 'c+');
    $assert($lockHandle !== false, 'worker lock must be openable');
    $assert(flock($lockHandle, LOCK_EX | LOCK_NB), 'test must acquire worker lock');
    $busy = $queue->workOne();
    $assert($busy['state'] === 'busy', 'second worker must fail closed as busy');
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    $assert($queue->workOne()['state'] === 'done', 'job must process after worker lock is released');

    $queue->enqueue('M75-BG-Retry_0001', 'qualification.fail_once');
    $retry = $queue->workOne();
    $assert($retry['state'] === 'retry' && $retry['attempts'] === 1, 'fail-once job must enter bounded retry');
    $retryStatus = $queue->status('M75-BG-Retry_0001');
    $assert($retryStatus['state'] === 'pending' && $retryStatus['attempts'] === 1, 'retry job must return to pending with attempt count');
    $retryDone = $queue->workOne();
    $assert($retryDone['state'] === 'done' && $retryDone['attempts'] === 2, 'fail-once job must succeed on second attempt');

    $queue->enqueue('M75-BG-Dead_0001', 'qualification.fail_always');
    $assert($queue->workOne()['state'] === 'retry', 'always-fail job attempt one must retry');
    $assert($queue->workOne()['state'] === 'retry', 'always-fail job attempt two must retry');
    $dead = $queue->workOne();
    $assert($dead['state'] === 'dead' && $dead['attempts'] === 3, 'always-fail job must dead-letter at bounded max attempts');

    $queue->enqueue('M75-BG-Recover_0001');
    $pendingRecover = $queue->basePath().'/pending/M75-BG-Recover_0001.json';
    $processingRecover = $queue->basePath().'/processing/M75-BG-Recover_0001.json';
    $assert(rename($pendingRecover, $processingRecover), 'test must simulate interrupted worker claim');
    $recovered = $queue->workOne();
    $assert($recovered['state'] === 'done', 'orphaned processing job must be recovered and completed');
    $assert($recovered['recovered'] === 1, 'worker must report one recovered orphan');

    file_put_contents($queue->basePath().'/pending/M75-BG-Bad_0001.json', "{not-json\n");
    $malformed = $queue->workOne();
    $assert($malformed['state'] === 'dead', 'malformed queue record must fail closed to dead state');
    $assert($malformed['last_error_code'] === 'ONEQAY_PREVIEW_QUEUE_MALFORMED_JOB', 'malformed record must emit bounded error code only');

    $queue->enqueue('M75-BG-One_0001');
    $queue->enqueue('M75-BG-Two_0001');
    $firstOneShot = $queue->workOne();
    $statesAfterOne = [
        $queue->status('M75-BG-One_0001')['state'],
        $queue->status('M75-BG-Two_0001')['state'],
    ];
    sort($statesAfterOne);
    $assert($firstOneShot['state'] === 'done', 'one-shot worker must complete one available job');
    $assert($statesAfterOne === ['done', 'pending'], 'one invocation must leave the second job pending');
    $assert($queue->workOne()['state'] === 'done', 'second invocation must complete the remaining job');

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($queue->basePath()));
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $mode = fileperms($file->getPathname());
        $assert($mode !== false && ($mode & 0002) === 0, 'queue files must not be world-writable');

        if ($file->getExtension() !== 'json') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        $assert(! str_contains($content, $secret), 'queue state must never copy APP_KEY');
        $decoded = json_decode($content, true);
        $assert(is_array($decoded), 'terminal and pending queue state must remain valid JSON');
        $assert(! array_key_exists('payload', $decoded), 'queue state must not contain arbitrary payload');
        $assert(! array_key_exists('exception_message', $decoded), 'queue state must not persist exception messages');
    }

    fwrite(STDOUT, "M7.5 Preview background execution regression passed.\n");
} finally {
    putenv('APP_KEY');
    unset($_ENV['APP_KEY'], $_SERVER['APP_KEY']);
    $removeTree($root);
}
