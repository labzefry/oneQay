<?php

namespace App\Infrastructure\Persistence;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPersistenceTransaction implements PersistenceTransaction
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {
    }

    public function run(callable $operation): mixed
    {
        $this->assertRuntimeAllowed();

        try {
            return $this->connection->transaction($operation, 1);
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::TRANSACTION_FAILURE,
                'Durable persistence transaction failed.',
            );
        }
    }

    private function assertRuntimeAllowed(): void
    {
        if (! $this->enabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'Durable persistence runtime is not authorized.',
            );
        }
    }
}
