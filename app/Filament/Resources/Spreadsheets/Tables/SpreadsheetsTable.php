<?php

namespace App\Filament\Resources\Spreadsheets\Tables;

use App\Models\Spreadsheet;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use App\Support\Facades\Excel;
use Qalainau\UniverSheet\SpreadsheetColumn;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SpreadsheetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_rows')
                    ->label('Rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_columns')
                    ->label('Cols')
                    ->numeric()
                    ->sortable(),
                SpreadsheetColumn::make('data'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->action(function (Spreadsheet $record) {
                        return self::exportSpreadsheetToCsv($record);
                    }),
                Action::make('importMore')
                    ->label('Import More')
                    ->icon('heroicon-o-plus')
                    ->color('info')
                    ->form([
                        FileUpload::make('file')
                            ->label('Excel/CSV File')
                            ->disk('public')
                            ->directory('spreadsheet-imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                                '.xlsx',
                                '.xls',
                                '.csv',
                                '.txt',
                            ])
                            ->maxSize(51200)
                            ->maxFiles(1)
                            ->required(),
                    ])
                    ->action(function (array $data, Spreadsheet $record) {
                        return self::importAdditionalData($record, $data);
                    }),
                Action::make('syncGoogleSheet')
                    ->label('Sync Google Sheet')
                    ->icon('heroicon-o-cloud-upload')
                    ->color('warning')
                    ->visible(fn (Spreadsheet $record): bool => ! empty($record->google_sheet_id))
                    ->requiresConfirmation()
                    ->action(function (Spreadsheet $record) {
                        return self::syncFromGoogleSheet($record);
                    }),
            ])
            ->toolbarActions([
                CreateAction::make(),
                Action::make('importExcel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        TextInput::make('name')
                            ->label('Spreadsheet Name')
                            ->required(),
                        FileUpload::make('file')
                            ->label('Excel/CSV File')
                            ->disk('public')
                            ->directory('spreadsheet-imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                                '.xlsx',
                                '.xls',
                                '.csv',
                                '.txt',
                            ])
                            ->maxSize(51200)
                            ->maxFiles(1)
                            ->minFiles(1)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $files = $data['file'] ?? [];
                            if (empty($files)) {
                                throw new \Exception('No file uploaded');
                            }

                            $filePath = is_array($files) ? ($files[0] ?? null) : $files;
                            if (! $filePath) {
                                throw new \Exception('No file uploaded');
                            }

                            // Find the uploaded file (similar to SQL import pattern)
                            $fullPath = storage_path('app/public/'.$filePath);
                            if (! file_exists($fullPath)) {
                                $fullPath = storage_path('app/'.$filePath);
                            }
                            if (! file_exists($fullPath)) {
                                throw new \Exception('File not found: '.$filePath);
                            }

                            // Determine file type and read accordingly
                            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                            $grid = [];

                            if (in_array($extension, ['xlsx', 'xls', 'xlsm', 'xltx', 'xltm'])) {
                                // Read Excel file using our Excel wrapper
                                try {
                                    $rows = Excel::toArray(null, $fullPath);
                                } catch (\Exception $ex) {
                                    throw new \Exception('Excel read error: '.$ex->getMessage());
                                }

                                if (empty($rows)) {
                                    throw new \Exception('No data found in the Excel file.');
                                }
                                $grid = $rows[0]; // First sheet
                            } elseif (in_array($extension, ['csv', 'txt'])) {
                                // Read CSV file
                                $fileHandle = fopen($fullPath, 'r');
                                if (! $fileHandle) {
                                    throw new \Exception('Cannot open CSV file.');
                                }

                                // Read CSV rows and filter empty rows
                                while (($row = fgetcsv($fileHandle, 0, ',', '"', '\\')) !== false) {
                                    // Check if row has any non-empty values
                                    $hasData = false;
                                    foreach ($row as $cell) {
                                        if (trim($cell) !== '') {
                                            $hasData = true;
                                            break;
                                        }
                                    }
                                    if ($hasData) {
                                        $grid[] = $row;
                                    }
                                }
                                fclose($fileHandle);

                                if (empty($grid)) {
                                    throw new \Exception('No data found in the CSV file.');
                                }

                                // Debug: log first few rows and columns
                                logger()->debug('CSV sample data', [
                                    'first_row' => $grid[0] ?? [],
                                    'first_cell' => $grid[0][0] ?? 'empty',
                                    'second_row' => $grid[1] ?? [],
                                ]);

                            } else {
                                throw new \Exception('Unsupported file format: '.$extension);
                            }

                            // Transform grid to Univer Sheet format
                            $transformedData = self::transformGridToUniverFormat($grid);

                            // Debug logging
                            logger()->debug('Importing spreadsheet', [
                                'name' => $data['name'],
                                'grid_rows' => count($grid),
                                'grid_columns' => count($grid[0] ?? []),
                                'file_path' => $fullPath,
                                'extension' => $extension,
                                'transformed_structure' => array_keys($transformedData),
                                'has_sheets' => isset($transformedData['sheets']),
                            ]);

                            // Create spreadsheet record
                            $spreadsheet = Spreadsheet::create([
                                'name' => $data['name'],
                                'data' => $transformedData,
                                'team_id' => filament()->getTenant()?->id,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Import Successful')
                                ->body('Spreadsheet imported successfully. ID: '.$spreadsheet->id)
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Import Failed')
                                ->body('Error: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine())
                                ->send();
                        }
                    }),
                BulkActionGroup::make([
                    Action::make('exportSelectedCsv')
                        ->label('Export Selected as CSV')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            return self::exportSpreadsheetsToZip($records);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function snapshotToGrid(array $snapshot): array
    {
        $grid = [];
        if (! isset($snapshot['sheets'])) {
            return $grid;
        }

        $firstSheet = collect($snapshot['sheets'])->first();
        if (! isset($firstSheet['cellData']) || ! is_array($firstSheet['cellData'])) {
            return $grid;
        }

        $cellData = $firstSheet['cellData'];
        foreach ($cellData as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $colIndex => $cell) {
                $value = is_array($cell) ? ($cell['v'] ?? '') : $cell;
                $grid[$rowIndex][$colIndex] = $value;
            }
        }

        // Ensure rectangular grid (fill missing cells with empty string)
        $maxCols = 0;
        foreach ($grid as $row) {
            $maxCols = max($maxCols, count($row));
        }
        foreach ($grid as &$row) {
            while (count($row) < $maxCols) {
                $row[] = '';
            }
        }

        return $grid;
    }

    private static function transformGridToUniverFormat(array $grid): array
    {
        $cellData = [];
        $bomRemoved = false;

        foreach ($grid as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as $colIndex => $cell) {
                // Remove UTF-8 BOM from first cell if present
                if ($rowIndex === 0 && $colIndex === 0 && is_string($cell)) {
                    $original = $cell;
                    $cell = self::removeUtf8Bom($cell);
                    if ($original !== $cell) {
                        $bomRemoved = true;
                        logger()->debug('BOM removed from first cell', [
                            'original_length' => strlen($original),
                            'new_length' => strlen($cell),
                            'original_start' => bin2hex(substr($original, 0, 3)),
                            'new_start' => bin2hex(substr($cell, 0, 3)),
                        ]);
                    }
                }

                // Store cell value as cell object
                $cellValue = $cell;
                if (! is_array($cellValue) && $cellValue !== null) {
                    $cellValue = ['v' => $cellValue];
                }
                $cellData[$rowIndex][$colIndex] = $cellValue;
            }
        }

        // Log sample of transformed data
        if (! empty($cellData)) {
            logger()->debug('Transformed cell data sample', [
                'total_rows' => count($cellData),
                'total_cols' => count($cellData[0] ?? []),
                'first_cell' => $cellData[0][0] ?? 'empty',
                'second_cell' => $cellData[0][1] ?? 'empty',
                'has_bom' => $bomRemoved,
            ]);
        }

        // Generate unique IDs
        $workbookId = '-U'.strtoupper(bin2hex(random_bytes(3)));
        $sheetId = bin2hex(random_bytes(10));

        // Determine grid dimensions
        $rowCount = max(1000, count($cellData));
        $colCount = max(20, empty($cellData) ? 20 : max(array_map('count', $cellData)));

        // Build proper Univer snapshot matching the format from empty spreadsheets
        return [
            'id' => $workbookId,
            'sheetOrder' => [$sheetId],
            'name' => '',
            'appVersion' => '0.21.0',
            'locale' => 'en-US', // Use config default instead of zhCN
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
                    'cellData' => $cellData, // Keep as 2D array for compatibility with preview column
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

    private static function exportSpreadsheetToCsv(Spreadsheet $spreadsheet): StreamedResponse
    {
        $grid = self::snapshotToGrid($spreadsheet->data);
        $filename = str_replace([' ', '/', '\\'], '-', $spreadsheet->name).'.csv';

        return new StreamedResponse(function () use ($grid) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($grid as $row) {
                fputcsv($handle, $row, ',', '"', '\\');
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private static function exportSpreadsheetsToZip(array $spreadsheets): StreamedResponse
    {
        $zip = new ZipArchive;
        $zipFilename = tempnam(sys_get_temp_dir(), 'spreadsheets_').'.zip';

        if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP file');
        }

        foreach ($spreadsheets as $spreadsheet) {
            $grid = self::snapshotToGrid($spreadsheet->data);
            $csvFilename = str_replace([' ', '/', '\\'], '-', $spreadsheet->name).'.csv';

            // Create CSV content with BOM
            $csvContent = "\xEF\xBB\xBF";
            $handle = fopen('php://temp', 'r+');
            foreach ($grid as $row) {
                fputcsv($handle, $row, ',', '"', '\\');
            }
            rewind($handle);
            $csvContent .= stream_get_contents($handle);
            fclose($handle);

            $zip->addFromString($csvFilename, $csvContent);
        }

        $zip->close();

        $response = new StreamedResponse(function () use ($zipFilename) {
            readfile($zipFilename);
            unlink($zipFilename);
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="spreadsheets-export-'.date('Y-m-d-His').'.zip"',
            'Content-Length' => filesize($zipFilename),
        ]);

        return $response;
    }

    private static function removeUtf8Bom(string $text): string
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

    private static function importAdditionalData(Spreadsheet $record, array $data): void
    {
        try {
            $files = $data['file'] ?? [];
            if (empty($files)) {
                throw new \Exception('No file uploaded');
            }

            $filePath = is_array($files) ? ($files[0] ?? null) : $files;
            if (! $filePath) {
                throw new \Exception('No file uploaded');
            }

            $fullPath = storage_path('app/public/'.$filePath);
            if (! file_exists($fullPath)) {
                $fullPath = storage_path('app/'.$filePath);
            }
            if (! file_exists($fullPath)) {
                throw new \Exception('File not found: '.$filePath);
            }

            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $newGrid = [];

            if (in_array($extension, ['xlsx', 'xls', 'xlsm', 'xltx', 'xltm'])) {
                $rows = Excel::toArray(null, $fullPath);
                if (empty($rows)) {
                    throw new \Exception('No data found in the Excel file.');
                }
                $newGrid = $rows[0];
            } elseif (in_array($extension, ['csv', 'txt'])) {
                $fileHandle = fopen($fullPath, 'r');
                if (! $fileHandle) {
                    throw new \Exception('Cannot open CSV file.');
                }

                while (($row = fgetcsv($fileHandle, 0, ',', '"', '\\')) !== false) {
                    $hasData = false;
                    foreach ($row as $cell) {
                        if (trim($cell) !== '') {
                            $hasData = true;
                            break;
                        }
                    }
                    if ($hasData) {
                        $newGrid[] = $row;
                    }
                }
                fclose($fileHandle);

                if (empty($newGrid)) {
                    throw new \Exception('No data found in the CSV file.');
                }
            } else {
                throw new \Exception('Unsupported file format: '.$extension);
            }

            $currentData = $record->data;
            $firstSheetId = array_key_first($currentData['sheets'] ?? []);
            $currentCellData = $currentData['sheets'][$firstSheetId]['cellData'] ?? [];

            $existingRows = [];
            foreach ($currentCellData as $rowIndex => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rowKey = '';
                foreach ($row as $colIndex => $cell) {
                    $value = is_array($cell) ? ($cell['v'] ?? '') : $cell;
                    $rowKey .= '|'.($value ?? '');
                }
                $existingRows[trim($rowKey)] = $rowIndex;
            }

            $addedCount = 0;
            $skippedCount = 0;
            $startRowIndex = count($currentCellData);

            foreach ($newGrid as $newRow) {
                $rowKey = '';
                foreach ($newRow as $cell) {
                    $rowKey .= '|'.trim($cell);
                }
                $rowKey = trim($rowKey);

                if (isset($existingRows[$rowKey])) {
                    $skippedCount++;

                    continue;
                }

                $newRowData = [];
                foreach ($newRow as $colIndex => $cell) {
                    $newRowData[$colIndex] = ['v' => $cell];
                }
                $currentCellData[$startRowIndex + $addedCount] = $newRowData;
                $addedCount++;
                $startRowIndex++;
            }

            $currentData['sheets'][$firstSheetId]['cellData'] = $currentCellData;
            $currentData['sheets'][$firstSheetId]['rowCount'] = max(1000, count($currentCellData));

            $record->update(['data' => $currentData]);

            Notification::make()
                ->success()
                ->title('Import Complete')
                ->body("Added {$addedCount} new rows, skipped {$skippedCount} duplicates.")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Import Failed')
                ->body('Error: '.$e->getMessage())
                ->send();
        }
    }

    private static function syncFromGoogleSheet(Spreadsheet $record): void
    {
        try {
            $googleSheetId = $record->google_sheet_id;
            if (empty($googleSheetId)) {
                throw new \Exception('No Google Sheet ID configured');
            }

            $url = "https://docs.google.com/spreadsheets/d/{$googleSheetId}/export?format=csv";
            $client = new Client;
            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch Google Sheet: '.$response->getStatusCode());
            }

            $csvContent = (string) $response->getBody();
            $fileHandle = fopen('php://memory', 'r+');
            fwrite($fileHandle, $csvContent);
            rewind($fileHandle);

            $newGrid = [];
            while (($row = fgetcsv($fileHandle, 0, ',', '"', '')) !== false) {
                $hasData = false;
                foreach ($row as $cell) {
                    if (trim($cell) !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if ($hasData) {
                    $newGrid[] = $row;
                }
            }
            fclose($fileHandle);

            if (empty($newGrid)) {
                throw new \Exception('No data found in the Google Sheet.');
            }

            $currentData = $record->data;
            $firstSheetId = array_key_first($currentData['sheets'] ?? []);
            $currentCellData = $currentData['sheets'][$firstSheetId]['cellData'] ?? [];

            $existingRows = [];
            foreach ($currentCellData as $rowIndex => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rowKey = '';
                foreach ($row as $colIndex => $cell) {
                    $value = is_array($cell) ? ($cell['v'] ?? '') : $cell;
                    $rowKey .= '|'.($value ?? '');
                }
                $existingRows[trim($rowKey)] = $rowIndex;
            }

            $addedCount = 0;
            $skippedCount = 0;
            $startRowIndex = count($currentCellData);

            foreach ($newGrid as $newRow) {
                $rowKey = '';
                foreach ($newRow as $cell) {
                    $rowKey .= '|'.trim($cell);
                }
                $rowKey = trim($rowKey);

                if (isset($existingRows[$rowKey])) {
                    $skippedCount++;

                    continue;
                }

                $newRowData = [];
                foreach ($newRow as $colIndex => $cell) {
                    $newRowData[$colIndex] = ['v' => $cell];
                }
                $currentCellData[$startRowIndex + $addedCount] = $newRowData;
                $addedCount++;
                $startRowIndex++;
            }

            $currentData['sheets'][$firstSheetId]['cellData'] = $currentCellData;
            $currentData['sheets'][$firstSheetId]['rowCount'] = max(1000, count($currentCellData));

            $record->update(['data' => $currentData]);

            Notification::make()
                ->success()
                ->title('Sync Complete')
                ->body("Added {$addedCount} new rows, skipped {$skippedCount} duplicates.")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Sync Failed')
                ->body('Error: '.$e->getMessage())
                ->send();
        }
    }
}
