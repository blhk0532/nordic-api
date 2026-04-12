<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AsteriskDialerService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('asterisk:dial
    {channel : Source channel, for example SIP/1001}
    {extension : Destination extension or number}
    {--context=default : Dialplan context}
    {--priority=1 : Dialplan priority}
    {--caller-id= : Optional caller ID}
    {--timeout=30000 : Timeout in milliseconds}
')]
#[Description('Originate a call through Asterisk AMI')]
class AsteriskDialCommand extends Command
{
    public function handle(AsteriskDialerService $dialerService): int
    {
        $didOriginate = $dialerService->originate(
            channel: (string) $this->argument('channel'),
            extension: (string) $this->argument('extension'),
            context: (string) $this->option('context'),
            priority: (int) $this->option('priority'),
            callerId: $this->option('caller-id') !== null ? (string) $this->option('caller-id') : null,
            timeoutMilliseconds: (int) $this->option('timeout'),
        );

        if (! $didOriginate) {
            $this->error('Originate failed.');

            return self::FAILURE;
        }

        $this->info('Originate submitted successfully.');

        return self::SUCCESS;
    }
}
