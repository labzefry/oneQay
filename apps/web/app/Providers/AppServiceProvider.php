<?php

namespace App\Providers;

use App\Application\Tenancy\TenantContextStore;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            TenantContextStore::class,
            static fn (): TenantContextStore => new RequestTenantContextStore(),
        );
    }

    public function boot(): void
    {
        //
    }
}
