<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PostNum;
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

class ImportPostNums implements ShouldQueue
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
        Log::info('Starting PostNums import', [
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

            Log::info('PostNums import completed', [
                'total_rows' => $totalRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => count($failedRows),
            ]);

            // Clean up the uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (Exception $e) {
            Log::error('PostNums import failed', [
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
        $stmt = $pdo->query('SELECT * FROM post_nums');

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

        // For PostNum, we need to handle the composite key (id is post_nummer)
        if (! isset($processedData['id']) && isset($processedData['post_nummer'])) {
            $processedData['id'] = $processedData['post_nummer'];
        }

        // Create or update record
        PostNum::updateOrCreate(
            ['id' => $processedData['id'] ?? $processedData['post_nummer']],
            $processedData
        );
    }

    protected function normalizeColumnName(string $columnName): string
    {
        // Convert camelCase, PascalCase, or space-separated to snake_case
        $columnName = preg_replace('/([a-z])([A-Z])/', '$1_$2', $columnName);
        $columnName = str_replace([' ', '-'], '_', $columnName);
        $columnName = mb_strtolower($columnName);

        // Handle common variations
        $mappings = [
            'post_nummer' => 'post_nummer',
            'post_ort' => 'post_ort',
            'post_lan' => 'post_lan',
            'hitta_personer_total' => 'hitta_personer_total',
            'hitta_foretag_total' => 'hitta_foretag_total',
            'hitta_personer_saved' => 'hitta_personer_saved',
            'hitta_personer_house_saved' => 'hitta_personer_house_saved',
            'hitta_foretag_saved' => 'hitta_foretag_saved',
            'hitta_personer_phone_saved' => 'hitta_personer_phone_saved',
            'hitta_foretag_phone_saved' => 'hitta_foretag_phone_saved',
            'hitta_personer_queue' => 'hitta_personer_queue',
            'hitta_foretag_queue' => 'hitta_foretag_queue',
            'ratsit_personer_total' => 'ratsit_personer_total',
            'ratsit_foretag_total' => 'ratsit_foretag_total',
            'ratsit_personer_saved' => 'ratsit_personer_saved',
            'ratsit_personer_house_saved' => 'ratsit_personer_house_saved',
            'ratsit_foretag_saved' => 'ratsit_foretag_saved',
            'ratsit_personer_phone_saved' => 'ratsit_personer_phone_saved',
            'ratsit_foretag_phone_saved' => 'ratsit_foretag_phone_saved',
            'ratsit_personer_queue' => 'ratsit_personer_queue',
            'ratsit_foretag_queue' => 'ratsit_foretag_queue',
            'merinfo_personer_total' => 'merinfo_personer_total',
            'merinfo_foretag_total' => 'merinfo_foretag_total',
            'merinfo_personer_phone_total' => 'merinfo_personer_phone_total',
            'merinfo_foretag_phone_total' => 'merinfo_foretag_phone_total',
            'merinfo_personer_phone_saved' => 'merinfo_personer_phone_saved',
            'merinfo_foretag_phone_saved' => 'merinfo_foretag_phone_saved',
            'merinfo_personer_saved' => 'merinfo_personer_saved',
            'merinfo_personer_house_saved' => 'merinfo_personer_house_saved',
            'merinfo_foretag_saved' => 'merinfo_foretag_saved',
            'merinfo_personer_queue' => 'merinfo_personer_queue',
            'merinfo_personer_count' => 'merinfo_personer_count',
            'merinfo_foretag_queue' => 'merinfo_foretag_queue',
            'is_active' => 'is_active',
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

            // Integer fields (statistics and counts)
            $integerFields = [
                'hitta_personer_total', 'hitta_foretag_total', 'hitta_personer_saved',
                'hitta_personer_house_saved', 'hitta_foretag_saved', 'hitta_personer_phone_saved',
                'hitta_foretag_phone_saved', 'ratsit_personer_total', 'ratsit_foretag_total',
                'ratsit_personer_saved', 'ratsit_personer_house_saved', 'ratsit_foretag_saved',
                'ratsit_personer_phone_saved', 'ratsit_foretag_phone_saved',
                'merinfo_personer_total', 'merinfo_foretag_total', 'merinfo_personer_saved',
                'merinfo_personer_house_saved', 'merinfo_foretag_saved', 'merinfo_personer_phone_saved',
                'merinfo_foretag_phone_saved', 'merinfo_personer_phone_total', 'merinfo_foretag_phone_total',
            ];

            // Boolean fields (queue flags)
            $booleanFields = [
                'hitta_personer_queue', 'hitta_foretag_queue', 'ratsit_personer_queue',
                'ratsit_foretag_queue', 'merinfo_personer_queue', 'merinfo_personer_count',
                'merinfo_foretag_queue', 'is_active',
            ];

            if (in_array($key, $integerFields)) {
                $processed[$key] = (int) $value;
            } elseif (in_array($key, $booleanFields)) {
                $processed[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } else {
                $processed[$key] = (string) $value;
            }
        }

        return $processed;
    }
}
