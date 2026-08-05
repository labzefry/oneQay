<?php

declare(strict_types=1);

require __DIR__ . '/../src/Configuration/Foundation.php';
require __DIR__ . '/../src/Persistence/Foundation.php';

use OneQay\Configuration\ArrayConfigurationSource;
use OneQay\Persistence\DatabaseConfigurationLoader;
use OneQay\Persistence\DatabaseConnectionPolicy;
use OneQay\Persistence\DatabaseConnectionService;
use OneQay\Persistence\DatabaseDriverIdentifier;
use OneQay\Persistence\PersistenceCapabilityIdentifier;
use OneQay\Persistence\PersistenceCapabilityReport;
use OneQay\Persistence\PersistenceCapabilityStatus;
use OneQay\Persistence\PersistenceCapabilityValidator;
use OneQay\Persistence\PersistenceException;
use OneQay\Persistence\SyntheticDatabaseConnector;
use OneQay\Persistence\SyntheticPersistenceCapabilityProvider;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$assert((new PersistenceCapabilityIdentifier('PDO_MYSQL_DRIVER'))->value === 'PDO_MYSQL_DRIVER', 'Valid capability identifier rejected.');
foreach (['', 'pdo_mysql_driver', 'PDO-MYSQL', ' PDO_MYSQL_DRIVER'] as $invalid) {
    try {
        new PersistenceCapabilityIdentifier($invalid);
        $assert(false, 'Invalid persistence capability identifier accepted.');
    } catch (InvalidArgumentException) {
        $assert(true, 'Invalid persistence capability identifier rejected.');
    }
}

$capabilities = [
    PersistenceCapabilityIdentifier::PDO_MYSQL_DRIVER => PersistenceCapabilityStatus::AVAILABLE,
    PersistenceCapabilityIdentifier::MARIADB_SERVER => PersistenceCapabilityStatus::AVAILABLE,
];
$capabilityValidator = new PersistenceCapabilityValidator();
$readyReport = $capabilityValidator->validate(new SyntheticPersistenceCapabilityProvider($capabilities));
$assert($readyReport->isFoundationReady(), 'Verified persistence capability was rejected.');

$unknownCapabilities = $capabilities;
$unknownCapabilities[PersistenceCapabilityIdentifier::MARIADB_SERVER] = PersistenceCapabilityStatus::UNKNOWN;
$unknownReport = $capabilityValidator->validate(new SyntheticPersistenceCapabilityProvider($unknownCapabilities));
$assert(!$unknownReport->isFoundationReady() && $unknownReport->unknown === [PersistenceCapabilityIdentifier::MARIADB_SERVER], 'Unknown capability was assumed available.');

$report = new PersistenceCapabilityReport(['A'], ['A'], [], []);
$assert($report->required === ['A'] && $report->available === ['A'], 'Immutable persistence report changed.');

$syntheticPassword = hash('sha256', 'oneqay-sprint-08-synthetic-password');
$validSource = new ArrayConfigurationSource([
    'DB_DRIVER' => 'pdo_mysql',
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_NAME' => 'oneqay_preview',
    'DB_USER' => 'oneqay_preview_user',
    'DB_PASSWORD' => $syntheticPassword,
    'DB_CHARSET' => 'utf8mb4',
]);
$loader = new DatabaseConfigurationLoader();
$configuration = $loader->load($validSource);
$assert($configuration->driver->value === DatabaseDriverIdentifier::PDO_MYSQL, 'Database driver was not canonicalized.');
$assert($configuration->host === 'localhost' && $configuration->port === 3306, 'Database endpoint configuration changed.');
$assert($configuration->charset === 'utf8mb4', 'Database charset was not constrained.');
$assert((string) $configuration->password === '[REDACTED]', 'Database password string leaked.');
$assert(!str_contains(json_encode($configuration->password, JSON_THROW_ON_ERROR), $syntheticPassword), 'Database password JSON leaked.');
$assert(!str_contains(serialize($configuration->password), $syntheticPassword), 'Database password serialization leaked.');
$assert(!str_contains(print_r($configuration, true), $syntheticPassword), 'Database configuration print leaked password.');
$assert(!str_contains(var_export($configuration, true), $syntheticPassword), 'Database configuration export leaked password.');

foreach ([
    ['DB_DRIVER' => 'mysqli'],
    ['DB_HOST' => 'https://database.example.test'],
    ['DB_PORT' => '70000'],
    ['DB_NAME' => 'invalid-name'],
    ['DB_USER' => 'invalid user'],
    ['DB_CHARSET' => 'latin1'],
] as $override) {
    $values = [
        'DB_DRIVER' => 'pdo_mysql',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'DB_NAME' => 'oneqay_preview',
        'DB_USER' => 'oneqay_preview_user',
        'DB_PASSWORD' => $syntheticPassword,
        'DB_CHARSET' => 'utf8mb4',
    ];
    $values = array_replace($values, $override);
    try {
        $loader->load(new ArrayConfigurationSource($values));
        $assert(false, 'Unsafe database configuration accepted.');
    } catch (PersistenceException $exception) {
        $assert($exception->errorCode === PersistenceException::CONFIGURATION_INVALID, 'Unexpected database configuration error code.');
    }
}

$policy = new DatabaseConnectionPolicy();
$options = $policy->pdoOptions();
$assert($options[PDO::ATTR_ERRMODE] === PDO::ERRMODE_EXCEPTION, 'PDO exception mode is not enforced.');
$assert($options[PDO::ATTR_EMULATE_PREPARES] === false, 'Emulated prepares are enabled.');
$assert($options[PDO::ATTR_PERSISTENT] === false, 'Persistent PDO connections are enabled.');
$assert($options[PDO::ATTR_STRINGIFY_FETCHES] === false, 'PDO stringify fetches are enabled.');

$service = new DatabaseConnectionService($loader);
$successConnector = new SyntheticDatabaseConnector();
$success = $service->connect($validSource, $successConnector, 'corr-persistence-001');
$assert($success->isSuccessful && $success->metadata === ['driver' => 'PDO_MYSQL'], 'Synthetic database connection failed.');
$encodedSuccess = json_encode($success, JSON_THROW_ON_ERROR);
$assert(!str_contains($encodedSuccess, $syntheticPassword), 'Connection result leaked password.');
$assert(!str_contains($encodedSuccess, 'localhost') && !str_contains($encodedSuccess, 'oneqay_preview_user'), 'Connection result leaked endpoint or username.');

$invalidSource = new ArrayConfigurationSource(['DB_DRIVER' => 'pdo_mysql']);
$unusedConnector = new SyntheticDatabaseConnector();
$configFailure = $service->connect($invalidSource, $unusedConnector, 'corr-persistence-002');
$assert(!$configFailure->isSuccessful && $unusedConnector->connectCalls === 0, 'Invalid configuration reached connector.');
$assert(in_array(PersistenceException::CONFIGURATION_INVALID, $configFailure->errorCodes, true), 'Configuration failure code missing.');

$throwingConnector = new SyntheticDatabaseConnector(true, true);
$connectionFailure = $service->connect($validSource, $throwingConnector, 'corr-persistence-003');
$assert(!$connectionFailure->isSuccessful && in_array(PersistenceException::CONNECTION_FAILED, $connectionFailure->errorCodes, true), 'Connection exception was not mapped safely.');

$disconnectedConnector = new SyntheticDatabaseConnector(false, false);
$unavailable = $service->connect($validSource, $disconnectedConnector, 'corr-persistence-004');
$assert(!$unavailable->isSuccessful && in_array(PersistenceException::CONNECTION_UNAVAILABLE, $unavailable->errorCodes, true), 'Unavailable connection was accepted.');

$source = (string) file_get_contents(__DIR__ . '/../src/Persistence/Foundation.php');
$assert(!preg_match('/\b(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE)\b/i', $source), 'Schema, migration, or write behavior introduced.');
$assert(!preg_match('/\b(pos|sale|payment|inventory|catalog)\b/i', $source), 'POS or business behavior introduced.');
$assert(!str_contains($source, 'ATTR_PERSISTENT => true'), 'Persistent database connections introduced.');

fwrite(STDOUT, sprintf("Persistence Capability and Database Connection Boundary tests passed: %d assertions.\n", $assertions));
