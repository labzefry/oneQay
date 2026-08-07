<?php

declare(strict_types=1);

require __DIR__ . '/../src/SchemaPlanning/Foundation.php';

use OneQay\DataDefinition\DataDefinitionIdentifier as DId;
use OneQay\DataDefinition\PortableScalarType as Scalar;
use OneQay\DataDefinition\TenantScope;
use OneQay\PhysicalMapping\CharsetPolicy;
use OneQay\PhysicalMapping\CollationPolicy;
use OneQay\PhysicalMapping\IndexKind;
use OneQay\PhysicalMapping\PhysicalAttributeMapping as Attribute;
use OneQay\PhysicalMapping\PhysicalEntityMapping as Entity;
use OneQay\PhysicalMapping\PhysicalIdentifier as PId;
use OneQay\PhysicalMapping\PhysicalIndexMapping as Index;
use OneQay\PhysicalMapping\PhysicalMappingManifest as Manifest;
use OneQay\PhysicalMapping\PhysicalReferenceMapping as Reference;
use OneQay\PhysicalMapping\PhysicalScalarMapping as Mapping;
use OneQay\PhysicalMapping\PhysicalTypeIdentifier as PType;
use OneQay\PhysicalMapping\VendorIdentifier as Vendor;
use OneQay\SchemaPlanning\ChangeRisk;
use OneQay\SchemaPlanning\CorrelationId;
use OneQay\SchemaPlanning\DeterministicPhysicalSchemaPlanner as Planner;
use OneQay\SchemaPlanning\DeterministicSchemaChangeReviewer as Reviewer;
use OneQay\SchemaPlanning\ManifestFingerprint;
use OneQay\SchemaPlanning\PhysicalSchemaChange;
use OneQay\SchemaPlanning\PhysicalSchemaPlan;
use OneQay\SchemaPlanning\PlanDisposition as Disposition;
use OneQay\SchemaPlanning\ReviewerReference;
use OneQay\SchemaPlanning\SchemaChangeKind as Kind;
use OneQay\SchemaPlanning\SchemaPlanFingerprint;
use OneQay\SchemaPlanning\SchemaPlanningException;
use OneQay\SchemaPlanning\SchemaReviewDecision as Decision;
use OneQay\SchemaPlanning\SchemaReviewException;
use OneQay\SchemaPlanning\SchemaReviewReasonCode as Reason;
use OneQay\SchemaPlanning\StableChangeIdentifier;

$n = 0;
$ok = static function (bool $v, string $m = 'Assertion failed.') use (&$n): void {
    $n++;
    if (!$v) {
        throw new RuntimeException($m);
    }
};
$planningError = static function (callable $fn, string $code) use ($ok): void {
    try {
        $fn();
        $ok(false, "Expected {$code}.");
    } catch (SchemaPlanningException $e) {
        $ok($e->errorCode === $code, "Unexpected {$e->errorCode}.");
    }
};
$reviewError = static function (callable $fn, string $code) use ($ok): void {
    try {
        $fn();
        $ok(false, "Expected {$code}.");
    } catch (SchemaReviewException $e) {
        $ok($e->errorCode === $code, "Unexpected {$e->errorCode}.");
    }
};
$hasKind = static fn (PhysicalSchemaPlan $p, Kind $k): bool => in_array(
    $k->value,
    array_map(static fn (PhysicalSchemaChange $c): string => $c->kind->value, $p->changes()),
    true,
);
$expectPlan = static function (PhysicalSchemaPlan $p, Disposition $d, ?Kind $k = null) use ($ok, $hasKind): void {
    $ok($p->disposition === $d, 'Unexpected plan disposition.');
    if ($k !== null) {
        $ok($hasKind($p, $k), "Missing {$k->value}.");
    }
};

$id = static fn (string $v): DId => new DId($v);
$pid = static fn (string $v): PId => new PId($v);
$charset = new CharsetPolicy(CharsetPolicy::UTF8MB4);
$unicode = new CollationPolicy(CollationPolicy::UNICODE_CI);
$binary = new CollationPolicy(CollationPolicy::BINARY);
$uuid = static fn (): Mapping => new Mapping(new Scalar(Scalar::UUID), new PType(PType::CHAR_UUID), 36, null, null, $charset, $binary);
$string = static fn (int $length = 128): Mapping => new Mapping(new Scalar(Scalar::STRING), new PType(PType::VARCHAR), $length, null, null, $charset, $unicode);
$integer = static fn (): Mapping => new Mapping(new Scalar(Scalar::INTEGER), new PType(PType::BIGINT_SIGNED));
$attr = static fn (string $logical, string $physical, Mapping $map): Attribute => new Attribute($id($logical), $pid($physical), $map);
$index = static fn (string $name, IndexKind $kind, array $attrs): Index => new Index($pid($name), $kind, $attrs);
$ref = static fn (string $name, string $target, array $map): Reference => new Reference($pid($name), $id($target), $map);
$vendor = new Vendor(Vendor::MARIADB_11);
$manifest = static fn (array $entities): Manifest => new Manifest($vendor, $entities);

$catalog = static function (
    string $name = 'reference_catalog',
    int $length = 128,
    bool $unique = true,
    array $primary = ['ID'],
    ?array $uniqueAttrs = null,
    bool $reordered = false,
) use ($id, $pid, $uuid, $string, $attr, $index): Entity {
    $attrs = [$attr('ID', 'id', $uuid()), $attr('CODE', 'code', $string($length))];
    if ($reordered) {
        $attrs = array_reverse($attrs);
    }
    return new Entity(
        $id('REFERENCE_CATALOG'),
        $pid($name),
        TenantScope::GLOBAL,
        $attrs,
        $index('pk_reference_catalog', IndexKind::PRIMARY, $primary),
        $unique ? [$index('uq_reference_catalog_code', IndexKind::UNIQUE, $uniqueAttrs ?? ['CODE'])] : [],
    );
};
$tenant = static function (
    bool $description = false,
    ?Mapping $codeMap = null,
    array $primary = ['TENANT_ID', 'RECORD_ID'],
    bool $unique = true,
    TenantScope $scope = TenantScope::TENANT_SCOPED,
    ?string $tenantKey = 'TENANT_ID',
    bool $reordered = false,
    bool $owner = false,
) use ($id, $pid, $uuid, $string, $attr, $index): Entity {
    $attrs = [
        $attr('TENANT_ID', 'tenant_id', $uuid()),
        $attr('RECORD_ID', 'record_id', $uuid()),
        $attr('CODE', 'code', $codeMap ?? $string()),
    ];
    if ($description) {
        $attrs[] = $attr('DESCRIPTION', 'description', $string(256));
    }
    if ($owner) {
        $attrs[] = $attr('OWNER_ID', 'owner_id', $uuid());
    }
    if ($reordered) {
        $attrs = array_reverse($attrs);
    }
    return new Entity(
        $id('TENANT_RECORD'),
        $pid('tenant_record'),
        $scope,
        $attrs,
        $index('pk_tenant_record', IndexKind::PRIMARY, $primary),
        $unique ? [$index('uq_tenant_record_code', IndexKind::UNIQUE, ['TENANT_ID', 'CODE'])] : [],
        [],
        $tenantKey === null ? null : $id($tenantKey),
    );
};
$child = static function (array $refs = [], bool $alternate = false) use ($id, $pid, $uuid, $attr, $index): Entity {
    $attrs = [$attr('ID', 'id', $uuid()), $attr('PARENT_ID', 'parent_id', $uuid())];
    if ($alternate) {
        $attrs[] = $attr('ALT_PARENT_ID', 'alt_parent_id', $uuid());
    }
    return new Entity($id('CHILD_RECORD'), $pid('child_record'), TenantScope::GLOBAL, $attrs, $index('pk_child_record', IndexKind::PRIMARY, ['ID']), [], $refs);
};
$extra = static fn (): Entity => new Entity(
    $id('EXTRA_ENTITY'),
    $pid('extra_entity'),
    TenantScope::GLOBAL,
    [$attr('ID', 'id', $uuid())],
    $index('pk_extra_entity', IndexKind::PRIMARY, ['ID']),
);

$planner = new Planner();
$reviewer = new Reviewer();
$base = $manifest([$catalog(), $tenant()]);
$reordered = $manifest([$tenant(reordered: true), $catalog(reordered: true)]);

// Sprint 12 regression coverage.
$none = $planner->plan($base, $reordered, 'corr-schema-001');
$expectPlan($none, Disposition::NO_CHANGES);
$ok($none->changes() === []);
$ok($none->baselineFingerprint->equals($none->targetFingerprint));
$ok(json_encode($none, JSON_THROW_ON_ERROR) === json_encode($planner->plan($base, $reordered, 'corr-schema-001'), JSON_THROW_ON_ERROR));

$entityAdded = $planner->plan($base, $manifest([$catalog(), $tenant(), $extra()]), 'corr-schema-002');
$expectPlan($entityAdded, Disposition::REVIEW_REQUIRED, Kind::ENTITY_CREATED);
$attributeAdded = $planner->plan($base, $manifest([$catalog(), $tenant(description: true)]), 'corr-schema-003');
$expectPlan($attributeAdded, Disposition::REVIEW_REQUIRED, Kind::ATTRIBUTE_ADDED);
$entityRemoved = $planner->plan($manifest([$catalog(), $tenant(), $extra()]), $base, 'corr-schema-004');
$expectPlan($entityRemoved, Disposition::BLOCKED, Kind::ENTITY_REMOVED);
$physicalId = $planner->plan($base, $manifest([$catalog(name: 'reference_catalog_v2'), $tenant()]), 'corr-schema-005');
$expectPlan($physicalId, Disposition::BLOCKED, Kind::ENTITY_PHYSICAL_IDENTIFIER_CHANGED);
$physicalMap = $planner->plan($base, $manifest([$catalog(length: 256), $tenant()]), 'corr-schema-006');
$expectPlan($physicalMap, Disposition::BLOCKED, Kind::ATTRIBUTE_PHYSICAL_MAPPING_CHANGED);
$scalarMap = $planner->plan($base, $manifest([$catalog(), $tenant(codeMap: $integer())]), 'corr-schema-007');
$expectPlan($scalarMap, Disposition::BLOCKED, Kind::ATTRIBUTE_SCALAR_MAPPING_CHANGED);
$primary = $planner->plan($base, $manifest([$catalog(primary: ['CODE']), $tenant()]), 'corr-schema-008');
$expectPlan($primary, Disposition::BLOCKED, Kind::PRIMARY_INDEX_CHANGED);
$uniqueAdded = $planner->plan($manifest([$catalog(unique: false), $tenant()]), $base, 'corr-schema-009');
$expectPlan($uniqueAdded, Disposition::REVIEW_REQUIRED, Kind::UNIQUE_INDEX_ADDED);
$uniqueRemoved = $planner->plan($base, $manifest([$catalog(unique: false), $tenant()]), 'corr-schema-010');
$expectPlan($uniqueRemoved, Disposition::BLOCKED, Kind::UNIQUE_INDEX_REMOVED);
$uniqueChanged = $planner->plan($base, $manifest([$catalog(uniqueAttrs: ['ID']), $tenant()]), 'corr-schema-011');
$expectPlan($uniqueChanged, Disposition::BLOCKED, Kind::UNIQUE_INDEX_CHANGED);

$parentRef = $ref('fk_child_parent', 'REFERENCE_CATALOG', ['PARENT_ID' => 'ID']);
$referenceAdded = $planner->plan($manifest([$catalog(), $tenant(), $child()]), $manifest([$catalog(), $tenant(), $child([$parentRef])]), 'corr-schema-012');
$expectPlan($referenceAdded, Disposition::REVIEW_REQUIRED, Kind::REFERENCE_ADDED);
$referenceRemoved = $planner->plan($manifest([$catalog(), $tenant(), $child([$parentRef])]), $manifest([$catalog(), $tenant(), $child()]), 'corr-schema-013');
$expectPlan($referenceRemoved, Disposition::BLOCKED, Kind::REFERENCE_REMOVED);
$alternateRef = $ref('fk_child_parent', 'REFERENCE_CATALOG', ['ALT_PARENT_ID' => 'ID']);
$referenceChanged = $planner->plan(
    $manifest([$catalog(), $tenant(), $child([$parentRef], true)]),
    $manifest([$catalog(), $tenant(), $child([$alternateRef], true)]),
    'corr-schema-014',
);
$expectPlan($referenceChanged, Disposition::BLOCKED, Kind::REFERENCE_CHANGED);

$scopeBase = $manifest([$catalog(), $tenant(scope: TenantScope::GLOBAL, tenantKey: null, primary: ['RECORD_ID'], unique: false)]);
$scopeChanged = $planner->plan($scopeBase, $manifest([$catalog(), $tenant(unique: false)]), 'corr-schema-015');
$expectPlan($scopeChanged, Disposition::BLOCKED, Kind::TENANT_SCOPE_CHANGED);
$tenantKeyChanged = $planner->plan(
    $manifest([$catalog(), $tenant(unique: false, owner: true)]),
    $manifest([$catalog(), $tenant(primary: ['OWNER_ID', 'RECORD_ID'], unique: false, tenantKey: 'OWNER_ID', owner: true)]),
    'corr-schema-016',
);
$expectPlan($tenantKeyChanged, Disposition::BLOCKED, Kind::TENANT_KEY_CHANGED);

$invalidTenant = $tenant(unique: false, tenantKey: null);
$planningError(fn () => $planner->plan($manifest([$catalog(), $invalidTenant]), $base, 'corr-schema-017'), SchemaPlanningException::MANIFEST_INCOMPATIBLE);
$planningError(fn () => $planner->plan($base, $base, '   '), SchemaPlanningException::CORRELATION_ID_INVALID);
$planningError(fn () => $planner->plan($base, $base, 'unsafe/path'), SchemaPlanningException::CORRELATION_ID_INVALID);

$before = new ManifestFingerprint(str_repeat('1', 64));
$after = new ManifestFingerprint(str_repeat('2', 64));
$manual = new PhysicalSchemaChange(new StableChangeIdentifier(str_repeat('3', 64)), Kind::ENTITY_REMOVED, ChangeRisk::BLOCKED, 'MANUAL_ENTITY', null, $before, null);
$planningError(fn () => new PhysicalSchemaPlan($before, $after, Disposition::REVIEW_REQUIRED, new CorrelationId('corr-invariant-001'), [$manual]), SchemaPlanningException::PLAN_INVALID);
$planningError(fn () => new PhysicalSchemaPlan($before, $after, Disposition::BLOCKED, new CorrelationId('corr-invariant-002'), [$manual, $manual]), SchemaPlanningException::PLAN_INVALID);
$planningError(fn () => new PhysicalSchemaPlan($before, $after, Disposition::NO_CHANGES, new CorrelationId('corr-invariant-003'), []), SchemaPlanningException::PLAN_INVALID);

$multiTarget = $manifest([$catalog(length: 256, unique: false), $tenant(description: true)]);
$multi1 = $planner->plan($base, $multiTarget, 'corr-schema-018');
$multi2 = $planner->plan($base, $multiTarget, 'corr-schema-018');
$ok(json_encode($multi1, JSON_THROW_ON_ERROR) === json_encode($multi2, JSON_THROW_ON_ERROR));
$keys = array_map(static fn (PhysicalSchemaChange $c): string => $c->sortKey(), $multi1->changes());
$sorted = $keys;
sort($sorted, SORT_STRING);
$ok($keys === $sorted);
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE'] as $forbidden) {
    $ok(!str_contains(json_encode($multi1, JSON_THROW_ON_ERROR), $forbidden));
}

// Sprint 13 review envelope.
$notRequired = $reviewer->review($none, 'reviewer-alpha', 'corr-review-001', Decision::NOT_REQUIRED, Reason::NO_CHANGES);
$ok($notRequired->decision === Decision::NOT_REQUIRED);
$ok($notRequired->sourcePlanDisposition === Disposition::NO_CHANGES);
$ok($notRequired->sourcePlanFingerprint->value === SchemaPlanFingerprint::fromPlan($none)->value);
$equivalent = $reviewer->review(
    $planner->plan($base, $reordered, 'corr-schema-001'),
    new ReviewerReference('reviewer-alpha'),
    new CorrelationId('corr-review-001'),
    'NOT_REQUIRED',
    'NO_CHANGES',
);
$ok($notRequired->toCanonicalJson() === $equivalent->toCanonicalJson());

$approved = $reviewer->review($entityAdded, 'zefriansyah', 'corr-review-002', Decision::APPROVED_FOR_MIGRATION_PLANNING, Reason::REVIEW_ACCEPTED);
$ok($approved->decision === Decision::APPROVED_FOR_MIGRATION_PLANNING);
$ok($approved->sourcePlanDisposition === Disposition::REVIEW_REQUIRED);
$ok($approved->sourceBaselineFingerprint->equals($entityAdded->baselineFingerprint));
$ok($approved->sourceTargetFingerprint->equals($entityAdded->targetFingerprint));
$ok($approved->sourceCorrelationId->value === $entityAdded->correlationId->value);
$ok($approved->sourcePlanFingerprint->value === SchemaPlanFingerprint::fromPlan($entityAdded)->value);

$rejected = $reviewer->review($attributeAdded, 'reviewer-beta', 'corr-review-003', Decision::REJECTED, Reason::REVIEW_REJECTED);
$ok($rejected->decision === Decision::REJECTED);
$blockedRejected = $reviewer->review($entityRemoved, 'reviewer-gamma', 'corr-review-004', Decision::REJECTED, Reason::BLOCKED_CHANGE_REJECTED);
$ok($blockedRejected->decision === Decision::REJECTED);
foreach ([$entityRemoved, $scopeChanged, $tenantKeyChanged] as $blockedPlan) {
    $reviewError(
        fn () => $reviewer->review($blockedPlan, 'reviewer-safe', 'corr-review-blocked', Decision::APPROVED_FOR_MIGRATION_PLANNING, Reason::REVIEW_ACCEPTED),
        SchemaReviewException::APPROVAL_FORBIDDEN,
    );
}

$reviewError(fn () => $reviewer->review($entityAdded, 'reviewer/unsafe', 'corr-review-005', 'REJECTED', 'REVIEW_REJECTED'), SchemaReviewException::REVIEWER_REFERENCE_INVALID);
$planningError(fn () => $reviewer->review($entityAdded, 'reviewer-safe', 'unsafe/path', 'REJECTED', 'REVIEW_REJECTED'), SchemaPlanningException::CORRELATION_ID_INVALID);
$reviewError(fn () => $reviewer->review($entityAdded, 'reviewer-safe', 'corr-review-006', 'EXECUTE_MIGRATION', 'REVIEW_ACCEPTED'), SchemaReviewException::DECISION_INVALID);
$reviewError(fn () => $reviewer->review($entityAdded, 'reviewer-safe', 'corr-review-007', 'REJECTED', 'free-form reason'), SchemaReviewException::REASON_CODE_INVALID);
$reviewError(fn () => $reviewer->review($none, 'reviewer-safe', 'corr-review-008', 'REJECTED', 'REVIEW_REJECTED'), SchemaReviewException::TRANSITION_INVALID);

$immutable = false;
try {
    $approved->decision = Decision::REJECTED;
} catch (Error) {
    $immutable = true;
}
$ok($immutable);

$safe = $approved->toCanonicalJson();
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE', 'TENANT_ID', 'tenant_record', '/var/', '/home/', 'https://', 'http://'] as $forbidden) {
    $ok(!str_contains($safe, $forbidden));
}
$payload = json_decode($safe, true, 512, JSON_THROW_ON_ERROR);
foreach (['changes', 'manifest', 'exception', 'sql', 'credential', 'endpoint', 'tenant_data', 'filesystem_path'] as $key) {
    $ok(!array_key_exists($key, $payload));
}

$source = implode("\n", [
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Foundation.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/ValueObjects.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Contracts.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Planning.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Review.php'),
]);
$ok(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $source));
$ok(!str_contains($source, 'new PDO('));
$ok(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $source));
$ok(str_contains($source, 'SchemaChangeKind::VENDOR_CHANGED') && str_contains($source, 'ChangeRisk::BLOCKED'));
$ok(str_contains($source, 'SchemaReviewException::APPROVAL_FORBIDDEN'));
$ok(!preg_match('/\b(pos|sale|payment|inventory)\b/i', $source));

fwrite(STDOUT, sprintf("Schema Planning tests passed: %d assertions.\n", $n));
