<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $spreadsheets = DB::table('spreadsheets')->get();

        foreach ($spreadsheets as $spreadsheet) {
            $data = json_decode($spreadsheet->data, true);
            if (! is_array($data)) {
                continue;
            }

            $newData = $this->convertToUniverSnapshot($data);

            if ($newData !== $data) {
                DB::table('spreadsheets')
                    ->where('id', $spreadsheet->id)
                    ->update(['data' => json_encode($newData)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This conversion is lossy (cannot revert to old format accurately)
        // We'll leave it as is since new format is backward compatible with preview column
    }

    private function convertToUniverSnapshot(array $data): array
    {
        // If already has sheets as object with sheet IDs as keys, assume it's new format
        if (isset($data['sheets']) && is_array($data['sheets'])) {
            $firstKey = array_key_first($data['sheets']);
            if ($firstKey !== null && ! is_numeric($firstKey)) {
                // Already new format (associative sheets)
                return $data;
            }
        }

        $grid = $this->extractGridFromData($data);
        if ($grid === null) {
            // Cannot extract grid, return as is
            return $data;
        }

        return $this->createUniverSnapshot($grid);
    }

    private function extractGridFromData(array $data): ?array
    {
        // Case 1: Old transformed format with sheets as numeric array
        if (isset($data['sheets']) && is_array($data['sheets']) && isset($data['sheets'][0]['cellData'])) {
            return $data['sheets'][0]['cellData'];
        }

        // Case 2: Raw grid data (no sheets key)
        if (! isset($data['sheets']) && is_array($data) && count($data) > 0 && is_array($data[0] ?? null)) {
            return $data;
        }

        // Case 3: Possibly empty or already correct
        return null;
    }

    private function createUniverSnapshot(array $grid): array
    {
        $cellData = [];
        foreach ($grid as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $colIndex => $cell) {
                // Remove UTF-8 BOM from first cell if present
                if ($rowIndex === 0 && $colIndex === 0 && is_string($cell)) {
                    $cell = $this->removeUtf8Bom($cell);
                }
                $cellValue = $cell;
                if (! is_array($cellValue) && $cellValue !== null) {
                    $cellValue = ['v' => $cellValue];
                }
                $cellData[$rowIndex][$colIndex] = $cellValue;
            }
        }

        // Generate unique IDs
        $workbookId = '-U'.strtoupper(bin2hex(random_bytes(3)));
        $sheetId = bin2hex(random_bytes(10));

        // Determine grid dimensions
        $rowCount = max(1000, count($cellData));
        $colCount = max(20, empty($cellData) ? 20 : max(array_map('count', $cellData)));

        return [
            'id' => $workbookId,
            'sheetOrder' => [$sheetId],
            'name' => '',
            'appVersion' => '0.21.0',
            'locale' => 'en-US',
            'styles' => [],
            'sheets' => [
                $sheetId => [
                    'id' => $sheetId,
                    'name' => 'Sheet1',
                    'tabColor' => '',
                    'hidden' => 0,
                    'rowCount' => $rowCount,
                    'columnCount' => $colCount,
                    'zoomRatio' => 1,
                    'freeze' => [
                        'xSplit' => 0,
                        'ySplit' => 0,
                        'startRow' => -1,
                        'startColumn' => -1,
                    ],
                    'scrollTop' => 0,
                    'scrollLeft' => 0,
                    'defaultColumnWidth' => 88,
                    'defaultRowHeight' => 24,
                    'mergeData' => [],
                    'cellData' => $cellData,
                    'rowData' => [],
                    'columnData' => [],
                    'showGridlines' => 1,
                    'rowHeader' => [
                        'width' => 46,
                        'hidden' => 0,
                    ],
                    'columnHeader' => [
                        'height' => 20,
                        'hidden' => 0,
                    ],
                    'rightToLeft' => 0,
                ],
            ],
            'resources' => [
                ['name' => 'SHEET_RANGE_PROTECTION_PLUGIN', 'data' => ''],
                ['name' => 'SHEET_AuthzIoMockService_PLUGIN', 'data' => '{}'],
                ['name' => 'SHEET_WORKSHEET_PROTECTION_PLUGIN', 'data' => '{}'],
                ['name' => 'SHEET_WORKSHEET_PROTECTION_POINT_PLUGIN', 'data' => '{}'],
                ['name' => 'SHEET_DEFINED_NAME_PLUGIN', 'data' => ''],
                ['name' => 'SHEET_RANGE_THEME_MODEL_PLUGIN', 'data' => '{}'],
            ],
        ];
    }

    private function removeUtf8Bom(string $text): string
    {
        // Remove UTF-8 BOM (EF BB BF)
        if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
            $text = substr($text, 3);
        }

        // Remove Unicode BOM (U+FEFF)
        if (substr($text, 0, 3) === "\xFE\xFF" || substr($text, 0, 2) === "\xFF\xFE") {
            $text = substr($text, 2);
        }

        // Remove Unicode BOM character
        $text = preg_replace('/^\x{FEFF}/u', '', $text);

        return $text;
    }
};
