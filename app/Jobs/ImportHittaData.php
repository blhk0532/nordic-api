<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\HittaData;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use PDO;

class ImportHittaData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $filePath;

    protected string $fileType;

    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, string $fileType, int $userId)
    {
        $this->filePath = $filePath;
        $this->fileType = $fileType;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting HittaData import', [
            'file_path' => $this->filePath,
            'file_type' => $this->fileType,
            'user_id' => $this->userId,
        ]);

        try {
            $data = $this->loadData();

            $totalRows = count($data);
            $successfulRows = 0;
            $failedRows = [];

            foreach ($data as $index => $row) {
                try {
                    $this->processRow($row);
                    $successfulRows++;
                } catch (Exception $e) {
                    $failedRows[] = [
                        'row_number' => $index + 1,
                        'data' => $row,
                        'error' => $e->getMessage(),
                    ];
                    Log::warning('Failed to import row '.($index + 1), [
                        'error' => $e->getMessage(),
                        'data' => $row,
                    ]);
                }
            }

            Log::info('HittaData import completed', [
                'total_rows' => $totalRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => count($failedRows),
            ]);

            // Clean up the uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (Exception $e) {
            Log::error('HittaData import failed', [
                'error' => $e->getMessage(),
                'file_path' => $this->filePath,
                'file_type' => $this->fileType,
            ]);

            throw $e;
        }
    }

    protected function loadData(): array
    {
        switch ($this->fileType) {
            case 'csv':
                return $this->loadCsvData();
            case 'xlsx':
            case 'xls':
                return $this->loadExcelData();
            case 'sqlite':
                return $this->loadSqliteData();
            default:
                throw new InvalidArgumentException("Unsupported file type: {$this->fileType}");
        }
    }

    protected function loadCsvData(): array
    {
        $fullPath = Storage::path($this->filePath);
        $data = [];

        if (($handle = fopen($fullPath, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $data[] = array_combine($headers, $row);
            }

            fclose($handle);
        }

        return $data;
    }

    protected function loadExcelData(): array
    {
        $fullPath = Storage::path($this->filePath);

        return Excel::toArray([], $fullPath)[0] ?? [];
    }

    protected function loadSqliteData(): array
    {
        $fullPath = Storage::path($this->filePath);

        $pdo = new PDO("sqlite:{$fullPath}");
        $stmt = $pdo->query('SELECT * FROM hitta_data');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function processRow(array $row): void
    {
        // Normalize column names (convert to snake_case)
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeColumnName($key);
            $normalizedRow[$normalizedKey] = $value;
        }

        // Process and cast data types
        $processedData = $this->processDataTypes($normalizedRow);

        // Create or update record
        HittaData::create($processedData);
    }

    protected function normalizeColumnName(string $columnName): string
    {
        // Convert camelCase, PascalCase, or space-separated to snake_case
        $columnName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $columnName);
        $columnName = str_replace([' ', '-'], '_', $columnName);
        $columnName = mb_strtolower($columnName);

        // Handle common variations
        $mappings = [
            'person_namn' => 'personnamn',
            'gatu_adress' => 'gatuadress',
            'post_nummer' => 'postnummer',
            'post_ort' => 'postort',
            'telefon_nummer' => 'telefonnummer',
            'telefon_numer' => 'telefonnummer', // Handle the DB typo
            'bostads_typ' => 'bostadstyp',
            'bostads_pris' => 'bostadspris',
            'is_active' => 'is_active',
            'is_telefon' => 'is_telefon',
            'is_ratsit' => 'is_ratsit',
            'is_hus' => 'is_hus',
        ];

        return $mappings[$columnName] ?? $columnName;
    }

    protected function processDataTypes(array $row): array
    {
        $processed = [];

        foreach ($row as $key => $value) {
            if ($value === '' || $value === null) {
                continue; // Skip empty values
            }

            switch ($key) {
                case 'telefonnummer':
                case 'telefonnumer': // Handle DB column name
                    // Handle JSON array or comma/pipe-separated string
                    if (is_string($value)) {
                        if (str_starts_with($value, '[')) {
                            $processed['telefonnummer'] = json_decode($value, true) ?? [];
                        } elseif (str_contains($value, '|')) {
                            $processed['telefonnummer'] = array_map('trim', explode('|', $value));
                        } elseif (str_contains($value, ',')) {
                            $processed['telefonnummer'] = array_map('trim', explode(',', $value));
                        } else {
                            $processed['telefonnummer'] = [$value];
                        }
                    } elseif (is_array($value)) {
                        $processed['telefonnummer'] = $value;
                    } else {
                        $processed['telefonnummer'] = [];
                    }

                    break;

                case 'is_active':
                case 'is_telefon':
                case 'is_ratsit':
                case 'is_hus':
                    $processed[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                    break;

                case 'alder':
                    $processed[$key] = (int) $value;

                    break;

                case 'bostadspris':
                    $processed[$key] = (float) $value;

                    break;

                default:
                    $processed[$key] = (string) $value;

                    break;
            }
        }

        return $processed;
    }
}
