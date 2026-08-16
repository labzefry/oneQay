<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

// Author by Lab | zefry
final class SystemUpdateSharedConfigurationPolicy
{
    /** @var list<string> */
    private const RUNTIME_CLASSES = ['local', 'test', 'ci', 'preview'];

    /** @var list<string> */
    private const APP_ENVIRONMENTS = ['local', 'testing', 'test', 'ci', 'preview'];

    public function evaluate(
        SystemUpdateSharedRuntimeConfiguration $configuration,
        SystemUpdateSecretPresenceProbe $secretProbe,
        int $nowUnix,
    ): SystemUpdateSharedConfigurationCompatibility {
        $secret = SystemUpdateSecretReference::appKey();
        $secretPresent = $secretProbe->available($secret);
        $secretStates = [$secret->safeName() => $secretPresent ? 'PRESENT' : 'MISSING'];

        $safeCode = $this->violation($configuration, $secretPresent);

        return new SystemUpdateSharedConfigurationCompatibility(
            $safeCode === null,
            $safeCode ?? 'shared_configuration_compatible',
            $configuration,
            $secretStates,
            $nowUnix,
        );
    }

    private function violation(
        SystemUpdateSharedRuntimeConfiguration $configuration,
        bool $appKeyPresent,
    ): ?string {
        if (
            $configuration->profile() !== SystemUpdateSharedRuntimeConfiguration::PROFILE
            || $configuration->layoutVersion() !== SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION
        ) {
            return 'shared_configuration_profile_unsupported';
        }

        if (! in_array($configuration->runtimeClass(), self::RUNTIME_CLASSES, true)) {
            return 'shared_runtime_class_invalid';
        }

        if (! in_array($configuration->appEnvironment(), self::APP_ENVIRONMENTS, true)) {
            return 'shared_app_environment_invalid';
        }

        if (
            $configuration->debugEnabled()
            && ! in_array($configuration->appEnvironment(), ['local', 'testing', 'test'], true)
        ) {
            return 'shared_debug_policy_invalid';
        }

        $url = $configuration->appUrl();
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = parse_url($url, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! is_string($host) || trim($host) === '') {
            return 'shared_app_url_invalid';
        }

        if ($configuration->runtimeClass() === 'preview' && $scheme !== 'https') {
            return 'shared_preview_https_required';
        }

        foreach ([
            $configuration->logChannel(),
            $configuration->sessionDriver(),
            $configuration->cacheStore(),
        ] as $token) {
            if (preg_match('/\A[a-z0-9][a-z0-9._-]{0,63}\z/', $token) !== 1) {
                return 'shared_runtime_driver_invalid';
            }
        }

        if (! $appKeyPresent) {
            return 'shared_secret_missing';
        }

        return null;
    }
}
