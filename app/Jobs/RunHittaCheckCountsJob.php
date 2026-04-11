<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Log;
use RuntimeException;

class RunHittaCheckCountsJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $timeout = 3000; // 5 minutes

    public int $tries = 1;

    public function __construct(public string $postNummer)
    {
        $this->onQueue('hitta');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $script = base_path('jobs/hitta_check_counts.mjs');

        $process = Process::timeout(300)->run([
            'APP_URL='.config('app.url'), 'API_URL='.config('app.url'),
            'node',
            $script,
            $this->postNummer,
        ]);

        if (! $process->successful()) {
            throw new RuntimeException('Failed to run hitta_check_counts.mjs script: '.$process->errorOutput());
        }

        // Laravel automatically tracks batch completion when jobs finish successfully
        // No manual increment needed - the framework handles this through recordSuccessfulJob()

        // The script updates the database directly, so we don't need to do anything else
        // Just log success
        Log::info("Hitta check counts completed for postnummer: {$this->postNummer}");

        // Manually increment completed_jobs counter since Laravel doesn't do this automatically
        if ($this->batchId) {
            DB::table('job_batches')
                ->where('id', $this->batchId)
                ->increment('completed_jobs');
            Log::info("Manually incremented completed_jobs for batch: {$this->batchId}");
        }
    }
}
