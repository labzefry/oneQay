<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationRequest;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingManifestMaterializationController
{
    public function __construct(private readonly FinalShiftCloseRuntimeBindingManifestMaterializer $materializer) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = $request->json()->all();
            if (! is_array($payload)) {
                return response()->json(['error' => 'invalid_request'], 422, $this->headers());
            }

            $keys = array_keys($payload);
            sort($keys, SORT_STRING);
            if ($keys !== ['expected_selection_fingerprint_sha256', 'operation_id']) {
                return response()->json(['error' => 'invalid_request'], 422, $this->headers());
            }

            $result = $this->materializer->materialize(new FinalShiftCloseRuntimeBindingManifestMaterializationRequest(
                (string) ($payload['operation_id'] ?? ''),
                (string) ($payload['expected_selection_fingerprint_sha256'] ?? ''),
            ));

            return response()->json($result, 200, $this->headers());
        } catch (Throwable) {
            return response()->json(['error' => 'materialization_unavailable'], 503, $this->headers());
        }
    }

    /** @return array<string,string> */
    private function headers(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ];
    }
}
