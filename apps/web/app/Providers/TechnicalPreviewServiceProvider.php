<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Application\Pos\SyntheticPosStore;
use App\Application\Preview\PreviewFixtureGateway;
use App\Application\Preview\TechnicalPreviewRuntimePolicy;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Preview\DeterministicPreviewFixture;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class TechnicalPreviewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            PreviewFixtureGateway::class,
            static fn (): PreviewFixtureGateway => new DeterministicPreviewFixture(),
        );

        $this->app->scoped(
            SyntheticPosStore::class,
            fn (): SyntheticPosStore => $this->app->make(PreviewFixtureGateway::class),
        );

        $this->app->scoped(
            TenantMembershipVerifier::class,
            static fn (): TenantMembershipVerifier => new SyntheticTenantMembershipVerifier([
                'synthetic-principal-a' => ['tenant-alpha'],
                'synthetic-principal-b' => ['tenant-beta'],
                'synthetic-principal-reviewer-a' => ['tenant-alpha'],
                'synthetic-principal-reviewer-b' => ['tenant-beta'],
            ]),
        );

        $this->app->scoped(
            OrganizationalRelationshipVerifier::class,
            static fn (): OrganizationalRelationshipVerifier => new SyntheticOrganizationalRelationshipVerifier([
                'synthetic-principal-a' => [[
                    'tenant' => 'tenant-alpha',
                    'organization' => 'organization-alpha',
                    'outlet' => 'outlet-alpha',
                    'device' => 'device-alpha',
                ]],
                'synthetic-principal-b' => [[
                    'tenant' => 'tenant-beta',
                    'organization' => 'organization-beta',
                    'outlet' => 'outlet-beta',
                    'device' => 'device-beta',
                ]],
                'synthetic-principal-reviewer-a' => [[
                    'tenant' => 'tenant-alpha',
                    'organization' => 'organization-alpha',
                    'outlet' => 'outlet-alpha',
                    'device' => 'device-alpha-reviewer',
                ]],
                'synthetic-principal-reviewer-b' => [[
                    'tenant' => 'tenant-beta',
                    'organization' => 'organization-beta',
                    'outlet' => 'outlet-beta',
                    'device' => 'device-beta-reviewer',
                ]],
            ]),
        );

        $this->app->scoped(
            OrganizationalContextStore::class,
            static fn (): OrganizationalContextStore => new RequestOrganizationalContextStore(),
        );
    }

    public function boot(): void
    {
        if (! $this->selectedPreviewRuntimePermitted()) {
            $this->removeDeniedPreviewRoutesAfterApplicationBoot();
            return;
        }

        $runtimeClass = strtolower(trim((string) config('technical-preview.runtime_class', '')));

        if ($runtimeClass === 'preview') {
            $this->applyDeployedPreviewSessionEnvelope();
        }

        // Preserve the current controller-facing configuration contract while
        // keeping Preview-specific environment reads isolated in its own
        // config-cacheable file.
        config([
            'oneqay.technical_preview.enabled' => true,
            'oneqay.runtime_class' => $runtimeClass,
        ]);

        $this->loadRoutesFrom(base_path('routes/technical-preview-cash-control.php'));
    }

    private function selectedPreviewRuntimePermitted(): bool
    {
        return TechnicalPreviewRuntimePolicy::permits(
            enabled: (bool) config('technical-preview.enabled', false),
            runtimeClass: (string) config('technical-preview.runtime_class', ''),
            sessionDriver: (string) config('technical-preview.session.driver', ''),
            sessionLifetimeMinutes: (int) config('technical-preview.session.lifetime', 0),
            sessionEncrypted: (bool) config('technical-preview.session.encrypt', false),
            sessionSecure: (bool) config('technical-preview.session.secure', false),
            sessionHttpOnly: (bool) config('technical-preview.session.http_only', false),
            sessionSameSite: (string) config('technical-preview.session.same_site', ''),
            sessionDomain: config('technical-preview.session.domain'),
            sessionPath: (string) config('technical-preview.session.path', ''),
            sessionCookie: (string) config('technical-preview.session.cookie', ''),
        );
    }

    private function removeDeniedPreviewRoutesAfterApplicationBoot(): void
    {
        $this->app->booted(function (): void {
            /** @var Router $router */
            $router = $this->app->make('router');
            $filteredRoutes = new RouteCollection();

            foreach ($router->getRoutes()->getRoutes() as $route) {
                $routeName = $route->getName();
                $uri = ltrim($route->uri(), '/');
                $isPreviewRoute = (is_string($routeName) && str_starts_with($routeName, 'preview.'))
                    || $uri === 'technical-preview'
                    || str_starts_with($uri, 'technical-preview/');

                if (! $isPreviewRoute) {
                    $filteredRoutes->add($route);
                }
            }

            $router->setRoutes($filteredRoutes);
        });
    }

    private function applyDeployedPreviewSessionEnvelope(): void
    {
        config([
            'session.driver' => (string) config('technical-preview.session.driver'),
            'session.lifetime' => (int) config('technical-preview.session.lifetime'),
            'session.encrypt' => (bool) config('technical-preview.session.encrypt'),
            'session.secure' => (bool) config('technical-preview.session.secure'),
            'session.http_only' => (bool) config('technical-preview.session.http_only'),
            'session.same_site' => (string) config('technical-preview.session.same_site'),
            'session.domain' => config('technical-preview.session.domain'),
            'session.path' => (string) config('technical-preview.session.path'),
            'session.cookie' => (string) config('technical-preview.session.cookie'),
        ]);
    }
}
