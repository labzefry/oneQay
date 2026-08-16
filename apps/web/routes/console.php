<?php

declare(strict_types=1);

use App\Infrastructure\Background\PreviewFilesystemQueue;
use Illuminate\Support\Facades\Artisan;

// Attribution: Lab | zefry

Artisan::command('oneqay:preview-queue:enqueue {job_id} {--scenario=noop}', function (): int {
    $jobId = (string) $this->argument('job_id');
    $scenario = (string) $this->option('scenario');
    $types = [
        'noop' => 'qualification.noop',
        'fail-once' => 'qualification.fail_once',
    ];

    if (! array_key_exists($scenario, $types)) {
        $this->error('ONEQAY_PREVIEW_QUEUE_INVALID_SCENARIO');

        return 2;
    }

    try {
        $result = PreviewFilesystemQueue::fromRuntime()->enqueue($jobId, $types[$scenario]);
        $this->line(sprintf(
            'ONEQAY_PREVIEW_QUEUE_ENQUEUE|JOB=%s|STATE=%s|CREATED=%d|ATTEMPTS=%d|SCENARIO=%s',
            $result['job_id'],
            $result['state'],
            $result['created'] ? 1 : 0,
            $result['attempts'],
            $scenario,
        ));

        return 0;
    } catch (\Throwable) {
        $this->error('ONEQAY_PREVIEW_QUEUE_ENQUEUE_FAILED');

        return 1;
    }
})->purpose('Enqueue one bounded synthetic Technical Preview qualification job.');

Artisan::command('oneqay:preview-queue:work-one', function (): int {
    $started = hrtime(true);

    try {
        $result = PreviewFilesystemQueue::fromRuntime()->workOne();
        $durationMs = (hrtime(true) - $started) / 1_000_000;
        $this->line(sprintf(
            'ONEQAY_PREVIEW_QUEUE_WORK|STATE=%s|JOB=%s|ATTEMPTS=%d|RECOVERED=%d|DURATION_MS=%.3f|ERROR_CODE=%s',
            $result['state'],
            $result['job_id'] ?? 'none',
            $result['attempts'],
            $result['recovered'],
            $durationMs,
            $result['last_error_code'] ?? 'none',
        ));

        return in_array($result['state'], ['done', 'retry', 'idle', 'busy'], true) ? 0 : 1;
    } catch (\Throwable) {
        $this->error('ONEQAY_PREVIEW_QUEUE_WORK_FAILED');

        return 1;
    }
})->purpose('Process at most one bounded synthetic Technical Preview qualification job.');

Artisan::command('oneqay:preview-queue:status {job_id}', function (): int {
    $jobId = (string) $this->argument('job_id');

    try {
        $result = PreviewFilesystemQueue::fromRuntime()->status($jobId);
        $this->line(sprintf(
            'ONEQAY_PREVIEW_QUEUE_STATUS|JOB=%s|STATE=%s|ATTEMPTS=%d|ERROR_CODE=%s',
            $result['job_id'],
            $result['state'],
            $result['attempts'],
            $result['last_error_code'] ?? 'none',
        ));

        return $result['state'] === 'conflict' ? 1 : 0;
    } catch (\Throwable) {
        $this->error('ONEQAY_PREVIEW_QUEUE_STATUS_FAILED');

        return 1;
    }
})->purpose('Report sanitized state for one bounded synthetic Technical Preview qualification job.');
