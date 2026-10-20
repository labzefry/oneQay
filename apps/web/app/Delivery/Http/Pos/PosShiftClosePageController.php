<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\ViewShiftClosureWorkspace;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final readonly class PosShiftClosePageController
{
    public function __construct(
        private ViewShiftClosureWorkspace $workspace,
    ) {}

    public function __invoke(): Response
    {
        try {
            $state = $this->workspace->view();
        } catch (DurableAuthorizationViolation) {
            abort(403, 'Final Shift Close access denied.');
        }

        return Inertia::render('Pos/ShiftClose', [
            'workspace' => $state + ['reconciliation_available' => Route::has('pos.shifts.reconciliation.workspace')],
            'delivery' => [
                'post_url' => '/pos/shifts/close',
                'operation_id_prefix' => 'shift-close-',
                'production_ready' => false,
                'activation_state' => 'DORMANT_FAIL_CLOSED',
            ],
        ]);
    }
}
