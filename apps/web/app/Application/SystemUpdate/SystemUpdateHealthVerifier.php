<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateHealthVerifier
{
    public function verify(SystemUpdateReleaseIdentity $expectedRelease): SystemUpdateHealthResult;
}
