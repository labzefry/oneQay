<?php
declare(strict_types=1);

use App\Infrastructure\Configuration\CriticalConfiguration;
use App\Infrastructure\Runtime\GovernedBusinessRuntimePolicy;

require_once __DIR__.'/../app/Infrastructure/Runtime/GovernedBusinessRuntimePolicy.php';
require_once __DIR__.'/../app/Infrastructure/Configuration/CriticalConfiguration.php';

// Author by Lab | zefry
function sprint216Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint216 regression failed: {$message}\n");
        exit(1);
    }
}

foreach (['local', 'test', 'ci'] as $runtime) {
    sprint216Assert(GovernedBusinessRuntimePolicy::allows($runtime, false), "{$runtime} existing eligibility must remain");
}
foreach (['durable-staging', 'production'] as $runtime) {
    sprint216Assert(! GovernedBusinessRuntimePolicy::allows($runtime, false), "{$runtime} must fail closed by default");
    sprint216Assert(GovernedBusinessRuntimePolicy::allows($runtime, true), "{$runtime} requires explicit governed enablement");
    sprint216Assert(GovernedBusinessRuntimePolicy::isGovernedRuntime($runtime), "{$runtime} must be governed");
}
foreach (['preview', 'synthetic-preview', 'prod', '', 'unknown'] as $runtime) {
    sprint216Assert(! GovernedBusinessRuntimePolicy::allows($runtime, true), "{$runtime} must not enter business routes");
}
foreach (['durable-staging', 'production'] as $runtime) {
    sprint216Assert(CriticalConfiguration::isReady([
        'app_key' => 'base64:'.str_repeat('a', 44),
        'runtime_class' => $runtime,
        'app_debug' => false,
        'app_env' => $runtime === 'production' ? 'production' : 'staging',
    ]), "{$runtime} must be health-ready when critical configuration is valid");
}
sprint216Assert(! CriticalConfiguration::isReady([
    'app_key' => 'base64:'.str_repeat('a', 44),
    'runtime_class' => 'production',
    'app_debug' => true,
    'app_env' => 'production',
]), 'Production debug must remain rejected');

fwrite(STDOUT, "Sprint216 governed business runtime policy regression passed.\n");
