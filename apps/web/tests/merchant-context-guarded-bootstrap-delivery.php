<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Tester\CommandTester;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$grant = [
    'tenant_id' => 'merchant-delivery-alpha',
    'identity_id' => 'merchant-delivery-alpha-owner',
    'organization_id' => 'merchant-delivery-alpha-organization',
    'outlet_id' => 'merchant-delivery-alpha-outlet',
    'device_id' => 'merchant-delivery-alpha-device',
    'provisioning_id' => 'merchant-delivery-alpha-initial-admin',
];

foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('g', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED' => 'true',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ENABLED' => 'true',
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_TENANT_ID' => $grant['tenant_id'],
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_IDENTITY_ID' => $grant['identity_id'],
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ORGANIZATION_ID' => $grant['organization_id'],
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_OUTLET_ID' => $grant['outlet_id'],
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_DEVICE_ID' => $grant['device_id'],
    'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_PROVISIONING_ID' => $grant['provisioning_id'],
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var ConsoleKernel $consoleKernel */
$consoleKernel = $app->make(ConsoleKernel::class);
$consoleKernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint177 guarded merchant bootstrap delivery regression failed: '.$message);
    }
};

$assert(extension_loaded('pdo_sqlite'), 'pdo_sqlite is required');

$routeSource = (string) file_get_contents(__DIR__.'/../routes/console.php');
$configSource = (string) file_get_contents(__DIR__.'/../config/oneqay.php');
$webSource = (string) file_get_contents(__DIR__.'/../routes/web.php');
$assert(str_contains($routeSource, "Artisan::command('oneqay:merchant-context:bootstrap'"), 'guarded console command missing');
$assert(! str_contains($routeSource, "oneqay:merchant-context:bootstrap {"), 'merchant bootstrap command accepts self-authorizing tuple arguments');
$assert(str_contains($routeSource, "['local', 'test', 'ci']"), 'Local/Test/CI runtime guard missing');
$assert(str_contains($routeSource, "config('oneqay.merchant_context_bootstrap.enabled', false)"), 'merchant bootstrap arm guard missing');
$assert(str_contains($routeSource, "config('oneqay.merchant_context_bootstrap.grant', [])"), 'configured exact tuple is not the command authorization source');
$assert(str_contains($configSource, "'merchant_context_bootstrap'"), 'merchant bootstrap config missing');
$assert(str_contains($configSource, "env('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ENABLED', false)"), 'merchant bootstrap config is not default-false');
$assert(! str_contains($webSource, 'merchant-context:bootstrap'), 'merchant bootstrap leaked into HTTP routes');

$removeTree = null;
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $removeTree($item->getPathname());
    }
    @rmdir($path);
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s177-merchant-delivery-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace creation failed');
$databasePath = $workspace.DIRECTORY_SEPARATOR.'merchant-delivery.sqlite';
$assert(touch($databasePath), 'SQLite creation failed');

$app['config']->set('database.connections.s177_merchant_delivery', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.first_control_principal_credential_bootstrap.enabled', true);
$app['config']->set('oneqay.merchant_context_bootstrap.enabled', true);
$app['config']->set('oneqay.merchant_context_bootstrap.grant', $grant);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s177_merchant_delivery');
$manager->setDefaultConnection('s177_merchant_delivery');
$connection = $manager->connection('s177_merchant_delivery');
$connection->getPdo();

foreach ([
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
] as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$commands = Artisan::all();
$command = $commands['oneqay:merchant-context:bootstrap'] ?? null;
$assert($command !== null, 'armed CI merchant bootstrap command is not registered');
foreach (['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id', 'provisioning_id'] as $argument) {
    $assert(! $command->getDefinition()->hasArgument($argument), 'command exposes self-authorizing argument '.$argument);
}

$assertTenantAbsent = static function (string $tenantId) use ($assert, $connection): void {
    $assert($connection->table('oneqay_tenants')->where('id', $tenantId)->doesntExist(), 'denied delivery mutated tenant state');
    foreach ([
        'oneqay_identities',
        'oneqay_organizations',
        'oneqay_identity_organizations',
        'oneqay_outlets',
        'oneqay_devices',
        'oneqay_initial_tenant_admin_provisionings',
        'oneqay_tenant_role_assignments',
        'oneqay_identity_password_credentials',
    ] as $table) {
        $assert($connection->table($table)->where('tenant_id', $tenantId)->doesntExist(), 'denied delivery left state in '.$table);
    }
};

// Hidden password confirmation mismatch must fail before any merchant state is written.
$mismatchPassword = 'Merchant-Delivery-Mismatch-S177!';
$mismatchTester = new CommandTester($command);
$mismatchTester->setInputs([$mismatchPassword, 'Merchant-Delivery-Different-S177!']);
$mismatchStatus = $mismatchTester->execute([], ['interactive' => true]);
$mismatchOutput = $mismatchTester->getDisplay(true);
$assert($mismatchStatus !== 0, 'password confirmation mismatch unexpectedly succeeded');
$assert(str_contains($mismatchOutput, 'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_FAILED'), 'mismatch failure is not sanitized');
$assert(! str_contains($mismatchOutput, $mismatchPassword), 'mismatch password leaked in output');
$assertTenantAbsent($grant['tenant_id']);

// Invalid configured authorization material must fail generically and cannot be replaced by command input.
$invalidGrant = $grant;
$invalidGrant['organization_id'] = ' malformed-organization ';
$app['config']->set('oneqay.merchant_context_bootstrap.grant', $invalidGrant);
$invalidTester = new CommandTester($command);
$invalidTester->setInputs(['Merchant-Invalid-Grant-S177!', 'Merchant-Invalid-Grant-S177!']);
$invalidStatus = $invalidTester->execute([], ['interactive' => true]);
$invalidOutput = $invalidTester->getDisplay(true);
$assert($invalidStatus !== 0, 'malformed configured grant unexpectedly succeeded');
$assert(str_contains($invalidOutput, 'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_FAILED'), 'malformed grant failure is not sanitized');
$assert(! str_contains($invalidOutput, 'malformed-organization'), 'configured tuple leaked in malformed grant output');
$assertTenantAbsent($grant['tenant_id']);
$app['config']->set('oneqay.merchant_context_bootstrap.grant', $grant);

$password = '  Merchant Delivery S177 Secure!  ';
$tester = new CommandTester($command);
$tester->setInputs([$password, $password]);
$status = $tester->execute([], ['interactive' => true]);
$output = $tester->getDisplay(true);
$assert($status === 0, 'valid guarded merchant bootstrap did not succeed');
$assert(str_contains($output, 'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP|STATE=applied'), 'sanitized success output missing');
$assert(! str_contains($output, $password), 'merchant bootstrap password leaked in output');
foreach ($grant as $value) {
    $assert(! str_contains($output, $value), 'configured merchant tuple leaked in success output');
}

foreach ([
    ['oneqay_tenants', ['id' => $grant['tenant_id']]],
    ['oneqay_identities', ['tenant_id' => $grant['tenant_id'], 'id' => $grant['identity_id']]],
    ['oneqay_organizations', ['tenant_id' => $grant['tenant_id'], 'id' => $grant['organization_id']]],
    ['oneqay_identity_organizations', ['tenant_id' => $grant['tenant_id'], 'identity_id' => $grant['identity_id'], 'organization_id' => $grant['organization_id']]],
    ['oneqay_outlets', ['tenant_id' => $grant['tenant_id'], 'id' => $grant['outlet_id'], 'organization_id' => $grant['organization_id']]],
    ['oneqay_devices', ['tenant_id' => $grant['tenant_id'], 'id' => $grant['device_id'], 'organization_id' => $grant['organization_id'], 'outlet_id' => $grant['outlet_id']]],
] as [$table, $where]) {
    $query = $connection->table($table);
    foreach ($where as $column => $value) {
        $query->where($column, $value);
    }
    $assert($query->count() === 1, 'guarded delivery did not materialize '.$table);
}

$assert(
    $connection->table('oneqay_initial_tenant_admin_provisionings')
        ->where('tenant_id', $grant['tenant_id'])
        ->where('provisioning_id', $grant['provisioning_id'])
        ->where('identity_id', $grant['identity_id'])
        ->where('outcome', 'applied')
        ->count() === 1,
    'guarded delivery did not journal initial administrator provisioning',
);
$assert(
    $connection->table('oneqay_tenant_role_assignments')
        ->where('tenant_id', $grant['tenant_id'])
        ->where('identity_id', $grant['identity_id'])
        ->where('role_id', 'authorization-policy-administrator')
        ->count() === 1,
    'guarded delivery did not assign protected administrator role',
);
$credential = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', $grant['tenant_id'])
    ->where('identity_id', $grant['identity_id'])
    ->first();
$assert($credential !== null, 'guarded delivery did not create first control credential');
$assert(is_string($credential->password_hash ?? null), 'credential hash missing');
$assert(password_verify($password, (string) $credential->password_hash), 'credential hash does not verify exact password');
$assert(! hash_equals($password, (string) $credential->password_hash), 'plaintext password persisted');
$assert(! password_verify(trim($password), (string) $credential->password_hash), 'password was normalized or trimmed');

// Replay must fail closed because the tenant is no longer fresh, and failure output must remain redacted.
$replayTester = new CommandTester($command);
$replayTester->setInputs(['Merchant-Replay-S177!', 'Merchant-Replay-S177!']);
$replayStatus = $replayTester->execute([], ['interactive' => true]);
$replayOutput = $replayTester->getDisplay(true);
$assert($replayStatus !== 0, 'merchant bootstrap replay unexpectedly succeeded');
$assert(str_contains($replayOutput, 'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_FAILED'), 'replay failure is not sanitized');
foreach ($grant as $value) {
    $assert(! str_contains($replayOutput, $value), 'configured merchant tuple leaked in replay output');
}
$assert(
    $connection->table('oneqay_tenants')->where('id', $grant['tenant_id'])->count() === 1,
    'replay changed tenant cardinality',
);
$assert(
    $connection->table('oneqay_identity_password_credentials')
        ->where('tenant_id', $grant['tenant_id'])
        ->where('identity_id', $grant['identity_id'])
        ->count() === 1,
    'replay changed credential cardinality',
);

$manager->disconnect('s177_merchant_delivery');
$manager->purge('s177_merchant_delivery');
@unlink($databasePath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'disposable workspace cleanup failed');

fwrite(STDOUT, "Sprint177 guarded merchant context bootstrap delivery regression passed.\n");
