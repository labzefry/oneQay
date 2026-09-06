<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\ExpectedCashRepository;
use App\Application\Pos\ExpectedCashResult;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use OverflowException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelExpectedCashRepository implements ExpectedCashRepository
{
    private LaravelExpectedCashSnapshotReader $snapshotReader;

    public function __construct(private Connection $connection)
    {
        $this->snapshotReader = new LaravelExpectedCashSnapshotReader($connection);
    }

    public function deriveFrom(ShiftClosingCashResult $closingCashEvidence): ExpectedCashResult
    {
        if ($this->connection->transactionLevel() !== 0) {
            throw new PosTransactionViolation();
        }

        $driver = strtolower(trim($this->connection->getDriverName()));
        if ($driver === 'mysql') {
            $this->connection->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        } elseif ($driver !== 'sqlite') {
            throw new PosTransactionViolation();
        }

        try {
            return $this->connection->transaction(
                fn (): ExpectedCashResult => $this->snapshotReader->deriveFrom($closingCashEvidence),
                1,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException|OverflowException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }
}
