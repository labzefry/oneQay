<?php

namespace App\Providers;

use App\Application\SystemUpdate\Security\PrivilegedUpdateSecurityPolicy;
use App\Application\Tenancy\TenantContextStore;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
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
        RateLimiter::for('privileged-update', static function (Request $request): array {
            $networkFingerprint = hash(
                'sha256',
                'oneqay:privileged-update:'.(string) ($request->ip() ?? 'unknown'),
            );

            return [
                Limit::perMinute(PrivilegedUpdateSecurityPolicy::RATE_LIMIT_PER_MINUTE)
                    ->by($networkFingerprint.':minute'),
                Limit::perHour(PrivilegedUpdateSecurityPolicy::RATE_LIMIT_PER_HOUR)
                    ->by($networkFingerprint.':hour'),
            ];
        });
    }
}
