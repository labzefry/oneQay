<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalEvidence;
use App\Application\SystemUpdate\Rehearsal\SystemUpdatePreviewRehearsalEvidenceStore;
use App\Infrastructure\SystemUpdate\Activation\SystemUpdateAtomicJsonFile;

// Author by Lab | zefry
final readonly class FilesystemSystemUpdatePreviewRehearsalEvidenceStore implements SystemUpdatePreviewRehearsalEvidenceStore
{
    public function __construct(
        private string $privateRoot,
        private SystemUpdateAtomicJsonFile $json,
    ) {
    }

    public function persist(SystemUpdatePreviewRehearsalEvidence $evidence): void
    {
        $payload = $evidence->toSafeArray();
        $operationId = (string) ($payload['operation_id'] ?? 'invalid');

        $this->json->write(
            rtrim($this->privateRoot, '/').'/deployment-state/m7-6-rehearsals/'.$operationId.'.json',
            $payload,
        );
    }
}
