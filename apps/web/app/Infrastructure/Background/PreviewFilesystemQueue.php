<?php

declare(strict_types=1);

namespace App\Infrastructure\Background;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

// Attribution: Lab | zefry
final class PreviewFilesystemQueue
{
    private const SCHEMA = 'oneqay.preview_queue.job.v1';
    private const SAFE_JOB_ID = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';
    private const MAX_ATTEMPTS = 3;
    private const STATES = ['pending', 'processing', 'done', 'dead'];
    private const TYPES = [
        'qualification.noop',
        'qualification.fail_once',
        'qualification.fail_always',
    ];

    public function __construct(
        private readonly string $basePath,
        private readonly string $runtimeClass,
        private readonly string $publicPath,
    ) {
        $this->assertPrivatePathBoundary();
    }

    public static function fromRuntime(): self
    {
        return new self(
            storage_path('app/oneqay-preview-queue'),
            (string) config('oneqay.runtime_class'),
            public_path(),
        );
    }

    /**
     * @return array<string, int|string|bool>
     */
    public static function policy(): array
    {
        return [
            'runtime_class' => 'preview',
            'storage' => 'private-filesystem',
            'max_jobs_per_invocation' => 1,
            'max_attempts' => self::MAX_ATTEMPTS,
            'arbitrary_payload_allowed' => false,
            'concurrent_workers_allowed' => false,
            'orphan_recovery' => true,
        ];
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function workerLockPath(): string
    {
        return $this->basePath.'/worker.lock';
    }

    /**
     * @return array{state:string,job_id:string,created:bool,attempts:int}
     */
    public function enqueue(string $jobId, string $type = 'qualification.noop'): array
    {
        $this->guardPreview();
        $this->validateJobId($jobId);
        $this->validateType($type);
        $this->ensureLayout();

        return $this->withStateLock(function () use ($jobId, $type): array {
            $current = $this->statusWithoutGuard($jobId);
            if ($current['state'] !== 'missing') {
                return [
                    'state' => $current['state'],
                    'job_id' => $jobId,
                    'created' => false,
                    'attempts' => $current['attempts'],
                ];
            }

            $record = [
                'schema' => self::SCHEMA,
                'job_id' => $jobId,
                'type' => $type,
                'attempts' => 0,
                'state' => 'pending',
                'last_error_code' => null,
            ];

            $this->writeRecord($this->statePath('pending', $jobId), $record, true);

            return [
                'state' => 'pending',
                'job_id' => $jobId,
                'created' => true,
                'attempts' => 0,
            ];
        });
    }

    /**
     * @return array{state:string,job_id:string|null,attempts:int,recovered:int,last_error_code:string|null}
     */
    public function workOne(): array
    {
        $this->guardPreview();
        $this->ensureLayout();

        $workerLock = fopen($this->workerLockPath(), 'c+');
        if ($workerLock === false) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_LOCK_UNAVAILABLE');
        }
        @chmod($this->workerLockPath(), 0640);

        if (! flock($workerLock, LOCK_EX | LOCK_NB)) {
            fclose($workerLock);

            return [
                'state' => 'busy',
                'job_id' => null,
                'attempts' => 0,
                'recovered' => 0,
                'last_error_code' => null,
            ];
        }

        try {
            $claim = $this->withStateLock(function (): array {
                $recovered = $this->recoverOrphanedProcessing();

                return [
                    'recovered' => $recovered,
                    'claim' => $this->claimNextPending(),
                ];
            });

            if ($claim['claim'] === null) {
                return [
                    'state' => 'idle',
                    'job_id' => null,
                    'attempts' => 0,
                    'recovered' => $claim['recovered'],
                    'last_error_code' => null,
                ];
            }

            $jobId = $claim['claim']['job_id'];
            $processingPath = $claim['claim']['path'];
            $record = $this->readRecord($processingPath, $jobId);

            if ($record === null) {
                $deadRecord = [
                    'schema' => self::SCHEMA,
                    'job_id' => $jobId,
                    'type' => 'invalid',
                    'attempts' => 1,
                    'state' => 'dead',
                    'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_MALFORMED_JOB',
                ];
                $this->transition($processingPath, 'dead', $jobId, $deadRecord);

                return [
                    'state' => 'dead',
                    'job_id' => $jobId,
                    'attempts' => 1,
                    'recovered' => $claim['recovered'],
                    'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_MALFORMED_JOB',
                ];
            }

            $attempt = $record['attempts'] + 1;
            $record['attempts'] = $attempt;
            $record['state'] = 'processing';
            $record['last_error_code'] = null;
            $this->writeRecord($processingPath, $record, false);

            try {
                $this->executeSynthetic($record['type'], $attempt);
                $record['state'] = 'done';
                $this->transition($processingPath, 'done', $jobId, $record);

                return [
                    'state' => 'done',
                    'job_id' => $jobId,
                    'attempts' => $attempt,
                    'recovered' => $claim['recovered'],
                    'last_error_code' => null,
                ];
            } catch (Throwable) {
                $record['last_error_code'] = 'ONEQAY_PREVIEW_QUEUE_SYNTHETIC_FAILURE';

                if ($attempt < self::MAX_ATTEMPTS) {
                    $record['state'] = 'pending';
                    $this->transition($processingPath, 'pending', $jobId, $record);

                    return [
                        'state' => 'retry',
                        'job_id' => $jobId,
                        'attempts' => $attempt,
                        'recovered' => $claim['recovered'],
                        'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_SYNTHETIC_FAILURE',
                    ];
                }

                $record['state'] = 'dead';
                $this->transition($processingPath, 'dead', $jobId, $record);

                return [
                    'state' => 'dead',
                    'job_id' => $jobId,
                    'attempts' => $attempt,
                    'recovered' => $claim['recovered'],
                    'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_SYNTHETIC_FAILURE',
                ];
            }
        } finally {
            flock($workerLock, LOCK_UN);
            fclose($workerLock);
        }
    }

    /**
     * @return array{state:string,job_id:string,attempts:int,last_error_code:string|null}
     */
    public function status(string $jobId): array
    {
        $this->guardPreview();
        $this->validateJobId($jobId);
        $this->ensureLayout();

        return $this->withStateLock(fn (): array => $this->statusWithoutGuard($jobId));
    }

    private function guardPreview(): void
    {
        if ($this->runtimeClass !== 'preview') {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_DENIED');
        }
    }

    private function validateJobId(string $jobId): void
    {
        if (preg_match(self::SAFE_JOB_ID, $jobId) !== 1) {
            throw new InvalidArgumentException('ONEQAY_PREVIEW_QUEUE_INVALID_JOB_ID');
        }
    }

    private function validateType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('ONEQAY_PREVIEW_QUEUE_INVALID_TYPE');
        }
    }

    private function ensureLayout(): void
    {
        foreach ([$this->basePath, ...array_map(fn (string $state): string => $this->basePath.'/'.$state, self::STATES)] as $directory) {
            if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
                throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_STORAGE_UNAVAILABLE');
            }
            @chmod($directory, 0750);
        }

        $this->assertPrivatePathBoundary(true);
    }

    private function assertPrivatePathBoundary(bool $resolveExisting = false): void
    {
        $base = $this->normalisePath($this->basePath);
        $public = $this->normalisePath($this->publicPath);

        if ($base === $public || str_starts_with($base.'/', $public.'/')) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_PUBLIC_PATH_DENIED');
        }

        if (! $resolveExisting) {
            return;
        }

        $realBase = realpath($this->basePath);
        $realPublic = realpath($this->publicPath);
        if ($realBase === false || $realPublic === false) {
            return;
        }

        $realBase = $this->normalisePath($realBase);
        $realPublic = $this->normalisePath($realPublic);
        if ($realBase === $realPublic || str_starts_with($realBase.'/', $realPublic.'/')) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_PUBLIC_PATH_DENIED');
        }
    }

    private function normalisePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function statePath(string $state, string $jobId): string
    {
        return $this->basePath.'/'.$state.'/'.$jobId.'.json';
    }

    private function stateLockPath(): string
    {
        return $this->basePath.'/state.lock';
    }

    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    private function withStateLock(callable $callback): mixed
    {
        $lock = fopen($this->stateLockPath(), 'c+');
        if ($lock === false) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_STATE_LOCK_UNAVAILABLE');
        }
        @chmod($this->stateLockPath(), 0640);

        if (! flock($lock, LOCK_EX)) {
            fclose($lock);
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_STATE_LOCK_UNAVAILABLE');
        }

        try {
            return $callback();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @return array{state:string,job_id:string,attempts:int,last_error_code:string|null}
     */
    private function statusWithoutGuard(string $jobId): array
    {
        $found = [];
        foreach (self::STATES as $state) {
            $path = $this->statePath($state, $jobId);
            if (is_file($path)) {
                $found[$state] = $path;
            }
        }

        if ($found === []) {
            return [
                'state' => 'missing',
                'job_id' => $jobId,
                'attempts' => 0,
                'last_error_code' => null,
            ];
        }

        if (count($found) !== 1) {
            return [
                'state' => 'conflict',
                'job_id' => $jobId,
                'attempts' => 0,
                'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_STATE_CONFLICT',
            ];
        }

        $state = array_key_first($found);
        $record = $this->readRecord($found[$state], $jobId);

        return [
            'state' => $state,
            'job_id' => $jobId,
            'attempts' => $record['attempts'] ?? 0,
            'last_error_code' => $record['last_error_code'] ?? ($record === null ? 'ONEQAY_PREVIEW_QUEUE_MALFORMED_JOB' : null),
        ];
    }

    /**
     * @return array{job_id:string,path:string}|null
     */
    private function claimNextPending(): ?array
    {
        $candidates = glob($this->basePath.'/pending/*.json') ?: [];
        sort($candidates, SORT_STRING);

        foreach ($candidates as $pendingPath) {
            $jobId = basename($pendingPath, '.json');
            if (preg_match(self::SAFE_JOB_ID, $jobId) !== 1) {
                continue;
            }

            $processingPath = $this->statePath('processing', $jobId);
            if (is_file($processingPath) || ! rename($pendingPath, $processingPath)) {
                continue;
            }

            @chmod($processingPath, 0640);

            return [
                'job_id' => $jobId,
                'path' => $processingPath,
            ];
        }

        return null;
    }

    private function recoverOrphanedProcessing(): int
    {
        $recovered = 0;
        $candidates = glob($this->basePath.'/processing/*.json') ?: [];
        sort($candidates, SORT_STRING);

        foreach ($candidates as $processingPath) {
            $jobId = basename($processingPath, '.json');
            if (preg_match(self::SAFE_JOB_ID, $jobId) !== 1) {
                continue;
            }

            $record = $this->readRecord($processingPath, $jobId);
            if ($record === null) {
                $record = [
                    'schema' => self::SCHEMA,
                    'job_id' => $jobId,
                    'type' => 'invalid',
                    'attempts' => 0,
                    'state' => 'dead',
                    'last_error_code' => 'ONEQAY_PREVIEW_QUEUE_MALFORMED_JOB',
                ];
                $this->transitionUnlocked($processingPath, 'dead', $jobId, $record);
                continue;
            }

            $pendingPath = $this->statePath('pending', $jobId);
            if (is_file($pendingPath) || is_file($this->statePath('done', $jobId)) || is_file($this->statePath('dead', $jobId))) {
                $record['state'] = 'dead';
                $record['last_error_code'] = 'ONEQAY_PREVIEW_QUEUE_STATE_CONFLICT';
                $this->transitionUnlocked($processingPath, 'dead', $jobId, $record);
                continue;
            }

            $record['state'] = 'pending';
            $record['last_error_code'] = 'ONEQAY_PREVIEW_QUEUE_WORKER_RECOVERED';
            $this->writeRecord($processingPath, $record, false);
            if (! rename($processingPath, $pendingPath)) {
                throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_RECOVERY_FAILED');
            }
            @chmod($pendingPath, 0640);
            $recovered++;
        }

        return $recovered;
    }

    /**
     * @return array{schema:string,job_id:string,type:string,attempts:int,state:string,last_error_code:string|null}|null
     */
    private function readRecord(string $path, string $expectedJobId): ?array
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        try {
            $record = json_decode($content, true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($record)
            || ($record['schema'] ?? null) !== self::SCHEMA
            || ($record['job_id'] ?? null) !== $expectedJobId
            || ! in_array($record['type'] ?? null, self::TYPES, true)
            || ! is_int($record['attempts'] ?? null)
            || $record['attempts'] < 0
            || $record['attempts'] > self::MAX_ATTEMPTS
            || ! in_array($record['state'] ?? null, self::STATES, true)
            || (! is_null($record['last_error_code'] ?? null) && ! is_string($record['last_error_code']))) {
            return null;
        }

        return [
            'schema' => self::SCHEMA,
            'job_id' => $expectedJobId,
            'type' => $record['type'],
            'attempts' => $record['attempts'],
            'state' => $record['state'],
            'last_error_code' => $record['last_error_code'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    private function writeRecord(string $path, array $record, bool $exclusive): void
    {
        $json = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        $mode = $exclusive ? 'x' : 'c+';
        $handle = @fopen($path, $mode);
        if ($handle === false) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_WRITE_FAILED');
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_WRITE_FAILED');
            }
            if (! $exclusive) {
                ftruncate($handle, 0);
                rewind($handle);
            }
            if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_WRITE_FAILED');
            }
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        @chmod($path, 0640);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function transition(string $processingPath, string $targetState, string $jobId, array $record): void
    {
        $this->withStateLock(function () use ($processingPath, $targetState, $jobId, $record): void {
            $this->transitionUnlocked($processingPath, $targetState, $jobId, $record);
        });
    }

    /**
     * @param array<string, mixed> $record
     */
    private function transitionUnlocked(string $processingPath, string $targetState, string $jobId, array $record): void
    {
        if (! in_array($targetState, ['pending', 'done', 'dead'], true)) {
            throw new InvalidArgumentException('ONEQAY_PREVIEW_QUEUE_INVALID_TRANSITION');
        }

        $targetPath = $this->statePath($targetState, $jobId);
        if ($targetPath !== $processingPath && is_file($targetPath)) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_STATE_CONFLICT');
        }

        $record['state'] = $targetState;
        $this->writeRecord($processingPath, $record, false);
        if ($processingPath !== $targetPath && ! rename($processingPath, $targetPath)) {
            throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_TRANSITION_FAILED');
        }
        @chmod($targetPath, 0640);
    }

    private function executeSynthetic(string $type, int $attempt): void
    {
        if ($type === 'qualification.noop') {
            return;
        }

        if ($type === 'qualification.fail_once' && $attempt > 1) {
            return;
        }

        throw new RuntimeException('ONEQAY_PREVIEW_QUEUE_SYNTHETIC_FAILURE');
    }
}
