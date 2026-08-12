<?php

namespace App\Delivery\Http;

use App\Infrastructure\Configuration\CriticalConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HealthController
{
    public function live(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'oneqay-web',
            'correlation_id' => self::correlationId($request),
        ]);
    }

    public function ready(Request $request): JsonResponse
    {
        $ready = CriticalConfiguration::isReady([
            'app_key' => config('app.key'),
            'runtime_class' => config('oneqay.runtime_class'),
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
        ]);

        return response()->json([
            'status' => $ready ? 'ready' : 'unavailable',
            'service' => 'oneqay-web',
            'correlation_id' => self::correlationId($request),
        ], $ready ? 200 : 503);
    }

    private static function correlationId(Request $request): string
    {
        $value = $request->attributes->get('oneqay.correlation_id');

        return is_string($value) ? $value : 'unavailable';
    }
}
