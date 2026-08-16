<?php

declare(strict_types=1);

namespace App\Delivery\Http\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateControlPlane;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use Illuminate\Http\JsonResponse;

// Author by Lab | zefry
final class SystemUpdateControlPlaneController
{
    public function __construct(private readonly SystemUpdateControlPlane $controlPlane)
    {
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'data' => $this->controlPlane->status()->toSafeArray(),
        ]);
    }

    public function check(): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->controlPlane->checkAvailability()->toSafeArray(),
            ]);
        } catch (SystemUpdateControlPlaneViolation $violation) {
            return $this->denied($violation);
        }
    }

    public function install(): JsonResponse
    {
        try {
            $this->controlPlane->requestInstall(null);
        } catch (SystemUpdateControlPlaneViolation $violation) {
            return $this->denied($violation);
        }
    }

    private function denied(SystemUpdateControlPlaneViolation $violation): JsonResponse
    {
        $status = match ($violation->safeCode()) {
            'privileged_authorization_required' => 403,
            'invalid_state_transition' => 409,
            'activation_not_implemented' => 501,
            default => 423,
        };

        return response()->json([
            'error' => [
                'code' => $violation->safeCode(),
                'message' => 'System update control plane request denied.',
            ],
        ], $status);
    }
}
