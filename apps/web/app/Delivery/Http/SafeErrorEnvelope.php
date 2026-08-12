<?php

namespace App\Delivery\Http;

final class SafeErrorEnvelope
{
    /**
     * @return array{error: array{code: string,message: string,correlation_id: string}}
     */
    public static function make(string $code, string $correlationId): array
    {
        return [
            'error' => [
                'code' => $code,
                'message' => 'The request could not be completed.',
                'correlation_id' => $correlationId,
            ],
        ];
    }

    private function __construct()
    {
    }
}
