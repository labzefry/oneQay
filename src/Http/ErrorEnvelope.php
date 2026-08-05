<?php

declare(strict_types=1);

namespace OneQay\Http;

final readonly class ErrorEnvelope
{
    /** @param array<string, mixed> $details */
    public function __construct(
        public string $code,
        public string $message,
        public string $correlationId,
        public array $details = [],
    ) {
        if ($this->code === '' || $this->message === '' || $this->correlationId === '') {
            throw new \InvalidArgumentException('Error envelope fields are required.');
        }
    }

    /** @return array{error: array{code: string, message: string, correlation_id: string, details: array<string, mixed>}} */
    public function toArray(): array
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'correlation_id' => $this->correlationId,
                'details' => $this->details,
            ],
        ];
    }
}
