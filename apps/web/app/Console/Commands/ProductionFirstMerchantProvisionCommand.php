<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Bootstrap\MerchantContextBootstrapService;
use App\Application\Bootstrap\MerchantPosReadyBootstrapService;
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
use App\Infrastructure\Bootstrap\ProductionMerchantProvisioningScope;
use Illuminate\Console\Command;
use Throwable;

// Author by Lab | zefry
// One-shot cPanel Cron credential creation, in exact isolated preactivation.
final class ProductionFirstMerchantProvisionCommand extends Command
{
    protected $signature = 'oneqay:production:first-merchant {--private-root= : Exact separate private oneQayDev root}';
    protected $description = 'Provision an initial merchant and private owner-only credential envelope on an exact, source-certified, dark Production dev target.';

    public function handle(): int
    {
        $credentialFile = null;
        try {
            $root = $this->option('private-root');
            if (! is_string($root)
                || $root !== '/home/pekd7254/oneqay-production-test'
                || realpath($root) !== $root
                || is_link($root)) {
                return $this->failClosed();
            }
            $directory = $root.'/shared';
            if (realpath($directory) !== $directory || ! is_writable($directory)
                || is_link($directory)) {
                return $this->failClosed();
            }
            $credentialFile = $directory.'/oneqaydev-first-merchant-credential.json';
            if (file_exists($credentialFile) || is_link($credentialFile)) {
                return $this->failClosed();
            }
            $source = env('ONEQAY_RUNNING_SOURCE_COMMIT', '');
            if (! is_string($source) || preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1) {
                return $this->failClosed();
            }

            $grant = [
                'tenant_id' => 'merchant-'.bin2hex(random_bytes(12)),
                'identity_id' => 'owner-'.bin2hex(random_bytes(12)),
                'organization_id' => 'org-'.bin2hex(random_bytes(12)),
                'outlet_id' => 'outlet-'.bin2hex(random_bytes(12)),
                'device_id' => 'device-'.bin2hex(random_bytes(12)),
                'provisioning_id' => 'provision-'.bin2hex(random_bytes(12)),
            ];
            // Generated in the private host only, never printed to stdout or GitHub.
            $password = bin2hex(random_bytes(20));
            $data = json_encode([
                'schema_version' => 1,
                'state' => 'PREPARED_NOT_YET_APPLIED',
                'source_commit' => $source,
                'domain' => 'oneqaydev.n07.my.id',
                'created_at_unix' => time(),
                'merchant' => $grant,
                'password' => $password,
                'attribution' => 'Lab | zefry',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $fileHandle = @fopen($credentialFile, 'x');
            if ($fileHandle === false) {
                return $this->failClosed();
            }
            try {
                if (! @chmod($credentialFile, 0600)
                    || file_put_contents($credentialFile, $data, LOCK_EX) !== strlen($data)) {
                    return $this->failClosed();
                }
            } finally {
                fclose($fileHandle);
            }

            $outcome = ProductionMerchantProvisioningScope::execute(function () use ($grant, $password): string {
                $runtime = 'production';
                $context = new DurableContextGraph(
                    TenantId::fromString($grant['tenant_id']),
                    PlatformIdentityId::fromString($grant['identity_id']),
                    OrganizationId::fromString($grant['organization_id']),
                    OutletId::fromString($grant['outlet_id']),
                    DeviceId::fromString($grant['device_id']),
                );
                $id = InitialTenantAdministratorProvisioningId::fromString($grant['provisioning_id']);
                $service = new MerchantPosReadyBootstrapService(
                    new MerchantContextBootstrapService(
                        new PreauthorizedMerchantContextBootstrapAuthority([$grant]),
                        new LaravelMerchantContextBootstrapStateRepository(
                            app('db')->connection(), true, $runtime,
                        ),
                        app(DurableContextGraphRepository::class),
                        app(InitialTenantAdministratorProvisioningRepository::class),
                        app(FirstControlPrincipalCredentialBootstrapService::class),
                        app(PersistenceTransaction::class),
                        app(PolicyAdministrationClock::class),
                    ),
                    app(\App\Application\Access\DurableOrganizationalAccessService::class),
                    app(\App\Application\Authorization\DurablePolicyAdministrationService::class),
                    app(PersistenceTransaction::class),
                );

                return $service->bootstrap($context, $id, $password);
            });

            if ($outcome !== MerchantContextBootstrapService::OUTCOME_APPLIED) {
                return $this->failClosed();
            }

            $applied = json_decode((string) file_get_contents($credentialFile), true, 16, JSON_THROW_ON_ERROR);
            $applied['state'] = 'APPLIED';
            $written = json_encode($applied, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (file_put_contents($credentialFile, $written, LOCK_EX) !== strlen($written)) {
                // Durable records may already exist; do not repeat or claim success.
                return $this->failClosed();
            }
            @chmod($credentialFile, 0600);
            $this->line('ONEQAYDEV_FIRST_MERCHANT_APPLIED|CREDENTIAL=PRIVATE_FILE_ONLY');
            return self::SUCCESS;
        } catch (Throwable) {
            return $this->failClosed();
        }
    }

    private function failClosed(): int
    {
        $this->error('ONEQAYDEV_FIRST_MERCHANT_FAILED');
        return self::FAILURE;
    }
}
