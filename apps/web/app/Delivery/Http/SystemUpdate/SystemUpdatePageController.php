<?php

declare(strict_types=1);

namespace App\Delivery\Http\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateControlPlane;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final class SystemUpdatePageController
{
    public function __construct(private readonly SystemUpdateControlPlane $controlPlane)
    {
    }

    public function __invoke(): Response
    {
        return Inertia::render('System/UpdateDeployment', [
            'status' => $this->controlPlane->status()->toSafeArray(),
            'ui' => [
                'mode' => 'READ_ONLY',
                'install_action_exposed' => false,
                'check_action_exposed' => false,
                'production_ready' => false,
            ],
        ]);
    }
}
