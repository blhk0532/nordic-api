<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\HittaData;
use App\Models\PostNum;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class RunHittaPostOrtPageJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use Queueable;

    public int $timeout = 11200; // 1 hour timeout per post nummer

    public function __construct(
        protected int|string $postNumId,
        protected int $page,
        protected int $totalPages
    ) {
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

            return "{$postNummer} - 📄 Page {$this->page}/{$this->totalPages}";
        } catch (Throwable $e) {
            return "PostNum #{$this->postNumId} - Page {$this->page}/{$this->totalPages}";
        }
    }

    public function handle(): void
    {
        // Check if the batch has been cancelled
        if ($this->batch()?->cancelled()) {
            Log::info('RunHittaPostOrtPageJob skipped - batch cancelled', [
                'postNumId' => $this->postNumId,
                'page' => $this->page,
            ]);

            return;
        }

        $postNum = PostNum::find($this->postNumId);
        if (! $postNum) {
            Log::warning('RunHittaPostOrtPageJob: PostNum not found', ['id' => $this->postNumId]);

            return;
        }

        $query = $postNum->post_ort; // search by post ort (city)

        // Build Node command: one page only via --maxResults 25
        $script = base_path('jobs/post_ort_hitta.mjs');
        $command = [
            'APP_URL='.config('app.url'), 'API_URL='.config('app.url'),
            'node',
            $script,
            (string) $query,
            '--startPage', (string) $this->page,
            '--maxResults', '25',
            '--json-output', // Add flag to output JSON data
            '--api-url', config('app.url'),
            '--api-token', env('LARAVEL_API_TOKEN'),
        ];

        Log::info('RunHittaPostOrtPageJob starting', [
            'postNum' => $postNum->post_nummer,
            'postOrt' => $query,
            'page' => $this->page,
            'totalPages' => $this->totalPages,
            'batchId' => $this->batch()?->id,
            'job_id' => $this->job?->getJobId(),
        ]);

        $process = new Process($command);
        $process->setTimeout(600000); // 15 minutes per page (script takes ~4-5 min normally)
        $process->run();

        $exitCode = $process->getExitCode();
        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        Log::info('RunHittaPostOrtPageJob script execution completed', [
            'postNum' => $postNum->post_nummer,
            'page' => $this->page,
            'exit_code' => $exitCode,
            'output_length' => mb_strlen($output),
            'error_output_length' => mb_strlen($errorOutput),
            'batchId' => $this->batch()?->id,
            'job_id' => $this->job?->getJobId(),
        ]);

        // Log script output if not too long
        if (mb_strlen($output) > 0 && mb_strlen($output) < 2000) {
            Log::info('RunHittaPostOrtPageJob script stdout', [
                'postNum' => $postNum->post_nummer,
                'page' => $this->page,
                'output' => mb_trim($output),
            ]);
        }

        // Log errors if any
        if (mb_strlen($errorOutput) > 0) {
            Log::warning('RunHittaPostOrtPageJob script stderr', [
                'postNum' => $postNum->post_nummer,
                'page' => $this->page,
                'error_output' => mb_trim($errorOutput),
            ]);
        }

        if (! $process->isSuccessful()) {
            Log::error('RunHittaPostOrtPageJob failed', [
                'postNum' => $postNum->post_nummer,
                'page' => $this->page,
                'exit_code' => $exitCode,
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
                'batchId' => $this->batch()?->id,
                'job_id' => $this->job?->getJobId(),
            ]);

            return;
        }

        // Parse JSON output and save directly to database
        $savedCount = $this->parseAndSaveResults($output, $postNum);

        Log::info('RunHittaPostOrtPageJob data saving completed', [
            'postNum' => $postNum->post_nummer,
            'page' => $this->page,
            'saved_records' => $savedCount,
        ]);

        Log::info('RunHittaPostOrtPageJob completed successfully', [
            'postNum' => $postNum->post_nummer,
            'page' => $this->page,
            'totalPages' => $this->totalPages,
            'savedRecords' => $savedCount,
            'batchId' => $this->batch()?->id,
        ]);

        // Update per-record progress
        try {
            $oldProcessedPages = $postNum->hitta_postort_processed_pages;
            $postNum->increment('hitta_postort_processed_pages');
            $postNum->update(['hitta_postort_last_page' => $this->page]);

            Log::info('RunHittaPostOrtPageJob progress updated in database', [
                'postNum' => $postNum->post_nummer,
                'page' => $this->page,
                'old_processed_pages' => $oldProcessedPages,
                'new_processed_pages' => $postNum->fresh()->hitta_postort_processed_pages,
                'last_page' => $this->page,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to update progress fields', [
                'id' => $this->postNumId,
                'postNum' => $postNum->post_nummer,
                'page' => $this->page,
                'error' => $e->getMessage(),
            ]);
        }

        // If last page, mark complete and update summary counts
        if ($this->page >= $this->totalPages) {
            try {
                // Update summary counts from hitta_data table
                $this->updatePostNumCounts($postNum);

                $postNum->update(['status' => 'complete']);
                Log::info('RunHittaPostOrtPageJob marked as complete', [
                    'postNum' => $postNum->post_nummer,
                    'total_pages' => $this->totalPages,
                    'final_page' => $this->page,
                    'status' => 'complete',
                ]);
            } catch (Exception $e) {
                Log::warning('Failed to update PostNum status after last page', [
                    'id' => $this->postNumId,
                    'postNum' => $postNum->post_nummer,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update PostNum summary counts from hitta_data table
     */
    protected function updatePostNumCounts(PostNum $postNum): void
    {
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
            ]);

            Log::info('Updated PostNum counts from hitta_data', [
                'postNum' => $postNum->post_nummer,
                'postNummer' => $postNummer,
                'total' => $totalCount,
                'with_phone' => $phoneCount,
                'houses' => $houseCount,
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to update PostNum counts', [
                'postNum' => $postNum->post_nummer,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parse script output and save results directly to database
     */
    protected function parseAndSaveResults(string $output, PostNum $postNum): int
    {
        $savedCount = 0;

        try {
            // Look for JSON data in the output
            $jsonStart = mb_strpos($output, '[{');
            $jsonEnd = mb_strrpos($output, '}]');

            if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
                $jsonData = mb_substr($output, $jsonStart, $jsonEnd - $jsonStart + 2);

                $records = json_decode($jsonData, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($records)) {
                    Log::info('Parsed JSON data from script output', [
                        'record_count' => count($records),
                        'postNum' => $postNum->post_nummer,
                    ]);

                    // Save each record directly to database
                    foreach ($records as $recordData) {
                        try {
                            $this->saveRecordToDatabase($recordData);
                            $savedCount++;
                        } catch (Exception $e) {
                            Log::warning('Failed to save individual record', [
                                'personnamn' => $recordData['personnamn'] ?? 'unknown',
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                } else {
                    Log::warning('Failed to parse JSON from script output', [
                        'json_error' => json_last_error_msg(),
                        'postNum' => $postNum->post_nummer,
                    ]);
                }
            } else {
                Log::warning('No JSON data found in script output', [
                    'postNum' => $postNum->post_nummer,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error parsing script output', [
                'postNum' => $postNum->post_nummer,
                'error' => $e->getMessage(),
            ]);
        }

        return $savedCount;
    }

    /**
     * Save a single record to the hitta_data table
     */
    protected function saveRecordToDatabase(array $recordData): void
    {
        // Normalize the data structure to match our database schema
        $normalizedData = [
            'personnamn' => $recordData['personnamn'] ?? null,
            'alder' => $recordData['alder'] ?? null,
            'kon' => $recordData['kon'] ?? null,
            'gatuadress' => $recordData['gatuadress'] ?? null,
            'postnummer' => $recordData['postnummer'] ?? null,
            'postort' => $recordData['postort'] ?? null,
            'telefon' => is_array($recordData['telefon'] ?? null) ? ($recordData['telefon'][0] ?? null) : ($recordData['telefon'] ?? null),
            'telefonnumer' => is_array($recordData['telefon'] ?? null) ? implode(' | ', $recordData['telefon']) : ($recordData['telefonnummer'] ?? null),
            'karta' => $recordData['karta'] ?? null,
            'link' => $recordData['link'] ?? null,
            'bostadstyp' => $recordData['bostadstyp'] ?? null,
            'bostadspris' => $recordData['bostadspris'] ?? null,
            'is_active' => true,
            'is_telefon' => ! empty($recordData['telefon']),
            'is_hus' => $this->isHouse($recordData),
        ];

        $normalizedData['is_ratsit'] = $normalizedData['is_hus'] && $normalizedData['is_telefon'];

        // Use upsert with compound key
        if (! empty($normalizedData['personnamn']) && ! empty($normalizedData['gatuadress'])) {
            HittaData::updateOrCreate(
                [
                    'personnamn' => $normalizedData['personnamn'],
                    'gatuadress' => $normalizedData['gatuadress'],
                ],
                $normalizedData
            );
        } elseif (! empty($normalizedData['personnamn'])) {
            HittaData::updateOrCreate(
                ['personnamn' => $normalizedData['personnamn']],
                $normalizedData
            );
        } else {
            HittaData::create($normalizedData);
        }
    }

    /**
     * Determine if a record represents a house
     */
    protected function isHouse(array $recordData): bool
    {
        // Check explicit house type
        $bostadstyp = mb_strtolower($recordData['bostadstyp'] ?? '');
        if (in_array($bostadstyp, ['hus', 'villa', 'radhus', 'friliggande', 'kedjehus'])) {
            return true;
        }

        // Check address for non-house indicators
        $address = mb_strtolower($recordData['gatuadress'] ?? '');
        $nonHousePatterns = [
            '/lgh\s*\d+/i',      // lägenhet/lgh with number
            '/\d+\s*tr/i',       // trappor (stairs)
            '/nb\b/i',           // nära bana (near station)
            '/bv\b/i',           // nära väg (near road)
            '/box\s*\d+/i',      // box with number
            '/\b\d{1,2}\s+[A-Z]\b/i', // single digit + letter (apartment)
        ];

        foreach ($nonHousePatterns as $pattern) {
            if (preg_match($pattern, $address)) {
                return false;
            }
        }

        return true; // Default to house if no exclusion patterns match
    }
}
