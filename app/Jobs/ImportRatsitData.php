<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RatsitData;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use PDO;

class ImportRatsitData implements ShouldQueue
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
        Log::info('Starting RatsitData import', [
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

            Log::info('RatsitData import completed', [
                'total_rows' => $totalRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => count($failedRows),
            ]);

            // Clean up the uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (Exception $e) {
            Log::error('RatsitData import failed', [
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
        $stmt = $pdo->query('SELECT * FROM ratsit_data');

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
        RatsitData::create($processedData);
    }

    protected function normalizeColumnName(string $columnName): string
    {
        // Convert camelCase, PascalCase, or space-separated to snake_case
        $columnName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $columnName);
        $columnName = str_replace([' ', '-'], '_', $columnName);
        $columnName = mb_strtolower($columnName);

        // Handle common variations
        $mappings = [
            'gatu_adress' => 'gatuadress',
            'post_nummer' => 'postnummer',
            'post_ort' => 'postort',
            'kommun_ratsit' => 'kommun_ratsit',
            'adress_andring' => 'adressandring',
            'fodelse_dag' => 'fodelsedag',
            'person_nummer' => 'personnummer',
            'stjarn_tacken' => 'stjarntacken',
            'civil_stand' => 'civilstand',
            'for_namn' => 'fornamn',
            'efter_namn' => 'efternamn',
            'person_namn' => 'personnamn',
            'epost_adress' => 'epost_adress',
            'bolag_engagemang' => 'bolagsengagemang',
            'agande_form' => 'agandeform',
            'bostads_typ' => 'bostadstyp',
            'bo_area' => 'boarea',
            'bygg_ar' => 'byggar',
            'google_maps' => 'google_maps',
            'google_streetview' => 'google_streetview',
            'ratsit_se' => 'ratsit_se',
            'is_active' => 'is_active',
            'is_telefon' => 'is_telefon',
            'is_hus' => 'is_hus',
            'is_queued' => 'is_queued',
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
                case 'telfonnummer':
                case 'epost_adress':
                case 'bolagsengagemang':
                case 'personer':
                case 'foretag':
                case 'grannar':
                case 'fordon':
                case 'hundar':
                    // Handle JSON array or pipe/comma-separated string
                    if (is_string($value)) {
                        if (str_starts_with($value, '[')) {
                            $processed[$key] = json_decode($value, true) ?? [];
                        } elseif (str_contains($value, '|')) {
                            $processed[$key] = array_map('trim', explode('|', $value));
                        } elseif (str_contains($value, ',')) {
                            $processed[$key] = array_map('trim', explode(',', $value));
                        } else {
                            $processed[$key] = [$value];
                        }
                    } elseif (is_array($value)) {
                        $processed[$key] = $value;
                    } else {
                        $processed[$key] = [];
                    }

                    break;

                case 'is_active':
                case 'is_telefon':
                case 'is_hus':
                case 'is_queued':
                    $processed[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                    break;

                case 'alder':
                case 'boarea':
                case 'byggar':
                    $processed[$key] = (int) $value;

                    break;

                case 'longitude':
                case 'latitud':
                    $processed[$key] = (float) $value;

                    break;

                case 'fodelsedag':
                    // Handle Swedish date format or standard formats
                    if (is_string($value)) {
                        // Try to parse various date formats
                        $date = date_create_immutable_from_format('Y-m-d', $value) ?:
                               date_create_immutable_from_format('d/m/Y', $value) ?:
                               date_create_immutable_from_format('Y/m/d', $value) ?:
                               date_create_immutable($value);

                        $processed[$key] = $date ? $date->format('Y-m-d') : $value;
                    } else {
                        $processed[$key] = $value;
                    }

                    break;

                default:
                    $processed[$key] = (string) $value;

                    break;
            }
        }

        return $processed;
    }
}
