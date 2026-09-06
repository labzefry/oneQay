<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Pos\FinalShiftCloseRuntimeBindingMaterializationRequest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingMaterializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingMaterializationController
{
    public function __construct(
        private readonly FinalShiftCloseRuntimeBindingMaterializer $materializer,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = $request->json()->all();
            if (! is_array($payload)) {
                throw new \RuntimeException('Invalid JSON object.');
            }

            $command = new FinalShiftCloseRuntimeBindingMaterializationRequest($payload);
            $receipt = $this->materializer->materialize($command);

            return response()->json($receipt, 202, [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        } catch (Throwable) {
            return response()->json([
                'error' => [
                    'code' => 'runtime_binding_materialization_unavailable',
                    'message' => 'Runtime binding materialization is unavailable.',
                ],
            ], 503, [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }
    }
}
