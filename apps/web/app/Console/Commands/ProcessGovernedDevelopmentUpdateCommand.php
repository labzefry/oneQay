<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\SystemUpdate\Development\DevelopmentUpdaterViolation;
use App\Infrastructure\SystemUpdate\Development\GovernedDevelopmentUpdateProcessor;
use Illuminate\Console\Command;

// Author by Lab | zefry
final class ProcessGovernedDevelopmentUpdateCommand extends Command
{
    protected $signature = 'oneqay:update:process-development';
    protected $description = 'Process one pending governed oneQay development/staging update request.';

    public function handle(GovernedDevelopmentUpdateProcessor $processor): int
    {
        try {
            $result = $processor->process();
            $state = (string) ($result['state'] ?? 'UNKNOWN');
            $release = (string) ($result['release_id'] ?? 'none');
            $this->info("oneQay development updater: {$state}; release={$release}");

            return self::SUCCESS;
        } catch (DevelopmentUpdaterViolation $violation) {
            $this->error('oneQay development updater denied: '.$violation->safeCode());

            return self::FAILURE;
        }
    }
}
