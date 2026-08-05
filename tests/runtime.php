<?php

declare(strict_types=1);

require __DIR__ . '/../src/Configuration/Foundation.php';
require __DIR__ . '/../src/Runtime/Foundation.php';

use OneQay\Configuration\ArrayConfigurationSource;
use OneQay\Configuration\StartupConfigurationValidator;
use OneQay\Runtime\CapabilityStatus;
use OneQay\Runtime\FixedCorrelationIdGenerator;
use OneQay\Runtime\HealthResult;
use OneQay\Runtime\ReadinessResult;
use OneQay\Runtime\RuntimeCapabilityIdentifier;
use OneQay\Runtime\RuntimeCapabilityReport;
use OneQay\Runtime\RuntimeCapabilityValidator;
use OneQay\Runtime\RuntimeException;
use OneQay\Runtime\SafeApplicationBootstrap;
use OneQay\Runtime\SyntheticRuntimeCapabilityProvider;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) throw new \RuntimeException($message);
};

$assert((new RuntimeCapabilityIdentifier('PHP_VERSION'))->value === 'PHP_VERSION', 'Valid identifier rejected.');
foreach (['', 'php_version', 'PHP-VERSION', ' PHP_VERSION'] as $invalid) {
    try { new RuntimeCapabilityIdentifier($invalid); $assert(false, 'Invalid identifier accepted.'); }
    catch (InvalidArgumentException) { $assert(true, 'Invalid identifier rejected.'); }
}

$required = [
    RuntimeCapabilityIdentifier::PHP_EXTENSION_JSON,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_OPENSSL,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO_MYSQL,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_FILTER,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_SESSION,
    RuntimeCapabilityIdentifier::PHP_EXTENSION_CTYPE,
    RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC,
];
$available = array_fill_keys($required, CapabilityStatus::AVAILABLE);
$validator = new RuntimeCapabilityValidator();
$valid = $validator->validate(new SyntheticRuntimeCapabilityProvider('8.3.26', $available));
$assert($valid->isReady(), 'Valid runtime rejected.');
$old = $validator->validate(new SyntheticRuntimeCapabilityProvider('8.1.29', $available));
$assert(in_array(RuntimeCapabilityIdentifier::PHP_VERSION, $old->unavailable, true), 'Old PHP accepted.');
$missing = $available;
$missing[RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING] = CapabilityStatus::UNAVAILABLE;
$missingReport = $validator->validate(new SyntheticRuntimeCapabilityProvider('8.3.26', $missing));
$assert(in_array(RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING, $missingReport->unavailable, true), 'Missing extension accepted.');
$unknown = $available;
$unknown[RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC] = CapabilityStatus::UNKNOWN;
$unknownReport = $validator->validate(new SyntheticRuntimeCapabilityProvider('8.3.26', $unknown));
$assert(in_array(RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC, $unknownReport->unknown, true), 'Unknown capability assumed available.');
$report = new RuntimeCapabilityReport(['A'], ['A'], [], []);
$assert($report->required === ['A'], 'Immutable report changed.');

$secret = hash('sha256', 'oneqay-runtime-synthetic-secret');
$config = new ArrayConfigurationSource(['APP_ENV' => 'test', 'APP_DEBUG' => false, 'SESSION_SECURE' => true, 'APP_KEY' => $secret]);
$bootstrap = new SafeApplicationBootstrap(new StartupConfigurationValidator(), $validator, new FixedCorrelationIdGenerator('corr-runtime-001'));
$success = $bootstrap->run($config, new SyntheticRuntimeCapabilityProvider('8.3.26', $available));
$assert($success->isSuccessful && $success->correlationId === 'corr-runtime-001', 'Successful bootstrap failed.');
$badConfig = new ArrayConfigurationSource(['APP_ENV' => 'production', 'APP_DEBUG' => true, 'SESSION_SECURE' => false, 'APP_KEY' => $secret]);
$configFailure = $bootstrap->run($badConfig, new SyntheticRuntimeCapabilityProvider('8.3.26', $available));
$assert(!$configFailure->isSuccessful && in_array(RuntimeException::CONFIGURATION_INVALID, $configFailure->errorCodes, true), 'Configuration failure not stopped.');
$runtimeFailure = $bootstrap->run($config, new SyntheticRuntimeCapabilityProvider('8.1.29', $missing));
$assert(!$runtimeFailure->isSuccessful && in_array(RuntimeException::NOT_READY, $runtimeFailure->errorCodes, true), 'Runtime failure not stopped.');
$healthJson = json_encode((new HealthResult(true, 'corr-health'))->toArray(), JSON_THROW_ON_ERROR);
$assert(!str_contains($healthJson, $secret), 'Health leaked secret.');
$readiness = ReadinessResult::fromBootstrap($runtimeFailure);
$readinessJson = json_encode($readiness->toArray(), JSON_THROW_ON_ERROR);
$assert(!$readiness->isReady && !str_contains($readinessJson, '/home/'), 'Readiness leaked path or accepted failure.');
$source = (string) file_get_contents(__DIR__ . '/../src/Runtime/Foundation.php') . (string) file_get_contents(__DIR__ . '/../public/index.php');
$assert(!preg_match('/\b(mysqli_connect|new\s+PDO|CREATE\s+TABLE|migration)\b/i', $source), 'Persistence behavior introduced.');
$assert(!preg_match('/\b(pos|sale|payment|inventory|catalog)\b/i', $source), 'Business behavior introduced.');

fwrite(STDOUT, sprintf("Runtime Capability and Bootstrap tests passed: %d assertions.\n", $assertions));
