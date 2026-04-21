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
            if (! is_array($data) || ! isset($data['sheets'])) {
                continue;
            }

            $updated = false;
            foreach ($data['sheets'] as $sheetId => $sheet) {
                if (isset($sheet['cellData']) && is_array($sheet['cellData'])) {
                    $cellData = $sheet['cellData'];
                    $newCellData = $this->convertCellDataToObjects($cellData);
                    if ($newCellData !== $cellData) {
                        $data['sheets'][$sheetId]['cellData'] = $newCellData;
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                DB::table('spreadsheets')
                    ->where('id', $spreadsheet->id)
                    ->update(['data' => json_encode($data)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This conversion is lossy (cannot revert simple values)
    }

    private function convertCellDataToObjects(array $cellData): array
    {
        $newCellData = [];
        foreach ($cellData as $rowIndex => $row) {
            if (! is_array($row)) {
                $newCellData[$rowIndex] = $row;

                continue;
            }
            foreach ($row as $colIndex => $cell) {
                $cellValue = $cell;
                if (! is_array($cellValue) && $cellValue !== null) {
                    $cellValue = ['v' => $cellValue];
                }
                $newCellData[$rowIndex][$colIndex] = $cellValue;
            }
        }

        return $newCellData;
    }
};
