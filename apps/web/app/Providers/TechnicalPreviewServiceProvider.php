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
            return;
        }

        if (strtolower(trim((string) config('technical-preview.runtime_class', ''))) === 'preview') {
            $this->applyDeployedPreviewSessionEnvelope();
        }

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
