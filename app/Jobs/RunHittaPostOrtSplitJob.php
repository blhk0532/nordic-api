<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\HittaData;
use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class RunHittaPostOrtSplitJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use Queueable;

    public int $timeout = 9600; // 1 hour timeout per post nummer

    public function __construct(protected int|string $postNumId)
    {
        $this->onQueue('hitta');
    }

    /**
     * Get the display name for the queued job.
     */
    public function displayName(): string
    {
        try {
            $postNum = PostNum::find($this->postNumId);
            $postNummer = $postNum ? str_replace(' ', '', $postNum->post_nummer) : $this->postNumId;

            return "{$postNummer} - 🔄 Hitta PostOrt Split";
        } catch (Throwable $e) {
            return "PostNum #{$this->postNumId} - Split Job";
        }
    }

    public function handle(): void
    {
        $postNum = PostNum::find($this->postNumId);
        if (! $postNum) {
            Log::warning('RunHittaPostOrtSplitJob: PostNum not found', ['id' => $this->postNumId]);

            return;
        }

        // Reset state to ensure clean start
        $postNum->update([
            'status' => 'running',
            'hitta_personer_queue' => true,
            'hitta_postort_total_pages' => null,
            'hitta_postort_processed_pages' => 0,
            'hitta_postort_last_page' => null,
        ]);

        $query = $postNum->post_ort;

        // First, compute total results using --onlyTotals
        $script = base_path('jobs/post_ort_hitta.mjs');
        $command = [
            'APP_URL='.config('app.url'), 'API_URL='.config('app.url'),
            'node',
            $script,
            (string) $query,
            '--onlyTotals',
            '--api-url', config('app.url'),
            '--api-token', env('LARAVEL_API_TOKEN'),
        ];

        $process = new Process($command);
        $process->setTimeout(60 * 4); // 4 minutes
        $process->run();

        $output = $process->getOutput();
        Log::info('Hitta PostOrt onlyTotals output', [
            'postNum' => $postNum->post_nummer,
            'postOrt' => $query,
            'output' => $output,
        ]);

        if (! $process->isSuccessful()) {
            Log::error('Failed to obtain totals for Hitta PostOrt', [
                'postNum' => $postNum->post_nummer,
                'error' => $process->getErrorOutput(),
            ]);

            return;
        }

        // Parse "Total results: N" or "Total persons: N" from output
        $total = 0;
        if (preg_match('/Total (?:results|persons):\s*(\d+)/i', $output, $m)) {
            $total = (int) ($m[1] ?? 0);
        }

        if ($total <= 0) {
            $postNum->update(['status' => 'empty']);
            Log::info('No results for Hitta PostOrt', ['postNum' => $postNum->post_nummer, 'postOrt' => $query]);

            return;
        }

        $perPage = 25;
        $totalPages = (int) ceil($total / $perPage);

        // Store totals and reset processed count
        $postNum->update([
            'hitta_postort_total_pages' => $totalPages,
            'hitta_postort_processed_pages' => 0,
            'hitta_postort_last_page' => null,
        ]);

        // Create a batch of per-page jobs
        $jobs = [];
        for ($page = 1; $page <= $totalPages; $page++) {
            $jobs[] = new RunHittaPostOrtPageJob($postNum->getKey(), $page, $totalPages);
        }

        $batchName = str_replace(' ', '', $postNum->post_nummer).' - 📄 Hitta PostOrt ('.$totalPages.' pages)';

        Log::info('Creating batch for Hitta PostOrt pages', [
            'postNum' => $postNum->post_nummer,
            'totalPages' => $totalPages,
            'totalJobs' => count($jobs),
            'batchName' => $batchName,
        ]);

        // For very large batches (>1000 jobs), dispatch in chunks to avoid memory issues
        if ($totalPages > 1000) {
            Log::warning('Large batch detected - this may take a while', [
                'postNum' => $postNum->post_nummer,
                'totalPages' => $totalPages,
                'estimatedTime' => round($totalPages / 1000).' minutes',
            ]);
        }

        $batch = Bus::batch($jobs)
            ->name($batchName)
            ->onQueue('hitta')
            ->then(function (Batch $batch) use ($postNum) {
                // When sub-batch completes, update counts and set status complete
                try {
                    $postNummer = $postNum->post_nummer;

                    // Count total records for this postnummer
                    $totalCount = HittaData::where('postnummer', $postNummer)->count();

                    // Count records with phone numbers
                    $phoneCount = HittaData::where('postnummer', $postNummer)
                        ->where('is_telefon', true)
                        ->count();

                    // Count records that are houses
                    $houseCount = HittaData::where('postnummer', $postNummer)
                        ->where('is_hus', true)
                        ->count();

                    $postNum->update([
                        'hitta_personer_saved' => $totalCount,
                        'hitta_personer_phone_saved' => $phoneCount,
                        'hitta_personer_house_saved' => $houseCount,
                        'status' => 'complete',
                    ]);

                    Log::info('Batch complete - Updated PostNum counts from hitta_data', [
                        'postNum' => $postNum->post_nummer,
                        'postNummer' => $postNummer,
                        'total' => $totalCount,
                        'with_phone' => $phoneCount,
                        'houses' => $houseCount,
                    ]);
                } catch (Exception $e) {
                    Log::warning('Failed to update PostNum counts in batch completion', [
                        'postNum' => $postNum->post_nummer,
                        'error' => $e->getMessage(),
                    ]);
                    $postNum->update(['status' => 'complete']);
                }
            })
            ->catch(function (Batch $batch, Throwable $e) use ($postNum) {
                Log::error('Batch failed for Hitta PostOrt', [
                    'postNum' => $postNum->post_nummer,
                    'batchId' => $batch->id,
                    'error' => $e->getMessage(),
                ]);

                $postNum->update(['status' => 'failed']);
            })
            ->finally(function (Batch $batch) use ($postNum) {
                Log::info('Batch finished for Hitta PostOrt', [
                    'postNum' => $postNum->post_nummer,
                    'batchId' => $batch->id,
                    'totalJobs' => $batch->totalJobs,
                    'pendingJobs' => $batch->pendingJobs,
                    'processedJobs' => $batch->processedJobs(),
                    'failedJobs' => $batch->failedJobs,
                ]);
            })
            ->dispatch();

        Log::info('Batch dispatched successfully', [
            'postNum' => $postNum->post_nummer,
            'batchId' => $batch->id,
            'totalJobs' => $batch->totalJobs,
            'totalPages' => $totalPages,
        ]);

        // Update job names in the jobs table from their displayName in payload
        $this->updateJobNamesFromPayload($batch->id, $totalPages);
    }

    /**
     * Update job names in jobs table from their payload displayName
     */
    protected function updateJobNamesFromPayload(string $batchId, int $totalPages): void
    {
        try {
            // Get all jobs that are part of this batch
            $jobs = DB::table('jobs')
                ->where('queue', 'hitta')
                ->whereRaw("JSON_EXTRACT(payload, '$.batchId') = ?", [$batchId])
                ->orderBy('id')
                ->get();

            $updated = 0;
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $displayName = $payload['displayName'] ?? null;

                if ($displayName) {
                    DB::table('jobs')
                        ->where('id', $job->id)
                        ->update(['name' => $displayName]);
                    $updated++;
                }
            }

            Log::info('Updated job names in jobs table', [
                'batchId' => $batchId,
                'totalJobs' => count($jobs),
                'updated' => $updated,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to update job names', [
                'batchId' => $batchId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
