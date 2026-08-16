<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use JsonException;

// Author by Lab | zefry
final readonly class SystemUpdateSharedConfigurationCompatibility
{
    /** @param array<string, string> $requiredSecrets */
    public function __construct(
        private bool $compatible,
        private string $safeCode,
        private SystemUpdateSharedRuntimeConfiguration $configuration,
        private array $requiredSecrets,
        private int $checkedAtUnix,
    ) {
    }

    public function compatible(): bool
    {
        return $this->compatible;
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }

    public function checkedAtUnix(): int
    {
        return $this->checkedAtUnix;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        $payload = [
            'status' => $this->compatible ? 'COMPATIBLE' : 'INCOMPATIBLE',
            'safe_code' => $this->safeCode,
            'configuration' => $this->configuration->toSafeArray(),
            'required_secrets' => $this->requiredSecrets,
            'checked_at_unix' => $this->checkedAtUnix,
        ];

        try {
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException) {
            $encoded = 'shared-configuration-unencodable';
        }

        $payload['safe_fingerprint'] = hash('sha256', $encoded);

        return $payload;
    }
}
