<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Commands;

use Illuminate\Console\Command;

class GraperCommand extends Command
{
    public $signature = 'graper:publish';

    public $description = 'Publish Graper config and migrations';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'graper-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'graper-migrations',
            '--force' => false,
        ]);

        $this->components->info('Graper assets published. Run [php artisan migrate] to create the graper_pages table.');

        return self::SUCCESS;
    }
}
