<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunRatsitHittaPostOrtJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    protected string $postOrt;

    protected string $postNumId;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 7200;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(string $postOrt, string $postNumId)
    {
        $this->postOrt = $postOrt;
        $this->postNumId = $postNumId;
    }

    /**
     * Custom serialization for PHP 8.1+ compatibility
     */
    public function __serialize(): array
    {
        return [
            'postOrt' => $this->postOrt,
            'postNumId' => $this->postNumId,
        ];
    }

    /**
     * Custom unserialization for PHP 8.1+ compatibility
     */
    public function __unserialize(array $data): void
    {
        $this->postOrt = $data['postOrt'];
        $this->postNumId = $data['postNumId'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $postNum = PostNum::find($this->postNumId);
            if (! $postNum) {
                throw new Exception("PostNum with ID {$this->postNumId} not found");
            }

            Log::info("Starting ratsit_hitta_postort.mjs job for post_ort: {$this->postOrt}");

            // Build the command
            $scriptPath = base_path('jobs/ratsit_hitta_postort.mjs');
            $command = 'APP_URL='.config('app.url').' API_URL='.config('app.url')." node {$scriptPath} --post-ort \"{$this->postOrt}\"";

            Log::info("Executing ratsit_hitta_postort command: {$command}");

            // Get initial job count for this batch
            $initialJobCount = DB::table('jobs')->count();

            // Execute the script
            $output = shell_exec($command);

            Log::info('ratsit_hitta_postort.mjs script completed', [
                'output' => $output,
                'post_ort' => $this->postOrt,
            ]);

            // Update the PostNum record to indicate completion
            $postNum->update([
                'status' => 'complete',
                'ratsit_personer_queue' => false,
                'updated_at' => now(),
            ]);

            // Get final job count
            $finalJobCount = DB::table('jobs')->count();
            $newJobsCreated = $finalJobCount - $initialJobCount;

            Log::info("Ratsit Hitta PostOrt job completed. New jobs created during execution: {$newJobsCreated}");

        } catch (Exception $e) {
            Log::error('RunRatsitHittaPostOrtJob failed', [
                'post_ort' => $this->postOrt,
                'postNumId' => $this->postNumId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update status to failed
            if ($postNum = PostNum::find($this->postNumId)) {
                $postNum->update(['status' => 'failed']);
            }

            throw $e;
        }
    }
}
