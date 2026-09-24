<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final class FinalShiftCloseDurableStagingDeliveryGate
{
    public const SUCCESSOR_MARKER = 'DURABLE_STAGING_DELIVERY_SUCCESSOR_V1';

    private const RUNTIME_CLASS = 'durable-staging';

    private const RELEASE_ENVIRONMENT = 'DURABLE_STAGING';

    /**
     * @param array<string, mixed> $release
     */
    public function allows(
        array $release,
        string $runtimeClass,
        string $runningSourceCommit,
        string $runningArtifactSha256,
        bool $persistenceEnabled,
        bool $sessionControlEnabled,
        bool $saleCompletionEnabled,
        bool $featureEnabled,
    ): bool {
        if (
            ! $persistenceEnabled
            || ! $sessionControlEnabled
            || ! $saleCompletionEnabled
            || ! $featureEnabled
        ) {
            return false;
        }

        if (strtolower(trim($runtimeClass)) !== self::RUNTIME_CLASS) {
            return false;
        }

        $sourceCommit = strtolower(trim((string) ($release['source_commit'] ?? '')));
        $releaseId = trim((string) ($release['release_id'] ?? ''));

        if (
            ($release['product'] ?? null) !== 'oneQay'
            || ($release['environment'] ?? null) !== self::RELEASE_ENVIRONMENT
            || ($release['required_runtime_class'] ?? null) !== self::RUNTIME_CLASS
            || ($release['production'] ?? null) !== false
            || ($release['synthetic_fixture_runtime'] ?? null) !== false
            || ($release['production_data_allowed'] ?? null) !== false
            || ! preg_match('/^[0-9a-f]{40}$/', $sourceCommit)
            || ! preg_match('/^durable-staging-[0-9a-f]{12}$/', $releaseId)
            || $releaseId !== 'durable-staging-'.substr($sourceCommit, 0, 12)
        ) {
            return false;
        }

        $runningSourceCommit = strtolower(trim($runningSourceCommit));
        $runningArtifactSha256 = strtolower(trim($runningArtifactSha256));

        if (
            ! preg_match('/^[0-9a-f]{40}$/', $runningSourceCommit)
            || ! hash_equals($sourceCommit, $runningSourceCommit)
            || ! preg_match('/^[0-9a-f]{64}$/', $runningArtifactSha256)
        ) {
            return false;
        }

        return true;
    }
}
