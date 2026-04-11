<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Commands;

use Illuminate\Console\Command;

class FilamentBookingCommand extends Command
{
    public $signature = 'filament-booking';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
