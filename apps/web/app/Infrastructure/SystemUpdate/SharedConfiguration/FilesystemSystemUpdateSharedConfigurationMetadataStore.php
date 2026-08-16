<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationCompatibility;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationMetadataStore;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;
use App\Infrastructure\SystemUpdate\Activation\SystemUpdateAtomicJsonFile;

// Author by Lab | zefry
final readonly class FilesystemSystemUpdateSharedConfigurationMetadataStore implements SystemUpdateSharedConfigurationMetadataStore
{
    public function __construct(
        private string $privateRoot,
        private SystemUpdateAtomicJsonFile $json,
    ) {
    }

    public function record(
        SystemUpdatePreparedRelease $release,
        SystemUpdateSharedConfigurationCompatibility $compatibility,
    ): void {
        $this->json->write(
            rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/deployment-state/shared-configuration.json',
            [
                'schema_version' => 1,
                'operation_id' => $release->operationId(),
                'release_id' => $release->identity()->releaseId(),
                'source_commit' => $release->identity()->sourceCommit(),
                'compatibility' => $compatibility->toSafeArray(),
                'attribution' => 'Lab | zefry',
            ],
        );
    }
}
