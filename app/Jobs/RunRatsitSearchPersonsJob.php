<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunRatsitSearchPersonsJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

    protected $postNumId;

    /**
     * Create a new job instance.
     */
    public function __construct($postNumId)
    {
        $this->postNumId = $postNumId;
    }

    /**
     * Custom serialization for PHP 8.1+ compatibility
     */
    public function __serialize(): array
    {
        return [
            'postNumId' => $this->postNumId,
        ];
    }

    /**
     * Custom unserialization for PHP 8.1+ compatibility
     */
    public function __unserialize(array $data): void
    {
        $this->postNumId = $data['postNumId'];
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

            $postNummer = $postNum->post_nummer;

            Log::info("Starting ratsitSearchPersonsQueue job for: {$postNummer}");

            // Build the command
            $scriptPath = base_path('jobs/ratsit_hitta.mjs');
            $command = 'APP_URL='.config('app.url').' API_URL='.config('app.url')." node {$scriptPath} \"{$postNummer}\"";

            Log::info("Executing ratsitSearchPersonsQueue command: {$command}");

            // Execute the script
            $output = shell_exec($command);

            Log::info('ratsitSearchPersonsQueue script completed', [
                'output' => $output,
                'postNummer' => $postNummer,
            ]);

            // Update the PostNum record to indicate completion
            $postNum->update([
                'status' => 'complete',
                'ratsit_personer_queue' => true,
                'updated_at' => now(),
            ]);

        } catch (Exception $e) {
            Log::error('RunRatsitSearchPersonsJob failed', [
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
