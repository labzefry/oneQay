<?php

declare(strict_types=1);

use App\Application\Identity\VerifyFirstPartyIdentityCredential;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityCredentialVerifier;
use Illuminate\Contracts\Http\Kernel;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assertS26 = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint 26 credential regression failed: '.$case);
    }
};

$assertS26(extension_loaded('pdo_sqlite'), 'pdo_sqlite is required');

$removeS26 = static function (string $path): void {
    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    ) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s26-credential-'.getmypid().'-'.bin2hex(random_bytes(4));
$removeS26($workspace);
$assertS26(@mkdir($workspace, 0700, false), 'workspace create failed');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'credential.sqlite';
$assertS26(touch($dbPath), 'SQLite create failed');

$app['config']->set('database.connections.s26_credential', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $dbPath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s26_credential');
$manager->setDefaultConnection('s26_credential');
$connection = $manager->connection('s26_credential');
$connection->getPdo();

$migrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
];
$actualMigrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($actualMigrations);
$assertS26($actualMigrations === $migrations, 'canonical migration set must be exactly #1-#9');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$schema = $connection->getSchemaBuilder();
$assertS26($schema->hasTable('oneqay_identity_password_credentials'), 'credential table missing');
$assertS26($schema->hasTable('oneqay_initial_password_enrollments'), 'Sprint 28 enrollment table missing during Sprint 26 preservation');
foreach (['tenant_id', 'identity_id', 'password_hash'] as $column) {
    $assertS26($schema->hasColumn('oneqay_identity_password_credentials', $column), 'credential column missing: '.$column);
}

$connection->table('oneqay_tenants')->insert([
    ['id' => 'tenant-alpha'],
    ['id' => 'tenant-beta'],
]);
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'identity-shared'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'identity-no-credential'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'identity-malformed'],
    ['tenant_id' => 'tenant-beta', 'id' => 'identity-shared'],
]);

$alphaPassword = 'Alpha-Synthetic-Only-26!';
$betaPassword = 'Beta-Synthetic-Only-26!';
$alphaHash = password_hash($alphaPassword, PASSWORD_DEFAULT);
$betaHash = password_hash($betaPassword, PASSWORD_DEFAULT);
$assertS26(is_string($alphaHash) && $alphaHash !== '', 'alpha fixture hash generation failed');
$assertS26(is_string($betaHash) && $betaHash !== '', 'beta fixture hash generation failed');

$connection->table('oneqay_identity_password_credentials')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'identity-shared', 'password_hash' => $alphaHash],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'identity-malformed', 'password_hash' => 'not-a-valid-password-hash'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'identity-shared', 'password_hash' => $betaHash],
]);

$credentialSnapshot = static function () use ($connection): array {
    return $connection->table('oneqay_identity_password_credentials')
        ->orderBy('tenant_id')
        ->orderBy('identity_id')
        ->get(['tenant_id', 'identity_id', 'password_hash'])
        ->map(static fn (object $row): array => [
            'tenant_id' => (string) $row->tenant_id,
            'identity_id' => (string) $row->identity_id,
            'password_hash' => (string) $row->password_hash,
        ])
        ->all();
};

$before = $credentialSnapshot();
$alphaTenant = TenantId::fromString('tenant-alpha');
$betaTenant = TenantId::fromString('tenant-beta');
$sharedIdentity = PlatformIdentityId::fromString('identity-shared');
$absentIdentity = PlatformIdentityId::fromString('identity-absent');
$noCredentialIdentity = PlatformIdentityId::fromString('identity-no-credential');
$malformedIdentity = PlatformIdentityId::fromString('identity-malformed');

$ciVerifier = new VerifyFirstPartyIdentityCredential(
    new LaravelFirstPartyIdentityCredentialVerifier($connection, true, 'ci'),
);
$assertS26($ciVerifier->verify($alphaTenant, $sharedIdentity, $alphaPassword), 'alpha correct password must verify');
$assertS26(! $ciVerifier->verify($alphaTenant, $sharedIdentity, 'wrong-password'), 'wrong password must fail');
$assertS26(! $ciVerifier->verify($alphaTenant, $absentIdentity, $alphaPassword), 'absent identity must fail generically');
$assertS26(! $ciVerifier->verify($alphaTenant, $noCredentialIdentity, $alphaPassword), 'missing credential must fail generically');
$assertS26($ciVerifier->verify($betaTenant, $sharedIdentity, $betaPassword), 'same textual identity in beta must use independent credential');
$assertS26(! $ciVerifier->verify($betaTenant, $sharedIdentity, $alphaPassword), 'alpha credential must not authenticate beta');
$assertS26(! $ciVerifier->verify($alphaTenant, $sharedIdentity, $betaPassword), 'beta credential must not authenticate alpha');
$assertS26(! $ciVerifier->verify($alphaTenant, $malformedIdentity, $alphaPassword), 'malformed stored hash must fail generically');
$assertS26(! $ciVerifier->verify($alphaTenant, $sharedIdentity, ''), 'empty password must fail closed');
$assertS26(! $ciVerifier->verify($alphaTenant, $sharedIdentity, str_repeat('x', 4097)), 'oversized password must fail closed');

$disabledVerifier = new VerifyFirstPartyIdentityCredential(
    new LaravelFirstPartyIdentityCredentialVerifier($connection, false, 'ci'),
);
$previewVerifier = new VerifyFirstPartyIdentityCredential(
    new LaravelFirstPartyIdentityCredentialVerifier($connection, true, 'preview'),
);
$productionVerifier = new VerifyFirstPartyIdentityCredential(
    new LaravelFirstPartyIdentityCredentialVerifier($connection, true, 'production'),
);
$assertS26(! $disabledVerifier->verify($alphaTenant, $sharedIdentity, $alphaPassword), 'persistence-disabled verification must fail closed');
$assertS26(! $previewVerifier->verify($alphaTenant, $sharedIdentity, $alphaPassword), 'Preview verification must be denied');
$assertS26(! $productionVerifier->verify($alphaTenant, $sharedIdentity, $alphaPassword), 'Production verification must be denied');

$after = $credentialSnapshot();
$assertS26($after === $before, 'verifier changed credential data');

$verifierSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php');
$serviceSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/VerifyFirstPartyIdentityCredential.php');
$contractSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityCredentialVerifier.php');
$migrationSource = (string) file_get_contents(__DIR__.'/../database/migrations/0000_00_00_000007_create_identity_password_credentials.php');

$assertS26(substr_count($verifierSource, "->where('tenant_id',") === 1, 'verifier must use one explicit tenant lookup predicate');
$assertS26(substr_count($verifierSource, "->where('identity_id',") === 1, 'verifier must use one explicit identity lookup predicate');
$assertS26(str_contains($verifierSource, "['local', 'test', 'ci']"), 'Local/Test/CI allowlist missing');
$assertS26(str_contains($verifierSource, 'password_verify('), 'password_verify primitive missing');
$assertS26(str_contains($verifierSource, 'DUMMY_HASH'), 'dummy hash path missing');
$assertS26(! str_contains($verifierSource, 'password_hash('), 'production verifier must not create password hashes');
$assertS26(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $verifierSource), 'read-only verifier gained write mechanics');
$assertS26(! preg_match('/\b(log|logger|info|debug|warning|error)\s*\(/i', $verifierSource), 'verifier gained logging mechanics');
$assertS26(! preg_match('/Illuminate\\\\|Schema::|DB::|new PDO|mysqli_/', $serviceSource.$contractSource), 'Application credential layer gained framework/database mechanics');
$assertS26(str_contains($serviceSource, '#[\\SensitiveParameter]'), 'Application service password parameter is not sensitive');
$assertS26(str_contains($contractSource, '#[\\SensitiveParameter]'), 'Application contract password parameter is not sensitive');

foreach ([
    "string('tenant_id', 64)",
    "string('identity_id', 96)",
    "string('password_hash', 255)",
    "primary(['tenant_id', 'identity_id']",
    "['tenant_id', 'identity_id']",
    "references(['tenant_id', 'id'])",
    "on('oneqay_identities')",
    'Forward-only generated migration; rollback is not authorized.',
] as $marker) {
    $assertS26(str_contains($migrationSource, $marker), 'migration #7 contract missing: '.$marker);
}

$manager->disconnect('s26_credential');
$manager->purge('s26_credential');
$app['config']->set('database.connections.s26_credential', null);
@unlink($dbPath);
$removeS26($workspace);
$assertS26(! file_exists($workspace), 'workspace cleanup failed');

echo "Sprint 26 identity credential verification regression passed.\n";
