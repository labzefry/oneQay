<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\CloseShift;
use App\Application\Pos\CloseShiftRepository;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\FinalShiftCloseAuthorizationPolicy;
use App\Application\Pos\ShiftCloseClock;
use App\Infrastructure\Pos\LaravelCloseShiftRepository;
use App\Infrastructure\Pos\LaravelExpectedCashSnapshotReader;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class FinalShiftCloseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(LaravelExpectedCashSnapshotReader::class, function ($app): LaravelExpectedCashSnapshotReader {
            return new LaravelExpectedCashSnapshotReader($this->connection($app));
        });

        $this->app->scoped(DeriveCashVariance::class, static fn (): DeriveCashVariance => new DeriveCashVariance());
        $this->app->scoped(FinalShiftCloseAuthorizationPolicy::class, static fn (): FinalShiftCloseAuthorizationPolicy => new FinalShiftCloseAuthorizationPolicy());

        $this->app->scoped(CloseShiftRepository::class, function ($app): CloseShiftRepository {
            return new LaravelCloseShiftRepository(
                $this->connection($app),
                $app->make(LaravelExpectedCashSnapshotReader::class),
                $app->make(DeriveCashVariance::class),
                $app->make(FinalShiftCloseAuthorizationPolicy::class),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->featureEnabled(),
            );
        });

        $this->app->scoped(CloseShift::class, fn ($app): CloseShift => new CloseShift(
            $app->make(CloseShiftRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
            $app->make(PersistenceTransaction::class),
            $app->make(ShiftCloseClock::class),
        ));

        $this->app->scoped(ShiftCloseClock::class, static fn (): ShiftCloseClock => new class implements ShiftCloseClock {
            public function nowUnix(): int
            {
                return time();
            }
        });
    }

    private function connection($app): Connection
    {
        /** @var Connection $connection */
        $connection = $app->make('db')->connection();

        return $connection;
    }

    private function persistenceEnabled(): bool
    {
        return (bool) config('database.oneqay_persistence_enabled', false);
    }

    private function runtimeClass(): string
    {
        return (string) config('oneqay.runtime_class', '');
    }

    private function featureEnabled(): bool
    {
        return filter_var(env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false), FILTER_VALIDATE_BOOL);
    }
}
