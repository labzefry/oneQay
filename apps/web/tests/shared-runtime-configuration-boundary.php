<?php

declare(strict_types=1);

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSecretPresenceProbe;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSecretReference;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationActivationCoordinator;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationBoundary;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationPolicy;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationSource;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedRuntimeConfiguration;
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
use App\Infrastructure\SystemUpdate\SharedConfiguration\FilesystemSystemUpdateSharedConfigurationMetadataStore;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Shared runtime configuration regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $expectedCode, string $case) use ($assert): void {
    try {
        $attempt();
        $assert(false, $case);
    } catch (SystemUpdateControlPlaneViolation $violation) {
        $assert($violation->safeCode() === $expectedCode, $case.' safe code');
        $assert($violation->getMessage() === 'System update control plane request denied.', $case.' generic message');
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

$makeIdentity = static function (string $hex, string $digest): SystemUpdateReleaseIdentity {
    $source = str_repeat($hex, 40);

    return new SystemUpdateReleaseIdentity(
        'm75-preview-'.substr($source, 0, 12),
        $source,
        str_repeat($digest, 64),
    );
};

$writePayload = static function (string $workspace, SystemUpdateReleaseIdentity $identity): void {
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
};

$expectDenied(
    static fn () => SystemUpdateSecretReference::fromName('DB_PASSWORD'),
    'shared_secret_reference_forbidden',
    'SECRET-001 arbitrary database secret reference denied',
);
$expectDenied(
    static fn () => SystemUpdateSecretReference::fromName('API_TOKEN'),
    'shared_secret_reference_forbidden',
    'SECRET-002 arbitrary token reference denied',
);

$source = new class implements SystemUpdateSharedConfigurationSource {
    private SystemUpdateSharedRuntimeConfiguration $configuration;

    public function __construct()
    {
        $this->configuration = new SystemUpdateSharedRuntimeConfiguration(
            SystemUpdateSharedRuntimeConfiguration::PROFILE,
            SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION,
            'preview',
            'preview',
            false,
            'https://preview.example.test',
            'stderr',
            'array',
            'array',
        );
    }

    public function replace(SystemUpdateSharedRuntimeConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function snapshot(): SystemUpdateSharedRuntimeConfiguration
    {
        return $this->configuration;
    }
};

$secretProbe = new class implements SystemUpdateSecretPresenceProbe {
    private string $rawSecret = 'synthetic-raw-secret-must-never-persist';
    private bool $present = true;

    public function setPresent(bool $present): void
    {
        $this->present = $present;
    }

    public function available(SystemUpdateSecretReference $reference): bool
    {
        return $reference->safeName() === SystemUpdateSecretReference::APP_KEY
            && $this->present
            && $this->rawSecret !== '';
    }
};

$root = sys_get_temp_dir().'/oneqay-shared-config-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);

try {
    $json = new SystemUpdateAtomicJsonFile();
    $metadata = new FilesystemSystemUpdateSharedConfigurationMetadataStore($root, $json);
    $policy = new SystemUpdateSharedConfigurationPolicy();
    $boundary = new SystemUpdateSharedConfigurationBoundary($source, $secretProbe, $policy, $metadata);

    $stable = $makeIdentity('a', '1');
    $candidate = $makeIdentity('b', '2');
    $release = new SystemUpdatePreparedRelease(
        'op-1111111111111111',
        $candidate,
        SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
        SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
        true,
    );

    $secretProbe->setPresent(false);
    $expectDenied(
        static fn () => $boundary->assertCompatible($release, 1_786_920_000),
        'shared_secret_missing',
        'CONFIG-001 missing APP_KEY presence fails closed',
    );

    $source->replace(new SystemUpdateSharedRuntimeConfiguration(
        SystemUpdateSharedRuntimeConfiguration::PROFILE,
        SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION,
        'preview',
        'preview',
        false,
        'http://preview.example.test',
        'stderr',
        'array',
        'array',
    ));
    $secretProbe->setPresent(true);
    $expectDenied(
        static fn () => $boundary->assertCompatible($release, 1_786_920_010),
        'shared_preview_https_required',
        'CONFIG-002 Preview shared URL requires HTTPS',
    );

    $source->replace(new SystemUpdateSharedRuntimeConfiguration(
        SystemUpdateSharedRuntimeConfiguration::PROFILE,
        SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION,
        'preview',
        'preview',
        true,
        'https://preview.example.test',
        'stderr',
        'array',
        'array',
    ));
    $expectDenied(
        static fn () => $boundary->assertCompatible($release, 1_786_920_020),
        'shared_debug_policy_invalid',
        'CONFIG-003 Preview debug=true denied',
    );

    $source->replace(new SystemUpdateSharedRuntimeConfiguration(
        SystemUpdateSharedRuntimeConfiguration::PROFILE,
        SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION,
        'preview',
        'preview',
        false,
        'https://preview.example.test',
        'stderr',
        'array',
        'array',
    ));

    $compatibility = $boundary->assertCompatible($release, 1_786_920_030);
    $safe = $compatibility->toSafeArray();
    $assert($compatibility->compatible(), 'CONFIG-004 valid shared configuration compatible');
    $assert(($safe['required_secrets']['APP_KEY'] ?? null) === 'PRESENT', 'CONFIG-005 secret presence only');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($safe['safe_fingerprint'] ?? '')) === 1, 'CONFIG-006 safe fingerprint');
    $assert(($safe['configuration']['url_scheme'] ?? null) === 'https', 'CONFIG-007 only URL scheme exposed');

    $serialized = strtolower(json_encode($safe, JSON_THROW_ON_ERROR));
    foreach (['synthetic-raw-secret-must-never-persist', 'preview.example.test', 'db_password', 'api_token', '.env', '/home/', 'public_html'] as $forbidden) {
        $assert(! str_contains($serialized, strtolower($forbidden)), 'SAFE-001 safe compatibility excludes '.$forbidden);
    }

    $releaseStore = new FilesystemSystemUpdateReleaseStore($root, 2000, 16 * 1024 * 1024);
    $pointerStore = new FilesystemSystemUpdateActiveReleasePointerStore($root, $json);
    $lockManager = new FilesystemSystemUpdateDeploymentLockManager($root, $json);
    $journal = new FilesystemSystemUpdateOperationJournal($root, $json);

    $stableWorkspace = $releaseStore->prepareStagingWorkspace('op-aaaaaaaaaaaaaaaa');
    $writePayload($stableWorkspace, $stable);
    $releaseStore->commitStagedRelease(new SystemUpdatePreparedRelease(
        'op-aaaaaaaaaaaaaaaa',
        $stable,
        SystemUpdatePreparedRelease::MIGRATION_CLASSIFICATION,
        SystemUpdatePreparedRelease::ROLLBACK_COMPATIBILITY,
        false,
    ));
    $pointerStore->initialize($stable, 1_786_920_040);

    $health = new class implements SystemUpdateHealthVerifier {
        public function verify(SystemUpdateReleaseIdentity $expectedRelease): SystemUpdateHealthResult
        {
            return SystemUpdateHealthResult::healthy($expectedRelease->releaseId());
        }
    };

    $inner = new SystemUpdateActivationCoordinator(
        new ConfiguredSystemUpdateFeatureGate(true, true),
        new SystemUpdateStateMachine(),
        $releaseStore,
        $pointerStore,
        $lockManager,
        $journal,
        $health,
    );
    $composed = new SystemUpdateSharedConfigurationActivationCoordinator($boundary, $inner);
    $authorization = new PrivilegedUpdateAuthorization(
        PlatformIdentityId::fromString('synthetic-platform-admin'),
        PrivilegedUpdateCapability::INSTALL,
        1_786_920_050,
    );

    $secretProbe->setPresent(false);
    $expectDenied(
        static fn () => $composed->activate($release, $authorization, 1_786_920_060),
        'shared_secret_missing',
        'ACT-001 incompatible shared configuration blocks before activation',
    );
    $assert($pointerStore->current()?->active()->equals($stable) === true, 'ACT-002 pointer unchanged on config failure');
    $assert(! $releaseStore->releaseExists($candidate->releaseId()), 'ACT-003 candidate not promoted on config failure');

    $secretProbe->setPresent(true);
    $candidateWorkspace = $releaseStore->prepareStagingWorkspace($release->operationId());
    $writePayload($candidateWorkspace, $candidate);
    $result = $composed->activate($release, $authorization, 1_786_920_070);
    $assert($result->terminalState() === SystemUpdateOperationState::SUCCEEDED, 'ACT-004 compatible configuration permits foundation activation');
    $assert($pointerStore->current()?->active()->equals($candidate) === true, 'ACT-005 pointer changes only after compatibility success');

    $metadataPayload = file_get_contents($root.'/deployment-state/shared-configuration.json');
    $assert(is_string($metadataPayload) && $metadataPayload !== '', 'META-001 metadata persisted');
    $lowerMetadata = strtolower((string) $metadataPayload);
    foreach (['synthetic-raw-secret-must-never-persist', 'preview.example.test', 'password', 'token', '.env', '/home/', 'public_html'] as $forbidden) {
        $assert(! str_contains($lowerMetadata, strtolower($forbidden)), 'META-002 persisted metadata excludes '.$forbidden);
    }
    $assert(str_contains($metadataPayload, '"APP_KEY":"PRESENT"'), 'META-003 only secret presence persisted');

    fwrite(STDOUT, "Shared runtime configuration boundary regression passed.\n");
} finally {
    $removeTree($root);
}
