<?php

declare(strict_types=1);

namespace App\Application\Preview;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Pos\SyntheticPosStore;
use App\Domain\Pos\CatalogItem;

// Author by Lab | zefry
interface PreviewFixtureGateway extends SyntheticPosStore
{
    /** @return list<PreviewProfile> */
    public function profiles(): array;

    public function profile(string $principalId): ?PreviewProfile;

    public function reviewerFor(string $operatorPrincipalId): ?PreviewProfile;

    public function verifiedIdentity(string $principalId): ?VerifiedPlatformIdentity;

    /** @return list<CatalogItem> */
    public function catalogFor(string $tenantId, string $outletId): array;
}
