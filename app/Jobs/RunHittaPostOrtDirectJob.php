<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\HittaData;
use App\Models\PostNum;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class RunHittaPostOrtDirectJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     * Set to 2 hours to allow for processing multiple pages.
     */
    public int $timeout = 720000;

    public function __construct(
        protected int|string $postNumId,
        protected ?int $maxPages = null
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

            return "{$postNummer} - 📥 Hitta PostOrt Direct";
        } catch (Throwable $e) {
            return "PostNum #{$this->postNumId} - Direct Job";
        }
    }

    public function handle(): void
    {
        $postNum = PostNum::find($this->postNumId);
        if (! $postNum) {
            Log::warning('RunHittaPostOrtDirectJob: PostNum not found', ['id' => $this->postNumId]);

            return;
        }

        $query = $postNum->post_ort;

        Log::info('RunHittaPostOrtDirectJob starting', [
            'postNum' => $postNum->post_nummer,
            'postOrt' => $query,
            'maxPages' => $this->maxPages,
        ]);

        // Reset state to ensure clean start
        $postNum->update([
            'status' => 'running',
            'hitta_personer_queue' => true,
            'hitta_postort_total_pages' => null,
            'hitta_postort_processed_pages' => 0,
            'hitta_postort_last_page' => null,
        ]);

        // First, compute total results using --onlyTotals
        $script = base_path('jobs/hitta_post_ort.mjs');
        $command = [
            'node',
            $script,
            (string) $query,
            '--onlyTotals',
            '--api-url', config('app.url'),
            '--api-token', env('LARAVEL_API_TOKEN'),
        ];

        $process = new Process($command);
        $process->setWorkingDirectory(base_path());
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
            $postNum->update(['status' => 'failed']);

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

        // Limit pages if maxPages is specified
        if ($this->maxPages && $this->maxPages < $totalPages) {
            $totalPages = $this->maxPages;
        }

        // Store totals
        $postNum->update([
            'hitta_postort_total_pages' => $totalPages,
            'hitta_postort_processed_pages' => 0,
            'hitta_postort_last_page' => null,
        ]);

        $totalSaved = 0;

        // Process each page directly in this job
        for ($page = 1; $page <= $totalPages; $page++) {
            Log::info('RunHittaPostOrtDirectJob processing page', [
                'postNum' => $postNum->post_nummer,
                'page' => $page,
                'totalPages' => $totalPages,
            ]);

            // Build Node command for this page
            $command = [
                'APP_URL='.config('app.url'), 'API_URL='.config('app.url'),
                'node',
                $script, // Uses hitta_post_ort.mjs
                (string) $query,
                '--startPage', (string) $page,
                '--api-url', config('app.url'),
                '--api-token', env('LARAVEL_API_TOKEN'),
            ];

            $process = new Process($command);
            $process->setWorkingDirectory(base_path());
            $process->setTimeout(600000); // 15 minutes per page
            $process->run();

            $exitCode = $process->getExitCode();
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            Log::info('RunHittaPostOrtDirectJob script execution completed', [
                'postNum' => $postNum->post_nummer,
                'page' => $page,
                'exit_code' => $exitCode,
                'output_length' => mb_strlen($output),
                'error_output_length' => mb_strlen($errorOutput),
                'output_preview' => mb_substr($output, -500), // Last 500 chars to see JSON
            ]);

            if (! $process->isSuccessful()) {
                Log::error('RunHittaPostOrtDirectJob failed on page', [
                    'postNum' => $postNum->post_nummer,
                    'page' => $page,
                    'exit_code' => $exitCode,
                    'error' => $process->getErrorOutput(),
                    'output_last_1000' => mb_substr($output, -1000),
                ]);

                // Don't continue - if one page fails, likely all will fail
                // This prevents wasting resources on a broken configuration
                break;
            }

            // Script handles database saving via API, so we just need to check success
            // The script saves records in real-time via the bulk save API
            // No need to parse JSON output - that's just for CSV files

            Log::info('RunHittaPostOrtDirectJob page completed', [
                'postNum' => $postNum->post_nummer,
                'page' => $page,
                'note' => 'Data saved by script via API during execution',
            ]);

            // Update progress after each page
            try {
                $postNum->increment('hitta_postort_processed_pages');
                $postNum->update(['hitta_postort_last_page' => $page]);

                Log::info('RunHittaPostOrtDirectJob progress updated', [
                    'postNum' => $postNum->post_nummer,
                    'page' => $page,
                    'processed_pages' => $postNum->fresh()->hitta_postort_processed_pages,
                ]);
            } catch (Throwable $e) {
                Log::warning('Failed to update progress fields', [
                    'id' => $this->postNumId,
                    'postNum' => $postNum->post_nummer,
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
            }

            // Small delay between pages to avoid overwhelming the target service
            usleep(500000); // 0.5 seconds
        }

        // Mark as complete and update summary counts
        try {
            // Update summary counts from hitta_data table
            $this->updatePostNumCounts($postNum);

            $postNum->update(['status' => 'complete']);

            Log::info('RunHittaPostOrtDirectJob completed successfully', [
                'postNum' => $postNum->post_nummer,
                'total_pages_processed' => $totalPages,
                'note' => 'Records were saved by script via API during execution',
                'status' => 'complete',
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to update PostNum status after completion', [
                'id' => $this->postNumId,
                'postNum' => $postNum->post_nummer,
                'error' => $e->getMessage(),
            ]);
            $postNum->update(['status' => 'failed']);
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
     * Parse script output and save results with forced database operations
     */
    protected function parseAndSaveResultsForced(string $output, PostNum $postNum): int
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

                    // Use database transaction for forced saving
                    DB::transaction(function () use ($records, &$savedCount) {
                        foreach ($records as $recordData) {
                            try {
                                $this->saveRecordToDatabaseForced($recordData);
                                $savedCount++;
                            } catch (Exception $e) {
                                Log::warning('Failed to save individual record (forced mode)', [
                                    'personnamn' => $recordData['personnamn'] ?? 'unknown',
                                    'gatuadress' => $recordData['gatuadress'] ?? 'unknown',
                                    'error' => $e->getMessage(),
                                ]);
                                // Continue with next record even if one fails
                            }
                        }
                    });

                    Log::info('Force-saved records to database', [
                        'postNum' => $postNum->post_nummer,
                        'records_saved' => $savedCount,
                    ]);

                } else {
                    Log::warning('Failed to parse JSON from script output', [
                        'json_error' => json_last_error_msg(),
                        'postNum' => $postNum->post_nummer,
                        'output_sample' => mb_substr($output, 0, 500),
                    ]);
                }
            } else {
                Log::warning('No JSON data found in script output', [
                    'postNum' => $postNum->post_nummer,
                    'output_length' => mb_strlen($output),
                    'output_sample' => mb_substr($output, 0, 500),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error parsing script output (forced mode)', [
                'postNum' => $postNum->post_nummer,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $savedCount;
    }

    /**
     * Save a single record to the hitta_data table with forced operations
     */
    protected function saveRecordToDatabaseForced(array $recordData): void
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

        // Force save using raw SQL insert with duplicate key update
        if (! empty($normalizedData['personnamn'])) {
            // Use DB::table for more direct control
            DB::table('hitta_data')->insertOrIgnore([
                'personnamn' => $normalizedData['personnamn'],
                'alder' => $normalizedData['alder'],
                'kon' => $normalizedData['kon'],
                'gatuadress' => $normalizedData['gatuadress'],
                'postnummer' => $normalizedData['postnummer'],
                'postort' => $normalizedData['postort'],
                'telefon' => $normalizedData['telefon'],
                'telefonnumer' => $normalizedData['telefonnumer'],
                'karta' => $normalizedData['karta'],
                'link' => $normalizedData['link'],
                'bostadstyp' => $normalizedData['bostadstyp'],
                'bostadspris' => $normalizedData['bostadspris'],
                'is_active' => $normalizedData['is_active'],
                'is_telefon' => $normalizedData['is_telefon'],
                'is_ratsit' => $normalizedData['is_ratsit'],
                'is_hus' => $normalizedData['is_hus'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // If record exists, update it
            if (! empty($normalizedData['gatuadress'])) {
                DB::table('hitta_data')
                    ->where('personnamn', $normalizedData['personnamn'])
                    ->where('gatuadress', $normalizedData['gatuadress'])
                    ->update([
                        'alder' => $normalizedData['alder'],
                        'kon' => $normalizedData['kon'],
                        'postnummer' => $normalizedData['postnummer'],
                        'postort' => $normalizedData['postort'],
                        'telefon' => $normalizedData['telefon'],
                        'telefonnumer' => $normalizedData['telefonnumer'],
                        'karta' => $normalizedData['karta'],
                        'link' => $normalizedData['link'],
                        'bostadstyp' => $normalizedData['bostadstyp'],
                        'bostadspris' => $normalizedData['bostadspris'],
                        'is_active' => $normalizedData['is_active'],
                        'is_telefon' => $normalizedData['is_telefon'],
                        'is_ratsit' => $normalizedData['is_ratsit'],
                        'is_hus' => $normalizedData['is_hus'],
                        'updated_at' => now(),
                    ]);
            }
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
