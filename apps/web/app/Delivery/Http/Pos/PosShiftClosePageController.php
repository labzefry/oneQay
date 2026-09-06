<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\FinalShiftClosePermission;
use Inertia\Inertia;
use Inertia\Response;

// Author by Lab | zefry
final readonly class PosShiftClosePageController
{
    public function __construct(
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
    ) {}

    public function __invoke(): Response
    {
        try {
            $this->authorization->require(
                $this->contexts->current(),
                FinalShiftClosePermission::identifier(),
            );
        } catch (DurableAuthorizationViolation) {
            abort(403, 'Final Shift Close access denied.');
        }

        return Inertia::render('Pos/ShiftClose', [
            'delivery' => [
                'post_url' => '/pos/shifts/close',
                'operation_id_prefix' => 'shift-close-',
                'production_ready' => false,
                'activation_state' => 'DORMANT_FAIL_CLOSED',
            ],
        ]);
    }
}
