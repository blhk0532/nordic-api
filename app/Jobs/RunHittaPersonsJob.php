<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class RunHittaPersonsJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use FoundationQueueable;
    use InteractsWithQueue;
    use Queueable;
    use Queueable;
    use SerializesModels;

    public int $timeout = 60000; // 60 minutes

    public int $tries = 1;

    protected $postNumId;

    protected $includeRatsit;

    /**
     * Create a new job instance.
     */
    public function __construct($postNumId, $includeRatsit = false)
    {
        $this->postNumId = $postNumId;
        $this->includeRatsit = $includeRatsit;
    }

    /**
     * Custom serialization for PHP 8.1+ compatibility
     */
    public function __serialize(): array
    {
        return [
            'postNumId' => $this->postNumId,
            'includeRatsit' => $this->includeRatsit,
        ];
    }

    /**
     * Custom unserialization for PHP 8.1+ compatibility
     */
    public function __unserialize(array $data): void
    {
        $this->postNumId = $data['postNumId'];
        $this->includeRatsit = $data['includeRatsit'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the PostNum record
            $postNum = PostNum::find($this->postNumId);
            if (! $postNum) {
                throw new Exception("PostNum with ID {$this->postNumId} not found");
            }

            $postNummer = str_replace(' ', '', $postNum->post_nummer);

            Log::info("Starting RunHittaPersonsJob job for: {$postNummer}");

            // Build the command
            $query = mb_trim($postNum->post_nummer);
            $scriptPath = base_path('jobs/post_ort_hitta.mjs');
            $command = 'APP_URL='.config('app.url').' API_URL='.config('app.url')." node {$scriptPath} \"{$postNummer}\"";

            Log::info("[Hitta Counts Queue {$postNum->post_nummer}] Starting count extraction");

            // Update status to running
            $postNum->update([

                'is_active' => true,
            ]);

            // Use post_nummer for search

            //   $script = base_path('scripts/hittaCounts.mjs');

            $process = Process::timeout(60000)->run([
                'node /home/baba/WORK/fireflow/os0.app/backend/jobs/post_ort_hitta.mjs 98499',
            ]);

            if (! $process->successful()) {
                Log::error("[Hitta Counts Queue {$postNum->post_nummer}] Failed: ".$process->errorOutput());
                $postNum->update([
                    'is_active' => false,
                ]);

                throw new RuntimeException('Failed to run hittaCount script: '.$process->errorOutput());
            }

            $counts = $this->extractCountsFromOutput($process->output());

            if ($counts === null) {
                Log::error("[Hitta Counts Queue {$postNum->post_nummer}] Failed to extract counts from output: ".$process->output());
                $postNum->update([
                    'is_active' => false,
                ]);

                throw new RuntimeException('Failed to extract counts from output: '.$process->output());
            }

            // Update the PostNum record to indicate completion
            $postNum->update([
                'status' => 'complete',
                'updated_at' => now(),
            ]);

        } catch (Exception $e) {
            Log::error('RunHittaPersonsJob failed', [
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
