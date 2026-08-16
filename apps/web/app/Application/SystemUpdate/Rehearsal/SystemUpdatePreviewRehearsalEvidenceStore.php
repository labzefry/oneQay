<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

// Author by Lab | zefry
interface SystemUpdatePreviewRehearsalEvidenceStore
{
    public function persist(SystemUpdatePreviewRehearsalEvidence $evidence): void;
}
