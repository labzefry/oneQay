<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Application\Pos\SyntheticPosStore;
use App\Application\Preview\PreviewFixtureGateway;
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
}
