<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('s', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint88 final Shift Close migration regression failed: '.$case);
    }
};

$deny = static function (callable $operation, string $case) use ($assert): void {
    $rejected = false;
    try {
        $operation();
    } catch (Throwable) {
        $rejected = true;
    }

    $assert($rejected, $case.' accepted');
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s88-shift-close-migration-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'shift-close.sqlite';
$assert(touch($db), 'SQLite create');

register_shutdown_function(static function () use ($db, $workspace): void {
    @unlink($db);
    @rmdir($workspace);
});

$app['config']->set('database.default', 's88_shift_close');
$app['config']->set('database.connections.s88_shift_close', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');

$manager = $app->make('db');
$manager->purge('s88_shift_close');
$manager->setDefaultConnection('s88_shift_close');
$connection = $manager->connection('s88_shift_close');
$connection->getPdo();
$connection->statement('PRAGMA foreign_keys = ON');

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 27, 'exact migration count through #27');
for ($index = 1; $index <= 27; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter(
            $migrations,
            static fn (string $file): bool => str_starts_with($file, $prefix),
        )) === 1,
        'migration #'.$index.' exact',
    );
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$schema = $connection->getSchemaBuilder();
$assert($schema->hasTable('oneqay_pos_shift_close_evidence'), 'close evidence table missing');
$columns = $schema->getColumnListing('oneqay_pos_shift_close_evidence');
foreach ([
    'tenant_id',
    'evidence_id',
    'operation_id',
    'payload_fingerprint',
    'shift_id',
    'opening_cash_evidence_id',
    'closing_cash_evidence_id',
    'closer_actor_identity_id',
    'organization_id',
    'outlet_id',
    'device_id',
    'cutoff_at_unix',
    'expected_cash_atomic',
    'observed_closing_cash_atomic',
    'variance_atomic',
    'variance_direction',
    'currency',
    'currency_scale',
    'review_evidence_id',
    'review_outcome',
    'correlation_id',
    'closed_at_unix',
] as $column) {
    $assert(in_array($column, $columns, true), 'missing column '.$column);
}

$insertBaseFixture = static function (
    string $suffix,
    string $shiftId,
    string $actor,
    int $openingCash,
    int $closingCash,
    int $cutoff,
) use ($connection): array {
    $tenant = 'tenant-'.$suffix;
    $organization = 'organization-'.$suffix;
    $outlet = 'outlet-'.$suffix;
    $device = 'device-'.$suffix;
    $opening = 'opening-'.$suffix.'-evidence-000000001';
    $closing = 'closing-'.$suffix.'-evidence-000000001';

    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $tenant,
        'id' => $organization,
    ]);
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => $tenant,
        'id' => $actor,
    ]);
    $connection->table('oneqay_outlets')->insert([
        'tenant_id' => $tenant,
        'id' => $outlet,
        'organization_id' => $organization,
    ]);
    $connection->table('oneqay_devices')->insert([
        'tenant_id' => $tenant,
        'id' => $device,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
    ]);
    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id' => $tenant,
        'shift_id' => $shiftId,
        'operation_id' => 'shift-operation-'.$suffix,
        'payload_fingerprint' => hash('sha256', $tenant.'|shift'),
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'active_slot' => 1,
        'correlation_id' => 'shift-correlation-'.$suffix,
        'opened_at_unix' => 1000,
    ]);
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id' => $tenant,
        'evidence_id' => $opening,
        'operation_id' => 'opening-operation-'.$suffix,
        'payload_fingerprint' => hash('sha256', $tenant.'|opening'),
        'shift_id' => $shiftId,
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'opening_cash_atomic' => $openingCash,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_DECLARED_OPENING_CASH',
        'correlation_id' => 'opening-correlation-'.$suffix,
        'recorded_at_unix' => 1100,
    ]);
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id' => $tenant,
        'evidence_id' => $closing,
        'operation_id' => 'closing-operation-'.$suffix,
        'payload_fingerprint' => hash('sha256', $tenant.'|closing'),
        'shift_id' => $shiftId,
        'opening_cash_evidence_id' => $opening,
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'closing_cash_atomic' => $closingCash,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH',
        'correlation_id' => 'closing-correlation-'.$suffix,
        'recorded_at_unix' => $cutoff,
    ]);

    return compact(
        'tenant',
        'organization',
        'outlet',
        'device',
        'opening',
        'closing',
        'actor',
        'shiftId',
        'cutoff',
    );
};

$match = $insertBaseFixture(
    'match',
    str_repeat('m', 32),
    'closer-match',
    100,
    900,
    2000,
);

$matchRow = [
    'tenant_id' => $match['tenant'],
    'evidence_id' => 'shift-close-match-evidence-000001',
    'operation_id' => 'shift-close-match-operation-0001',
    'payload_fingerprint' => hash('sha256', 'match-close'),
    'shift_id' => $match['shiftId'],
    'opening_cash_evidence_id' => $match['opening'],
    'closing_cash_evidence_id' => $match['closing'],
    'closer_actor_identity_id' => $match['actor'],
    'organization_id' => $match['organization'],
    'outlet_id' => $match['outlet'],
    'device_id' => $match['device'],
    'cutoff_at_unix' => $match['cutoff'],
    'expected_cash_atomic' => 900,
    'observed_closing_cash_atomic' => 900,
    'variance_atomic' => 0,
    'variance_direction' => 'MATCH',
    'currency' => 'IDR',
    'currency_scale' => 0,
    'review_evidence_id' => null,
    'review_outcome' => null,
    'correlation_id' => 'shift-close-match-correlation-0001',
    'closed_at_unix' => 2100,
];
$connection->table('oneqay_pos_shift_close_evidence')->insert($matchRow);
$assert(
    $connection->table('oneqay_pos_shift_close_evidence')
        ->where('tenant_id', $match['tenant'])
        ->where('shift_id', $match['shiftId'])
        ->count() === 1,
    'valid MATCH close rejected',
);

$deny(
    fn () => $connection->table('oneqay_pos_shift_close_evidence')->insert(array_merge(
        $matchRow,
        [
            'evidence_id' => 'shift-close-match-evidence-000002',
            'operation_id' => 'shift-close-match-operation-0002',
        ],
    )),
    'duplicate shift close',
);

$deny(
    fn () => $connection->table('oneqay_pos_shift_close_evidence')
        ->where('tenant_id', $match['tenant'])
        ->where('evidence_id', $matchRow['evidence_id'])
        ->update([
            'variance_direction' => 'OVER',
            'variance_atomic' => 1,
        ]),
    'invalid MATCH-to-OVER update without review',
);

$over = $insertBaseFixture(
    'over',
    str_repeat('o', 32),
    'closer-over',
    100,
    1000,
    3000,
);
$explainer = 'explainer-over';
$reviewer = 'reviewer-over';
foreach ([$explainer, $reviewer] as $identity) {
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => $over['tenant'],
        'id' => $identity,
    ]);
}
$explanation = 'varexp-over-evidence-000000001';
$review = 'varreview-over-evidence-00000001';
$connection->table('oneqay_pos_cash_variance_explanation_evidence')->insert([
    'tenant_id' => $over['tenant'],
    'evidence_id' => $explanation,
    'operation_id' => 'variance-explain-over-operation-0001',
    'payload_fingerprint' => hash('sha256', 'over-explanation'),
    'shift_id' => $over['shiftId'],
    'opening_cash_evidence_id' => $over['opening'],
    'closing_cash_evidence_id' => $over['closing'],
    'actor_identity_id' => $explainer,
    'organization_id' => $over['organization'],
    'outlet_id' => $over['outlet'],
    'cutoff_at_unix' => $over['cutoff'],
    'expected_cash_atomic' => 900,
    'observed_closing_cash_atomic' => 1000,
    'variance_atomic' => 100,
    'variance_direction' => 'OVER',
    'currency' => 'IDR',
    'currency_scale' => 0,
    'explanation_text' => 'Authoritative over variance explanation.',
    'correlation_id' => 'variance-explain-over-correlation-0001',
    'recorded_at_unix' => 3100,
]);
$connection->table('oneqay_pos_cash_variance_review_decision_evidence')->insert([
    'tenant_id' => $over['tenant'],
    'review_evidence_id' => $review,
    'operation_id' => 'variance-review-over-operation-0001',
    'payload_fingerprint' => hash('sha256', 'over-review'),
    'shift_id' => $over['shiftId'],
    'opening_cash_evidence_id' => $over['opening'],
    'closing_cash_evidence_id' => $over['closing'],
    'cash_variance_explanation_evidence_id' => $explanation,
    'explanation_actor_identity_id' => $explainer,
    'reviewer_actor_identity_id' => $reviewer,
    'organization_id' => $over['organization'],
    'outlet_id' => $over['outlet'],
    'cutoff_at_unix' => $over['cutoff'],
    'expected_cash_atomic' => 900,
    'observed_closing_cash_atomic' => 1000,
    'variance_atomic' => 100,
    'variance_direction' => 'OVER',
    'currency' => 'IDR',
    'currency_scale' => 0,
    'explanation_payload_fingerprint' => hash('sha256', 'over-explanation'),
    'review_outcome' => 'REVIEW_ACCEPTED',
    'correlation_id' => 'variance-review-over-correlation-0001',
    'reviewed_at_unix' => 3200,
]);

$overRow = [
    'tenant_id' => $over['tenant'],
    'evidence_id' => 'shift-close-over-evidence-0000001',
    'operation_id' => 'shift-close-over-operation-0001',
    'payload_fingerprint' => hash('sha256', 'over-close'),
    'shift_id' => $over['shiftId'],
    'opening_cash_evidence_id' => $over['opening'],
    'closing_cash_evidence_id' => $over['closing'],
    'closer_actor_identity_id' => $over['actor'],
    'organization_id' => $over['organization'],
    'outlet_id' => $over['outlet'],
    'device_id' => $over['device'],
    'cutoff_at_unix' => $over['cutoff'],
    'expected_cash_atomic' => 900,
    'observed_closing_cash_atomic' => 1000,
    'variance_atomic' => 100,
    'variance_direction' => 'OVER',
    'currency' => 'IDR',
    'currency_scale' => 0,
    'review_evidence_id' => $review,
    'review_outcome' => 'REVIEW_ACCEPTED',
    'correlation_id' => 'shift-close-over-correlation-0001',
    'closed_at_unix' => 3300,
];
$connection->table('oneqay_pos_shift_close_evidence')->insert($overRow);
$assert(
    $connection->table('oneqay_pos_shift_close_evidence')
        ->where('tenant_id', $over['tenant'])
        ->where('shift_id', $over['shiftId'])
        ->count() === 1,
    'valid OVER close rejected',
);

$short = $insertBaseFixture(
    'short',
    str_repeat('h', 32),
    'closer-short',
    100,
    800,
    4000,
);

$deny(
    fn () => $connection->table('oneqay_pos_shift_close_evidence')->insert([
        'tenant_id' => $short['tenant'],
        'evidence_id' => 'shift-close-short-evidence-000001',
        'operation_id' => 'shift-close-short-operation-0001',
        'payload_fingerprint' => hash('sha256', 'short-close-no-review'),
        'shift_id' => $short['shiftId'],
        'opening_cash_evidence_id' => $short['opening'],
        'closing_cash_evidence_id' => $short['closing'],
        'closer_actor_identity_id' => $short['actor'],
        'organization_id' => $short['organization'],
        'outlet_id' => $short['outlet'],
        'device_id' => $short['device'],
        'cutoff_at_unix' => $short['cutoff'],
        'expected_cash_atomic' => 900,
        'observed_closing_cash_atomic' => 800,
        'variance_atomic' => -100,
        'variance_direction' => 'SHORT',
        'currency' => 'IDR',
        'currency_scale' => 0,
        'review_evidence_id' => null,
        'review_outcome' => null,
        'correlation_id' => 'shift-close-short-correlation-0001',
        'closed_at_unix' => 4100,
    ]),
    'SHORT close without accepted review',
);

$deny(
    fn () => $connection->table('oneqay_pos_shift_close_evidence')->insert(array_merge(
        $overRow,
        [
            'tenant_id' => $short['tenant'],
            'evidence_id' => 'shift-close-cross-tenant-review-01',
            'operation_id' => 'shift-close-cross-tenant-operation-01',
            'payload_fingerprint' => hash('sha256', 'cross-tenant-review'),
            'shift_id' => $short['shiftId'],
            'opening_cash_evidence_id' => $short['opening'],
            'closing_cash_evidence_id' => $short['closing'],
            'closer_actor_identity_id' => $short['actor'],
            'organization_id' => $short['organization'],
            'outlet_id' => $short['outlet'],
            'device_id' => $short['device'],
            'cutoff_at_unix' => $short['cutoff'],
            'expected_cash_atomic' => 700,
            'observed_closing_cash_atomic' => 800,
            'variance_atomic' => 100,
            'variance_direction' => 'OVER',
            'correlation_id' => 'shift-close-cross-tenant-correlation',
            'closed_at_unix' => 4200,
        ],
    )),
    'cross-tenant review evidence',
);

$deny(
    fn () => $connection->table('oneqay_pos_shift_close_evidence')->insert(array_merge(
        $overRow,
        [
            'evidence_id' => 'shift-close-rejected-review-0001',
            'operation_id' => 'shift-close-rejected-operation-0001',
            'review_outcome' => 'REVIEW_REJECTED',
        ],
    )),
    'REVIEW_REJECTED finalization',
);

$deny(
    fn () => (require __DIR__.'/../database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php')->down(),
    'migration rollback',
);

$assert(
    (int) $connection->table('oneqay_pos_shifts')
        ->where('tenant_id', $match['tenant'])
        ->where('shift_id', $match['shiftId'])
        ->value('active_slot') === 1,
    'schema migration mutated active shift state',
);

fwrite(STDOUT, "Sprint88 final Shift Close migration source regression passed.\n");
