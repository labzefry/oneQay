<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class FinalShiftCloseRuntimeDatabaseIdentity
{
    public const ALGORITHM = 'SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1';

    public function __construct(
        private string $databaseName,
        private string $serverHostname,
        private int $serverPort,
    ) {
        if (trim($this->databaseName) === '' || trim($this->serverHostname) === '') {
            throw new InvalidArgumentException('Runtime database identity is incomplete.');
        }

        if ($this->serverPort < 1 || $this->serverPort > 65535) {
            throw new InvalidArgumentException('Runtime database identity port is invalid.');
        }
    }

    /**
     * @return array{database_name:string,server_hostname:string,server_port:int}
     */
    public function canonicalPayload(): array
    {
        $payload = [
            'database_name' => $this->databaseName,
            'server_hostname' => $this->serverHostname,
            'server_port' => $this->serverPort,
        ];

        ksort($payload, SORT_STRING);

        return $payload;
    }

    public function fingerprintSha256(): string
    {
        return hash('sha256', json_encode(
            $this->canonicalPayload(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ));
    }
}
