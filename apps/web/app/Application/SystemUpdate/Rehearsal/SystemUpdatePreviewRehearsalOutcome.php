<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Rehearsal;

use App\Application\SystemUpdate\SystemUpdateReleaseIdentity;

// Author by Lab | zefry
final readonly class SystemUpdatePreviewRehearsalOutcome
{
    public function __construct(
        private SystemUpdatePreviewRehearsalPhase $terminalPhase,
        private SystemUpdateReleaseIdentity $activeRelease,
        private string $safeCode,
    ) {
    }

    public function terminalPhase(): SystemUpdatePreviewRehearsalPhase { return $this->terminalPhase; }
    public function activeRelease(): SystemUpdateReleaseIdentity { return $this->activeRelease; }
    public function safeCode(): string { return $this->safeCode; }
}
