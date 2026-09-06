<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use Illuminate\Database\Connection;
use RuntimeException;

// Author by Lab | zefry
final class LaravelFinalShiftCloseRuntimeDatabaseIdentityReader implements FinalShiftCloseRuntimeDatabaseIdentityReader
{
    private const MIGRATION = '0000_00_00_000027_create_pos_shift_close_evidence_foundation';

    public function __construct(private readonly Connection $connection) {}

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        if (strtolower($this->connection->getDriverName()) !== 'mysql') {
            throw new RuntimeException('Runtime database binding requires MySQL-compatible persistence.');
        }

        $schema = $this->connection->getSchemaBuilder();
        if (! $schema->hasTable('migrations')) {
            throw new RuntimeException('Runtime database migrations ledger is unavailable.');
        }
        if ($schema->hasTable('oneqay_pos_shift_close_evidence')) {
            throw new RuntimeException('Migration27 table already exists; runtime binding attestation is retrospective.');
        }

        $recordCount = (int) $this->connection
            ->table('migrations')
            ->where('migration', self::MIGRATION)
            ->count();
        if ($recordCount !== 0) {
            throw new RuntimeException('Migration27 is already recorded; runtime binding attestation is retrospective.');
        }

        $identity = $this->connection->selectOne(
            'SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port',
        );
        if (! is_object($identity)) {
            throw new RuntimeException('Runtime database identity readback failed.');
        }

        return new FinalShiftCloseRuntimeDatabaseIdentity(
            trim((string) ($identity->database_name ?? '')),
            trim((string) ($identity->server_hostname ?? '')),
            (int) ($identity->server_port ?? 0),
        );
    }
}
