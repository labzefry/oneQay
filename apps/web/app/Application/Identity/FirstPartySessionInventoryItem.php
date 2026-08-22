<?php

declare(strict_types=1);

namespace App\Application\Identity;

// Author by Lab | zefry
final readonly class FirstPartySessionInventoryItem
{
    public function __construct(
        public string $handle,
        public bool $current,
        public string $organizationId,
        public ?string $outletId,
        public ?string $deviceId,
        public int $issuedAtUnix,
        public int $lastSeenAtUnix,
        public int $expiresAtUnix,
    ) {}

    /** @return array{handle:string,current:bool,organization_id:string,outlet_id:?string,device_id:?string,issued_at_unix:int,last_seen_at_unix:int,expires_at_unix:int} */
    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
            'current' => $this->current,
            'organization_id' => $this->organizationId,
            'outlet_id' => $this->outletId,
            'device_id' => $this->deviceId,
            'issued_at_unix' => $this->issuedAtUnix,
            'last_seen_at_unix' => $this->lastSeenAtUnix,
            'expires_at_unix' => $this->expiresAtUnix,
        ];
    }
}
