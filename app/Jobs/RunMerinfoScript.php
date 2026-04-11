<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunMerinfoScript implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $postnummer;

    public string $queueName;

    public function __construct(string $postnummer, string $queueName = 'merinfo')
    {
        // Expect normalized 5 digit postnummer
        $this->postnummer = $postnummer;
        $this->queueName = $queueName;

        // Set queue name via the Queueable trait's method
        $this->onQueue($queueName);
    }

    public function handle(): void
    {
        // Basic safeguard
        if (! preg_match('/^[0-9]{5}$/', $this->postnummer)) {
            Log::warning('RunMerinfoScript skipped invalid postnummer: '.$this->postnummer);

            return;
        }

    }
}
