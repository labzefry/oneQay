<?php

declare(strict_types=1);

use App\Application\Tenancy\VerifiedTenantContext;
use App\Infrastructure\Persistence\PreviewDatabaseQualification;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$valid = [
    'enabled' => true,
    'profile' => 'mariadb',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'preview_database',
    'username' => 'preview_user',
    'password' => 'synthetic-secret-not-for-runtime',
    'charset' => 'utf8mb4',
];

$assert(
    PreviewDatabaseQualification::configurationViolations($valid) === [],
    'valid MariaDB Preview qualification configuration must pass static validation',
);

$invalid = $valid;
$invalid['enabled'] = false;
$invalid['profile'] = 'production';
$invalid['password'] = '';
$invalid['charset'] = 'latin1';
$violations = PreviewDatabaseQualification::configurationViolations($invalid);

foreach (['enabled', 'profile', 'password', 'charset'] as $expected) {
    $assert(in_array($expected, $violations, true), "invalid qualification config must report {$expected}");
}

$secretBearingInvalid = $valid;
$secretBearingInvalid['host'] = '';
$result = (new PreviewDatabaseQualification())->qualify($secretBearingInvalid);
$serialized = json_encode($result, JSON_THROW_ON_ERROR);

$assert(($result['status'] ?? null) === 'blocked', 'invalid qualification config must fail closed');
$assert(($result['failed_check'] ?? null) === 'configuration', 'configuration must be the failed check');
$assert(! str_contains($serialized, (string) $valid['password']), 'qualification result must never expose DB password');
$assert(! array_key_exists('database', $result), 'qualification result must not expose DB name');
$assert(! array_key_exists('username', $result), 'qualification result must not expose DB username');
$assert(($result['production_ready'] ?? true) === false, 'qualification result must never claim Production readiness');
$assert(($result['persistent_schema_created'] ?? true) === false, 'qualification result must never claim persistent schema creation');

$qualification = new PreviewDatabaseQualification();

$missingContext = $qualification->qualify($valid, null);
$missingSerialized = json_encode($missingContext, JSON_THROW_ON_ERROR);
$assert(($missingContext['status'] ?? null) === 'blocked', 'missing verified tenant context must fail closed');
$assert(($missingContext['failed_check'] ?? null) === 'tenant_context', 'missing context must stop at tenant_context');
$assert(($missingContext['checks']['configuration'] ?? null) === 'verified', 'configuration must be verified before context evaluation');
$assert(($missingContext['checks']['tenant_context'] ?? null) === 'blocked', 'tenant_context check must be blocked');
$assert(! str_contains($missingSerialized, 'tenant-alpha'), 'missing-context result must not invent or expose tenant identity');
$assert(! str_contains($missingSerialized, 'tenant-beta'), 'missing-context result must not expose foreign tenant identity');

$malformedContext = new class implements VerifiedTenantContext {
    public function tenantId(): string
    {
        return 'tenant.alpha';
    }
};
$malformedResult = $qualification->qualify($valid, $malformedContext);
$malformedSerialized = json_encode($malformedResult, JSON_THROW_ON_ERROR);
$assert(($malformedResult['status'] ?? null) === 'blocked', 'malformed verified tenant context must fail closed');
$assert(($malformedResult['failed_check'] ?? null) === 'tenant_context', 'malformed context must stop at tenant_context');
$assert(! str_contains($malformedSerialized, 'tenant.alpha'), 'malformed tenant identity must not leak into result');

$nonPreviewContext = new class implements VerifiedTenantContext {
    public function tenantId(): string
    {
        return 'tenant-gamma';
    }
};
$nonPreviewResult = $qualification->qualify($valid, $nonPreviewContext);
$nonPreviewSerialized = json_encode($nonPreviewResult, JSON_THROW_ON_ERROR);
$assert(($nonPreviewResult['status'] ?? null) === 'blocked', 'non-deterministic Preview tenant context must fail closed');
$assert(($nonPreviewResult['failed_check'] ?? null) === 'tenant_context', 'non-Preview context must stop at tenant_context');
$assert(! str_contains($nonPreviewSerialized, 'tenant-gamma'), 'non-Preview tenant identity must not leak into result');

$method = new ReflectionMethod(PreviewDatabaseQualification::class, 'qualify');
$parameters = $method->getParameters();
$assert(count($parameters) === 2, 'qualification must accept configuration plus verified tenant context only');
$assert($parameters[1]->getName() === 'tenantContext', 'second qualification parameter must be the tenant context');

fwrite(STDOUT, "M7.5 Preview database qualification regression passed.\n");
