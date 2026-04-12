<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AsteriskDialerService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('asterisk:ping')]
#[Description('Test connectivity to Asterisk AMI')]
class AsteriskPingCommand extends Command
{
    public function handle(AsteriskDialerService $dialerService): int
    {
        if ($dialerService->ping()) {
            $this->info('AMI connection successful.');

            return self::SUCCESS;
        }

        $this->error('AMI connection failed.');

        return self::FAILURE;
    }
}
