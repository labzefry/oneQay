<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosOperationalSalesSummaryRepository;
use App\Application\Pos\ViewPosOperationalSalesSummary;
use App\Infrastructure\Pos\LaravelPosOperationalSalesSummaryRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosOperationalSalesSummaryRepository::class, function ($app): PosOperationalSalesSummaryRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosOperationalSalesSummaryRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('oneqay.pos_operational_reporting.enabled', false),
            );
        });

        $this->app->scoped(ViewPosOperationalSalesSummary::class, fn ($app): ViewPosOperationalSalesSummary => new ViewPosOperationalSalesSummary(
            $app->make(PosOperationalSalesSummaryRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));
    }
}