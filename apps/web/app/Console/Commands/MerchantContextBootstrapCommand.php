<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Bootstrap\MerchantContextBootstrapService;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Bootstrap\LaravelMerchantContextBootstrapStateRepository;
use App\Infrastructure\Bootstrap\PreauthorizedMerchantContextBootstrapAuthority;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class MerchantContextBootstrapCommand extends Command
{
    /** @var string */
    protected $signature = 'oneqay:merchant-context:bootstrap';

    /** @var string */
    protected $description = 'Atomically establish one exact preauthorized merchant context in an explicitly armed Local/Test/CI runtime.';

    public function handle(): int
    {
        try {
            $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
            $merchantBootstrapArmed = (bool) config('merchant_context_bootstrap.enabled', false);
            $credentialBootstrapArmed = (bool) config('oneqay.first_control_principal_credential_bootstrap.enabled', false);
            $persistenceEnabled = (bool) config('database.oneqay_persistence_enabled', false);

            if (! in_array($runtimeClass, ['local', 'test', 'ci'], true)
                || ! $merchantBootstrapArmed
                || ! $credentialBootstrapArmed
                || ! $persistenceEnabled) {
                return $this->failClosed();
            }

            $grant = config('merchant_context_bootstrap.grant', []);
            if (! is_array($grant)) {
                throw new RuntimeException('Invalid merchant bootstrap grant.');
            }

            foreach ([
                'tenant_id',
                'identity_id',
                'organization_id',
                'outlet_id',
                'device_id',
                'provisioning_id',
            ] as $key) {
                $value = $grant[$key] ?? null;
                if (! is_string($value) || $value === '' || trim($value) !== $value) {
                    throw new RuntimeException('Invalid merchant bootstrap grant.');
                }
            }

            $graph = new DurableContextGraph(
                TenantId::fromString($grant['tenant_id']),
                PlatformIdentityId::fromString($grant['identity_id']),
                OrganizationId::fromString($grant['organization_id']),
                OutletId::fromString($grant['outlet_id']),
                DeviceId::fromString($grant['device_id']),
            );
            $provisioningId = InitialTenantAdministratorProvisioningId::fromString($grant['provisioning_id']);

            $password = $this->secret('New merchant control-principal password');
            $confirmation = $this->secret('Confirm merchant control-principal password');
            if (! is_string($password)
                || ! is_string($confirmation)
                || ! hash_equals($password, $confirmation)) {
                return $this->failClosed();
            }

            $service = new MerchantContextBootstrapService(
                new PreauthorizedMerchantContextBootstrapAuthority([$grant]),
                new LaravelMerchantContextBootstrapStateRepository(
                    app('db')->connection(),
                    $persistenceEnabled && $merchantBootstrapArmed,
                    $runtimeClass,
                ),
                app(DurableContextGraphRepository::class),
                app(InitialTenantAdministratorProvisioningRepository::class),
                app(FirstControlPrincipalCredentialBootstrapService::class),
                app(PersistenceTransaction::class),
                app(PolicyAdministrationClock::class),
            );

            $outcome = $service->bootstrap($graph, $provisioningId, $password);
            if ($outcome !== MerchantContextBootstrapService::OUTCOME_APPLIED) {
                return $this->failClosed();
            }

            $this->line('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP|STATE=applied');

            return self::SUCCESS;
        } catch (Throwable) {
            return $this->failClosed();
        }
    }

    private function failClosed(): int
    {
        $this->error('ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_FAILED');

        return self::FAILURE;
    }
}
