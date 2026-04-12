<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

#[Signature('asterisk:dialer:listen {--monitor : Show AMI events in terminal output}')]
#[Description('Run AMI event listener for dialer state ingestion')]
class AsteriskDialerListenCommand extends Command
{
    public function handle(): int
    {
        return Artisan::call('ami:listen', [
            '--monitor' => (bool) $this->option('monitor'),
        ]);
    }
}
