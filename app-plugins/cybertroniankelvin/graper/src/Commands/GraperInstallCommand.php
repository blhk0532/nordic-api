<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Commands;

use Illuminate\Console\Command;

class GraperInstallCommand extends Command
{
    protected $signature = 'graper:install';

    protected $description = 'Install Graper package';

    public function handle(): int
    {
        $this->info('Installing Graper...');

        $this->call('vendor:publish', [
            '--package' => 'graper/graper',
            '--tag' => 'grapesjs-config',
        ]);

        $this->call('vendor:publish', [
            '--package' => 'graper/graper',
            '--tag' => 'grapesjs-migrations',
        ]);

        $this->info('Graper installed successfully.');

        return self::SUCCESS;
    }
}
