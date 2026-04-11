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

class TestScrapeQueueJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $testMessage = 'Test from scrape queue!')
    {
        //
    }

    public function handle(): void
    {
        Log::info('TestScrapeQueueJob executed', ['message' => $this->testMessage]);
        sleep(2); // Simulate some work
        Log::info('TestScrapeQueueJob completed', ['message' => $this->testMessage]);
    }
}
