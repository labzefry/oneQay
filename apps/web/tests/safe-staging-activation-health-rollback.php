<?php

declare(strict_types=1);

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;
use App\Application\SystemUpdate\SystemUpdateActivationCoordinator;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateHealthResult;
use App\Application\SystemUpdate\SystemUpdateHealthVerifier;
use App\Application\SystemUpdate\SystemUpdateOperationState;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;
use App\Application\SystemUpdate\SystemUpdateStateMachine;
use App\Domain\Identity\PlatformIdentityId;
use App\Infrastructure\SystemUpdate\Activation\FilesystemSystemUpdateActiveReleasePointerStore;
use App\Infrastructure\SystemUpdate\Activation\FilesystemSystemUpdateDeploymentLockManager;
use App\Infrastructure\SystemUpdate\Activation\FilesystemSystemUpdateOperationJournal;
use App\Infrastructure\SystemUpdate\Activation\FilesystemSystemUpdateReleaseStore;
use App\Infrastructure\SystemUpdate\Activation\SystemUpdateAtomicJsonFile;
use App\Infrastructure\SystemUpdate\ConfiguredSystemUpdateFeatureGate;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Safe staging/activation regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $expectedCode, string $case) use ($assert): void {
    try {
        $attempt();
        $assert(false, $case);
    } catch (SystemUpdateControlPlaneViolation $violation) {
        $assert($violation->safeCode() === $expectedCode, $case.' safe code');
        $assert(
            $violation->getMessage() === 'System update control plane request denied.',
            $case.' generic message',
        );
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
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $removeTree($path.DIRECTORY_SEPARATOR.$entry);
    }

    @rmdir($path);
};

$makeIdentity = static function (string $hex, string $digestChar): SystemUpdateReleaseIdentity {
    $source = str_repeat($hex, 40);

    return new SystemUpdateReleaseIdentity(
        'm75-preview-'.substr($source, 0, 12),
        $source,
        str_repeat($digestChar, 64),
    );
};

$writePayload = static function (
    string $workspace,
    SystemUpdateReleaseIdentity $identity,
    bool $withSecret = false,
): void {
    $root = $workspace.'/'.$identity->releaseId();

    foreach ([
        $root.'/apps/web/vendor',
        $root.'/apps/web/bootstrap',
        $root.'/apps/web/public',
        $root.'/public-surface',
    ] as $directory) {
        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create synthetic release fixture.');
        }
    }

    file_put_contents($root.'/apps/web/vendor/autoload.php', "<?php\n");
    file_put_contents($root.'/apps/web/bootstrap/app.php', "<?php\n");
    file_put_contents($root.'/apps/web/public/index.php', "<?php\n");
    file_put_contents($root.'/public-surface/index.php', "<?php\n");
    file_put_contents($root.'/RELEASE.json', json_encode([
        'payload_metadata_version' => 1,
        'product' => 'oneQay',
        'environment' => 'TECHNICAL_PREVIEW',
        'production' => false,
        'synthetic_data_only' => true,
        'source_commit' => $identity->sourceCommit(),
        'release_id' => $identity->releaseId(),
        'migration_classification' => 'NO_SCHEMA_CHANGE',
        'updater_activation' => 'DISABLED',
        'attribution' => 'Lab | zefry',
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\n");

    if ($withSecret) {
        file_put_contents($root.'/.env', "APP_KEY=forbidden\n");
    }
};

$root = sys_get_temp_dir().'/oneqay-updater-foundation-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);

try {
    $json = new SystemUpdateAtomicJsonFile();
    $releaseStore = new FilesystemSystemUpdateReleaseStore($root, 2000, 16 * 1024 * 1024);
    $pointerStore = new FilesystemSystemUpdateActiveReleasePointerStore($root, $json);
    $lockManager = new FilesystemSystemUpdateDeploymentLockManager($root, $json);
    $journal = new FilesystemSystemUpdateOperationJournal($root, $json);

    $stable = $makeIdentity('a', '1');
    $next = $makeIdentity('b', '2');
    $rollbackCandidate = $makeIdentity('c', '3');
    $fatalCandidate = $makeIdentity('d', '4');

    $stage = static function (
        string $operationId,
        SystemUpdateReleaseIdentity $identity,
    ) use ($releaseStore, $writePayload): void {
        $workspace = $releaseStore->prepareStagingWorkspace($operationId);
        $writePayload($workspace, $identity);
        $releaseStore->commitStagedRelease(new SystemUpdatePreparedRelease(
            $operationId,
            $identity,
            SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
            SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
            false,
        ));
    };

    $stage('op-aaaaaaaaaaaaaaaa', $stable);
    $pointerStore->initialize($stable, 1_786_910_000);

    $assert($releaseStore->releaseExists($stable->releaseId()), 'STAGE-001 stable release committed');
    $assert($pointerStore->current()?->active()->equals($stable) === true, 'POINTER-001 initial pointer stable');

    $secretOperation = 'op-bbbbbbbbbbbbbbbb';
    $secretWorkspace = $releaseStore->prepareStagingWorkspace($secretOperation);
    $writePayload($secretWorkspace, $next, true);
    $expectDenied(
        static fn () => $releaseStore->commitStagedRelease(new SystemUpdatePreparedRelease(
            $secretOperation,
            $next,
            SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
            SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
            false,
        )),
        'staging_secret_file_forbidden',
        'STAGE-002 secret-bearing file rejected',
    );
    $removeTree($secretWorkspace);

    if (function_exists('symlink')) {
        $linkOperation = 'op-cccccccccccccccc';
        $linkWorkspace = $releaseStore->prepareStagingWorkspace($linkOperation);
        $writePayload($linkWorkspace, $next);
        @symlink('/tmp', $linkWorkspace.'/'.$next->releaseId().'/escape-link');

        if (is_link($linkWorkspace.'/'.$next->releaseId().'/escape-link')) {
            $expectDenied(
                static fn () => $releaseStore->commitStagedRelease(new SystemUpdatePreparedRelease(
                    $linkOperation,
                    $next,
                    SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
                    SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
                    false,
                )),
                'staging_link_forbidden',
                'STAGE-003 symlink rejected',
            );
        }

        $removeTree($linkWorkspace);
    }

    if (function_exists('link')) {
        $hardlinkOperation = 'op-dddddddddddddddd';
        $hardlinkWorkspace = $releaseStore->prepareStagingWorkspace($hardlinkOperation);
        $writePayload($hardlinkWorkspace, $next);
        $sourceFile = $hardlinkWorkspace.'/'.$next->releaseId().'/apps/web/public/index.php';
        $hardlinkFile = $hardlinkWorkspace.'/'.$next->releaseId().'/apps/web/public/index-copy.php';
        @link($sourceFile, $hardlinkFile);

        if (is_file($hardlinkFile)) {
            $expectDenied(
                static fn () => $releaseStore->commitStagedRelease(new SystemUpdatePreparedRelease(
                    $hardlinkOperation,
                    $next,
                    SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
                    SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
                    false,
                )),
                'staging_hardlink_forbidden',
                'STAGE-004 hardlink rejected',
            );
        }

        $removeTree($hardlinkWorkspace);
    }

    $heldLock = $lockManager->acquire(
        'op-eeeeeeeeeeeeeeee',
        'synthetic-platform-admin',
        1_786_910_100,
        60,
    );

    $expectDenied(
        static fn () => $lockManager->acquire(
            'op-ffffffffffffffff',
            'synthetic-platform-admin',
            1_786_910_120,
            60,
        ),
        'deployment_lock_held',
        'LOCK-001 concurrent deployment denied',
    );

    $expectDenied(
        static fn () => $lockManager->acquire(
            'op-ffffffffffffffff',
            'synthetic-platform-admin',
            1_786_910_200,
            60,
        ),
        'deployment_lock_stale_reconciliation_required',
        'LOCK-002 stale lock requires reconciliation',
    );

    $lockManager->release($heldLock);

    $health = new class implements SystemUpdateHealthVerifier {
        /** @var array<string, list<SystemUpdateHealthResult>> */
        private array $results = [];

        public function queue(string $releaseId, SystemUpdateHealthResult ...$results): void
        {
            $this->results[$releaseId] = $results;
        }

        public function verify(SystemUpdateReleaseIdentity $expectedRelease): SystemUpdateHealthResult
        {
            $queue = $this->results[$expectedRelease->releaseId()] ?? [];
            if ($queue === []) {
                return SystemUpdateHealthResult::unhealthy('health_not_configured');
            }

            $result = array_shift($queue);
            $this->results[$expectedRelease->releaseId()] = $queue;

            return $result;
        }
    };

    $coordinator = new SystemUpdateActivationCoordinator(
        new ConfiguredSystemUpdateFeatureGate(true, true),
        new SystemUpdateStateMachine(),
        $releaseStore,
        $pointerStore,
        $lockManager,
        $journal,
        $health,
    );

    $authorization = new PrivilegedUpdateAuthorization(
        PlatformIdentityId::fromString('synthetic-platform-admin'),
        PrivilegedUpdateCapability::INSTALL,
        1_786_911_000,
    );

    $disabledCoordinator = new SystemUpdateActivationCoordinator(
        new ConfiguredSystemUpdateFeatureGate(true, false),
        new SystemUpdateStateMachine(),
        $releaseStore,
        $pointerStore,
        $lockManager,
        $journal,
        $health,
    );

    $preparedNext = new SystemUpdatePreparedRelease(
        'op-1111111111111111',
        $next,
        SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
        SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
        true,
    );

    $expectDenied(
        static fn () => $disabledCoordinator->activate($preparedNext, $authorization, 1_786_911_100),
        'install_disabled',
        'FLAG-001 runtime source gate remains authoritative',
    );

    $health->queue($next->releaseId(), SystemUpdateHealthResult::healthy($next->releaseId()));
    $nextWorkspace = $releaseStore->prepareStagingWorkspace($preparedNext->operationId());
    $writePayload($nextWorkspace, $next);

    $success = $coordinator->activate($preparedNext, $authorization, 1_786_911_100);
    $assert($success->terminalState() === SystemUpdateOperationState::SUCCEEDED, 'ACT-001 healthy activation succeeds');
    $assert($success->activeRelease()->equals($next), 'ACT-002 new release remains active');
    $assert($pointerStore->current()?->active()->equals($next) === true, 'POINTER-002 pointer switched');
    $assert(
        $journal->currentState($preparedNext->operationId()) === SystemUpdateOperationState::SUCCEEDED,
        'JOURNAL-001 success persisted',
    );

    $rollbackRelease = new SystemUpdatePreparedRelease(
        'op-2222222222222222',
        $rollbackCandidate,
        SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
        SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
        true,
    );
    $rollbackWorkspace = $releaseStore->prepareStagingWorkspace($rollbackRelease->operationId());
    $writePayload($rollbackWorkspace, $rollbackCandidate);

    $health->queue(
        $rollbackCandidate->releaseId(),
        SystemUpdateHealthResult::unhealthy('readiness_failed', $rollbackCandidate->releaseId()),
    );
    $health->queue($next->releaseId(), SystemUpdateHealthResult::healthy($next->releaseId()));

    $rolledBack = $coordinator->activate($rollbackRelease, $authorization, 1_786_911_200);
    $assert(
        $rolledBack->terminalState() === SystemUpdateOperationState::ROLLED_BACK,
        'ROLLBACK-001 unhealthy new release automatically rolls back',
    );
    $assert($pointerStore->current()?->active()->equals($next) === true, 'ROLLBACK-002 previous stable restored');
    $assert(
        $journal->currentState($rollbackRelease->operationId()) === SystemUpdateOperationState::ROLLED_BACK,
        'JOURNAL-002 rollback persisted',
    );

    $fatalRelease = new SystemUpdatePreparedRelease(
        'op-3333333333333333',
        $fatalCandidate,
        SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
        SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
        true,
    );
    $fatalWorkspace = $releaseStore->prepareStagingWorkspace($fatalRelease->operationId());
    $writePayload($fatalWorkspace, $fatalCandidate);

    $health->queue(
        $fatalCandidate->releaseId(),
        SystemUpdateHealthResult::unhealthy('readiness_failed', $fatalCandidate->releaseId()),
    );
    $health->queue($next->releaseId(), SystemUpdateHealthResult::unhealthy('rollback_readiness_failed'));

    $expectDenied(
        static fn () => $coordinator->activate($fatalRelease, $authorization, 1_786_911_300),
        'rollback_health_failed',
        'ROLLBACK-003 unhealthy rollback target becomes terminal recovery condition',
    );
    $assert(
        $journal->currentState($fatalRelease->operationId()) === SystemUpdateOperationState::FAILED,
        'JOURNAL-003 rollback failure persisted',
    );

    $expectDenied(
        static fn () => $lockManager->acquire(
            'op-4444444444444444',
            'synthetic-platform-admin',
            1_786_911_301,
            60,
        ),
        'deployment_lock_held',
        'LOCK-003 fatal recovery condition retains active lock lease',
    );

    $pointerPayload = file_get_contents($root.'/current-release.json');
    $journalPayload = file_get_contents(
        $root.'/deployment-state/operations/'.$rollbackRelease->operationId().'.json',
    );
    $serializedSafeState = strtolower((string) $pointerPayload.(string) $journalPayload);

    foreach (['password', 'totp_secret', 'session_token', 'public_html', '.env', '/home/'] as $forbidden) {
        $assert(
            ! str_contains($serializedSafeState, $forbidden),
            'SAFE-001 persisted deployment state excludes '.$forbidden,
        );
    }

    $expectDenied(
        static fn () => new SystemUpdatePreparedRelease(
            'op-5555555555555555',
            $makeIdentity('e', '5'),
            'SCHEMA_CHANGE',
            SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
            true,
        ),
        'schema_change_not_supported',
        'DB-001 schema-changing release fails closed',
    );

    fwrite(STDOUT, "Safe staging, activation, health, and rollback regression passed.\n");
} finally {
    $removeTree($root);
}
