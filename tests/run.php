<?php

declare(strict_types=1);

require __DIR__ . '/../src/Auth/Foundation.php';
require __DIR__ . '/../src/Tenant/Foundation.php';
require __DIR__ . '/../src/Authorization/Foundation.php';
require __DIR__ . '/../src/Configuration/Foundation.php';
require __DIR__ . '/../src/Http/ErrorEnvelope.php';

use OneQay\Auth\ArraySessionStore;
use OneQay\Auth\AuthenticationService;
use OneQay\Auth\InMemoryUserProvider;
use OneQay\Auth\NativePasswordHasher;
use OneQay\Auth\SessionGuard;
use OneQay\Auth\User;
use OneQay\Authorization\AuthorizationContext;
use OneQay\Authorization\AuthorizationException;
use OneQay\Authorization\AuthorizationService;
use OneQay\Authorization\AuthorizationSubject;
use OneQay\Authorization\DenyByDefaultPolicy;
use OneQay\Authorization\ExplicitGrantPolicy;
use OneQay\Authorization\PermissionIdentifier;
use OneQay\Configuration\ArrayConfigurationSource;
use OneQay\Configuration\ConfigurationException;
use OneQay\Configuration\ConfigurationKey;
use OneQay\Configuration\EnvironmentIdentifier;
use OneQay\Configuration\EnvironmentVariableConfigurationSource;
use OneQay\Configuration\SecretValue;
use OneQay\Configuration\StartupConfigurationValidator;
use OneQay\Http\ErrorEnvelope;
use OneQay\Tenant\SessionTenantContextResolver;
use OneQay\Tenant\TenantIdentifier;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$throwsAuthorizationCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (AuthorizationException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

$throwsConfigurationCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (ConfigurationException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

// Authentication, Tenant Context, and Authorization regressions.
$hasher = new NativePasswordHasher();
$hash = $hasher->hash('Correct-Horse-2026');
$assert($hasher->verify('Correct-Horse-2026', $hash), 'Authentication password regression failed.');
$users = new InMemoryUserProvider([new User('user-001', 'owner@example.test', $hash)]);
$session = new ArraySessionStore();
$guard = new SessionGuard($session, $users);
$auth = new AuthenticationService($users, $hasher, $guard);
$tenant = new SessionTenantContextResolver($session, $guard);
$fingerprint = 'ua|127.0.0.1';

$unauthorized = new AuthorizationService($guard, $tenant, new DenyByDefaultPolicy());
$throwsAuthorizationCode(
    fn () => $unauthorized->subject($fingerprint),
    AuthorizationService::AUTHENTICATION_REQUIRED
);

$login = $auth->login('owner@example.test', 'Correct-Horse-2026', $fingerprint);
$assert($login->isAuthenticated, 'Authentication regression failed.');
$throwsAuthorizationCode(
    fn () => $unauthorized->subject($fingerprint),
    AuthorizationService::TENANT_REQUIRED
);
$tenant->select('tenant_alpha', $fingerprint);
$assert(
    $tenant->requireContext($fingerprint)->tenantId->value === 'tenant_alpha',
    'Tenant Context regression failed.'
);

$permission = new PermissionIdentifier(' FOUNDATION.READ ');
$assert($permission->value === 'foundation.read', 'Permission normalization failed.');
foreach (['', 'read', '/foundation/read', 'tenant_alpha.foundation.read'] as $invalidPermission) {
    try {
        new PermissionIdentifier($invalidPermission);
        $assert(false, 'Invalid permission accepted.');
    } catch (InvalidArgumentException) {
        $assert(true, 'Invalid permission rejected.');
    }
}

$subject = $unauthorized->subject($fingerprint);
$context = new AuthorizationContext($subject, $permission, 'corr-authz-001');
$assert(!$unauthorized->evaluate($context, $fingerprint)->isAllowed, 'Deny-by-default regression failed.');

$grants = new ExplicitGrantPolicy();
$grants->grant('user-001', new TenantIdentifier('tenant_alpha'), $permission);
$authorization = new AuthorizationService($guard, $tenant, $grants);
$assert($authorization->evaluate($context, $fingerprint)->isAllowed, 'Explicit grant regression failed.');

$crossTenant = new AuthorizationContext(
    new AuthorizationSubject('user-001', new TenantIdentifier('tenant_beta')),
    $permission,
    'corr-authz-002'
);
$throwsAuthorizationCode(
    fn () => $authorization->evaluate($crossTenant, $fingerprint),
    AuthorizationService::CROSS_TENANT_DENIED
);

// Environment and Configuration Key value objects.
$environment = new EnvironmentIdentifier(' PREVIEW ');
$assert($environment->value === 'preview', 'Environment was not normalized.');
$assert($environment->isRestricted(), 'Preview environment was not classified as restricted.');
$throwsConfigurationCode(
    fn () => new EnvironmentIdentifier('staging'),
    ConfigurationException::INVALID
);

$key = new ConfigurationKey('app_env');
$assert($key->value === 'APP_ENV', 'Configuration key was not canonicalized.');
foreach (['', ' APP_ENV', 'APP ENV', 'APP-ENV', '_APP_ENV'] as $invalidKey) {
    $throwsConfigurationCode(
        fn () => new ConfigurationKey($invalidKey),
        ConfigurationException::INVALID
    );
}

$syntheticSecret = hash('sha256', 'oneqay-sprint-06-synthetic-secret');
$source = new ArrayConfigurationSource([
    'APP_ENV' => 'test',
    'APP_DEBUG' => false,
    'SESSION_SECURE' => true,
    'APP_KEY' => $syntheticSecret,
    'OPTIONAL_VALUE' => 'present',
    'EMPTY_VALUE' => '',
    'BOOLEAN_TRUE' => 'yes',
    'BOOLEAN_FALSE' => 'off',
    'BOOLEAN_INVALID' => 'sometimes',
]);

$assert(
    $source->requiredString(new ConfigurationKey('APP_ENV')) === 'test',
    'Required string could not be read.'
);
$throwsConfigurationCode(
    fn () => $source->requiredString(new ConfigurationKey('MISSING_VALUE')),
    ConfigurationException::REQUIRED
);
$throwsConfigurationCode(
    fn () => $source->requiredString(new ConfigurationKey('EMPTY_VALUE')),
    ConfigurationException::EMPTY
);
$assert(
    $source->optionalString(new ConfigurationKey('MISSING_OPTIONAL'), 'fallback') === 'fallback',
    'Optional string did not use its explicit default.'
);
$assert(
    $source->optionalString(new ConfigurationKey('OPTIONAL_VALUE'), 'fallback') === 'present',
    'Optional string ignored its configured value.'
);
$assert($source->requiredBoolean(new ConfigurationKey('BOOLEAN_TRUE')), 'True boolean parsing failed.');
$assert(!$source->requiredBoolean(new ConfigurationKey('BOOLEAN_FALSE')), 'False boolean parsing failed.');
$assert(
    $source->optionalBoolean(new ConfigurationKey('MISSING_BOOLEAN'), true),
    'Optional boolean did not use its explicit default.'
);
$throwsConfigurationCode(
    fn () => $source->requiredBoolean(new ConfigurationKey('BOOLEAN_INVALID')),
    ConfigurationException::INVALID
);

// Secret protection and leakage-negative checks.
$secret = $source->secret(new ConfigurationKey('APP_KEY'));
$assert($secret instanceof SecretValue, 'Secret wrapper was not returned.');
$assert($secret->reveal() === $syntheticSecret, 'Explicit secret reveal failed.');
$assert((string) $secret === '[REDACTED]', 'Direct string conversion was not redacted.');
$assert($secret->redacted() === '[REDACTED]', 'Redacted representation changed.');
$assert(json_encode($secret) === '"[REDACTED]"', 'JSON serialization was not redacted.');
$assert(!str_contains(serialize($secret), $syntheticSecret), 'Serialized secret leaked its raw value.');
ob_start();
var_dump($secret);
$debugOutput = (string) ob_get_clean();
$assert(!str_contains($debugOutput, $syntheticSecret), 'Debug output leaked the raw secret.');
$assert(!str_contains(print_r($secret, true), $syntheticSecret), 'Print output leaked the raw secret.');
$assert(!str_contains(var_export($secret, true), $syntheticSecret), 'Exported state leaked the raw secret.');
$throwsConfigurationCode(
    fn () => (new ArrayConfigurationSource([]))->secret(new ConfigurationKey('APP_KEY')),
    ConfigurationException::SECRET_REQUIRED
);

// Environment-variable adapter uses trusted process environment only.
putenv('ONEQAY_TEST_FLAG=true');
$environmentSource = new EnvironmentVariableConfigurationSource();
$assert(
    $environmentSource->requiredBoolean(new ConfigurationKey('ONEQAY_TEST_FLAG')),
    'Environment-variable adapter failed.'
);
putenv('ONEQAY_TEST_FLAG');
$throwsConfigurationCode(
    fn () => $environmentSource->requiredString(new ConfigurationKey('ONEQAY_TEST_FLAG')),
    ConfigurationException::REQUIRED
);

// Safe startup validation.
$validator = new StartupConfigurationValidator();
$safeStartup = $validator->validate(new ArrayConfigurationSource([
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,
    'SESSION_SECURE' => true,
    'APP_KEY' => $syntheticSecret,
]));
$assert($safeStartup->isValid && $safeStartup->errorCodes === [], 'Safe startup configuration was rejected.');

$previewDebug = $validator->validate(new ArrayConfigurationSource([
    'APP_ENV' => 'preview',
    'APP_DEBUG' => true,
    'SESSION_SECURE' => true,
    'APP_KEY' => $syntheticSecret,
]));
$assert(
    !$previewDebug->isValid
        && in_array(ConfigurationException::ENVIRONMENT_UNSAFE, $previewDebug->errorCodes, true),
    'Preview environment accepted debug mode.'
);

$productionInsecure = $validator->validate(new ArrayConfigurationSource([
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,
    'SESSION_SECURE' => false,
    'APP_KEY' => $syntheticSecret,
]));
$assert(
    !$productionInsecure->isValid
        && in_array(ConfigurationException::ENVIRONMENT_UNSAFE, $productionInsecure->errorCodes, true),
    'Production environment accepted an insecure session.'
);

$missingSecret = $validator->validate(new ArrayConfigurationSource([
    'APP_ENV' => 'test',
    'APP_DEBUG' => false,
    'SESSION_SECURE' => true,
]));
$assert(
    !$missingSecret->isValid
        && in_array(ConfigurationException::SECRET_REQUIRED, $missingSecret->errorCodes, true),
    'Missing startup secret was not rejected safely.'
);

$invalidEnvironment = $validator->validate(new ArrayConfigurationSource([
    'APP_ENV' => 'staging',
    'APP_DEBUG' => false,
    'SESSION_SECURE' => true,
    'APP_KEY' => $syntheticSecret,
]));
$assert(
    !$invalidEnvironment->isValid
        && in_array(ConfigurationException::INVALID, $invalidEnvironment->errorCodes, true),
    'Invalid environment was not rejected.'
);

// Safe error envelope does not carry secrets or environment dumps.
$envelope = new ErrorEnvelope(
    ConfigurationException::ENVIRONMENT_UNSAFE,
    'Konfigurasi lingkungan tidak aman.',
    'corr-config-001'
);
$encodedEnvelope = json_encode($envelope->toArray(), JSON_THROW_ON_ERROR);
$assert(!str_contains($encodedEnvelope, $syntheticSecret), 'Error envelope leaked the synthetic secret.');
$assert(
    str_contains($encodedEnvelope, ConfigurationException::ENVIRONMENT_UNSAFE),
    'Configuration error code is missing from the envelope.'
);

$configurationSource = file_get_contents(__DIR__ . '/../src/Configuration/Foundation.php');
$assert(
    is_string($configurationSource)
        && !preg_match('/\b(pos|sale|payment|inventory|catalog)\b/i', $configurationSource),
    'POS or business behavior was introduced into Configuration Foundation.'
);

fwrite(
    STDOUT,
    sprintf(
        "Authentication, Tenant Context, Authorization, and Configuration Boundary tests passed: %d assertions.\n",
        $assertions
    )
);
