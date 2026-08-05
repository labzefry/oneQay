<?php

declare(strict_types=1);

use OneQay\Configuration\EnvironmentVariableConfigurationSource;
use OneQay\Configuration\StartupConfigurationValidator;
use OneQay\Runtime\HealthResult;
use OneQay\Runtime\NativeRuntimeCapabilityProvider;
use OneQay\Runtime\RandomCorrelationIdGenerator;
use OneQay\Runtime\ReadinessResult;
use OneQay\Runtime\RuntimeCapabilityIdentifier;
use OneQay\Runtime\RuntimeCapabilityValidator;
use OneQay\Runtime\SafeApplicationBootstrap;

require dirname(__DIR__) . '/src/Configuration/Foundation.php';
require dirname(__DIR__) . '/src/Runtime/Foundation.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
$publicDirectory = realpath(__DIR__);
$documentRootIsPublic = $documentRoot !== false && $publicDirectory !== false && hash_equals($publicDirectory, $documentRoot);

$runtime = new NativeRuntimeCapabilityProvider($documentRootIsPublic, [
    RuntimeCapabilityIdentifier::CRON_AVAILABLE => true,
    RuntimeCapabilityIdentifier::LOG_ACCESS_AVAILABLE => true,
    RuntimeCapabilityIdentifier::COMPOSER_AVAILABLE => null,
    RuntimeCapabilityIdentifier::URL_REWRITE => null,
    RuntimeCapabilityIdentifier::BACKGROUND_WORKER_AVAILABLE => null,
]);
$bootstrap = new SafeApplicationBootstrap(
    new StartupConfigurationValidator(),
    new RuntimeCapabilityValidator(),
    new RandomCorrelationIdGenerator()
);
$result = $bootstrap->run(new EnvironmentVariableConfigurationSource(), $runtime);
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

if ($path === '/health') {
    http_response_code(200);
    echo json_encode((new HealthResult(true, $result->correlationId))->toArray(), JSON_THROW_ON_ERROR);
    exit;
}

$readiness = ReadinessResult::fromBootstrap($result);
http_response_code($readiness->isReady ? 200 : 503);
echo json_encode($readiness->toArray(), JSON_THROW_ON_ERROR);
