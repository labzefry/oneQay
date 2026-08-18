<?php

declare(strict_types=1);

use App\Application\Identity\FirstControlPrincipalCredentialBootstrapRepository;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Background\PreviewFilesystemQueue;
use Illuminate\Support\Facades\Artisan;

// Attribution: Lab | zefry

$bootstrapRuntime = strtolower(trim((string) config('oneqay.runtime_class', '')));
$bootstrapArmed = (bool) config('oneqay.first_control_principal_credential_bootstrap.enabled', false);

if (in_array($bootstrapRuntime, ['local', 'test', 'ci'], true) && $bootstrapArmed) {
    Artisan::command('oneqay:identity:first-control-credential-bootstrap {tenant_id}', function (): int {
        try {
            $tenantId = TenantId::fromString((string) $this->argument('tenant_id'));
            $password = $this->secret('New first-control-principal password');
            $confirmation = $this->secret('Confirm first-control-principal password');

            if (! is_string($password)
                || ! is_string($confirmation)
                || ! hash_equals($password, $confirmation)) {
                $this->error('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_FAILED');

                return 1;
            }

            /** @var FirstControlPrincipalCredentialBootstrapService $service */
            $service = app(FirstControlPrincipalCredentialBootstrapService::class);
            $outcome = $service->bootstrap($tenantId, $password);

            if ($outcome !== FirstControlPrincipalCredentialBootstrapRepository::OUTCOME_APPLIED) {
                $this->error('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_FAILED');

                return 1;
            }

            $this->line('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP|STATE=applied');

            return 0;
        } catch (\Throwable) {
            $this->error('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_FAILED');

            return 1;
        }
    })->purpose('Establish the first credential for the exact Sprint 23 control principal in an explicitly armed Local/Test/CI runtime.');
}

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
