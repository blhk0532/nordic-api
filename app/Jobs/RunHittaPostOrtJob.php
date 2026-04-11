<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunHittaPostOrtJob implements ShouldQueue
{
    use Batchable;
    use Queueable;

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

            Log::info("Starting post_ort_hitta.mjs job for: {$postNummer}");

            // Build the command
            $scriptPath = base_path('jobs/post_ort_hitta.mjs');
            $command = 'APP_URL='.config('app.url').' API_URL='.config('app.url')." node {$scriptPath} \"{$postNummer}\"";

            // Add --ratsit flag if requested
            if ($this->includeRatsit) {
                $command .= ' --ratsit';
            }

            Log::info("Executing hittaSearchPersons command: {$command}");

            // Execute the script
            $output = shell_exec($command);

            Log::info('hittaSearchPersons script completed', [
                'output' => $output,
                'postNummer' => $postNummer,
                'includeRatsit' => $this->includeRatsit,
            ]);

            // Update the PostNum record to indicate completion
            $postNum->update([
                'status' => 'complete',
                'updated_at' => now(),
            ]);

        } catch (Exception $e) {
            Log::error('RunHittaSearchPersonsJob failed', [
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
