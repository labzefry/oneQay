<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\SystemUpdate\Development\DevelopmentUpdaterViolation;
use App\Infrastructure\SystemUpdate\Development\GovernedDevelopmentUpdateProcessor;
use Illuminate\Console\Command;

// Author by Lab | zefry
final class DiscoverGovernedDevelopmentUpdateCommand extends Command
{
    protected $signature = 'oneqay:update:discover-development';
    protected $description = 'Discover and bind the latest trusted oneQay staging release without deploying it.';

    public function handle(GovernedDevelopmentUpdateProcessor $processor): int
    {
        try {
            $result = $processor->discover();
            $state = (string) ($result['state'] ?? 'UNKNOWN');
            $release = (string) ($result['release_id'] ?? 'none');
            $this->info("oneQay development updater discovery: {$state}; release={$release}");

            return self::SUCCESS;
        } catch (DevelopmentUpdaterViolation $violation) {
            $this->error('oneQay development updater discovery denied: '.$violation->safeCode());

            return self::FAILURE;
        }
    }
}
