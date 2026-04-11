<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Console\Commands;

use Adultdate\Wirechat\Jobs\DeleteExpiredMessagesJob;
use Illuminate\Console\Command;

class DeleteExpiredMessage extends Command
{
    protected $signature = 'wirechat:delete-expired';

    protected $description = 'Deletes expired disappearing messages from conversations';

    public function handle()
    {
        // Run the job that deletes expired messages
        DeleteExpiredMessagesJob::dispatch();

        $this->info('Expired messages have been deleted successfully!');
    }
}
