<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use Illuminate\Http\JsonResponse;
use Throwable;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeDbBindingAttestationController
{
    public function __construct(
        private readonly FinalShiftCloseRuntimeDbBindingAttestation $attestation,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            return response()->json($this->attestation->attest(), 200, [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        } catch (Throwable) {
            return response()->json([
                'error' => 'RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE',
            ], 503, [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }
    }
}
