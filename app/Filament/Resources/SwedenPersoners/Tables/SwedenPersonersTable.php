<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Tables;

use App\Actions\TransferSwedenPersonerToRingaDataAction;
use App\Actions\UpdateSwedenPersonerAction;
use App\Exports\SwedenPersonerExporter;
use App\Models\SwedenPersoner;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SwedenPersonersTable
{
    private static function transferToRingaDataBulkAction(): Action
    {
        return Action::make('transferToRingaData')
            ->label('Transfer to Ringa Data')
            ->icon('heroicon-o-arrow-up-right')
            ->color('warning')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->accessSelectedRecords()
            ->modalHeading('Transfer selected to Ringa Data')
            ->modalSubmitActionLabel('Transfer')
            ->schema([
                TextInput::make('url')
                    ->label('Ringa Data API URL')
                    ->required()
                    ->url()
                    ->default('https://ringa-data.example.com/api/import')
                    ->placeholder('https://ringa-data.example.com/api/import'),
            ])
            ->action(function (array $data, $records) {
                $url = $data['url'] ?? null;
                if (! $url) {
                    Notification::make()
                        ->danger()
                        ->title('No URL specified')
                        ->body('You must provide a valid Ringa Data API URL.')
                        ->send();

                    return;
                }

                $payload = $records->map->toArray()->values()->all();

                try {
                    $response = Http::post($url, [
                        'records' => $payload,
                    ]);

                    if ($response->successful()) {
                        Notification::make()
                            ->success()
                            ->title('Transfer successful')
                            ->body('Data was sent to Ringa Data API.')
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Transfer failed')
                            ->body('API response: '.$response->body())
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Transfer failed')
                        ->body('Error: '.$e->getMessage())
                        ->send();
                }
            });
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('adress')
                    ->label('Adress')
                    ->searchable()
                    ->limit(218)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('personnamn')
                    ->label('Namn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('fornamn')
                    ->label('Förnamn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('efternamn')
                    ->label('Efternamn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('personnummer')
                    ->label('Personnummer')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kon')
                    ->label('Kön')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('swedenKommun.lan')
                    ->label('Län')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefon')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('civilstand')
                    ->label('Civilstånd')
                    ->hidden()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bostadstyp')
                    ->label('Bostadstyp')
                    ->hidden()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personer')
                    ->label('Hushåll')
                    ->numeric()
                    ->hidden()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_hus')
                    ->hidden()
                    ->label('Hus')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_owner')
                    ->label('Ägare')
                    ->hidden()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_done')
                    ->label('Klar')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormWidth(Width::FourExtraLarge)
            ->filtersFormColumns(4)
            ->filters([
                SelectFilter::make('telefon')
                    ->label('Phone')
                    ->default('yes')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->whereNotNull('telefon'),
                            'no' => $query->whereNull('telefon'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('is_hus')
                    ->label('House')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->where('is_hus', true),
                            'no' => $query->where('is_hus', false),
                            default => $query,
                        };
                    }),
                SelectFilter::make('ratsit_data')
                    ->label('Ratsit Data')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->whereNotNull('ratsit_data'),
                            'no' => $query->whereNull('ratsit_data'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('hitta_data')
                    ->label('Hitta Data')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->whereNotNull('hitta_data'),
                            'no' => $query->whereNull('hitta_data'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('merinfo_data')
                    ->label('MerInfo Data')
                    ->options([
                        'yes' => 'Yes',
                        'no' => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->whereNotNull('merinfo_data'),
                            'no' => $query->whereNull('merinfo_data'),
                            default => $query,
                        };
                    }),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('is_queue')
                    ->label('Queue'),

                SelectFilter::make('postort')
                    ->label('Postort')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return SwedenPersoner::query()
                            ->whereNotNull('postort')
                            ->distinct()
                            ->orderBy('postort')
                            ->pluck('postort', 'postort')
                            ->toArray();
                    }),

                SelectFilter::make('kommun')
                    ->label('Kommun')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return SwedenPersoner::query()
                            ->whereNotNull('kommun')
                            ->distinct()
                            ->orderBy('kommun')
                            ->pluck('kommun', 'kommun')
                            ->toArray();
                    }),

                SelectFilter::make('lan')
                    ->label('Landskap')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return SwedenPersoner::query()
                            ->whereNotNull('lan')
                            ->distinct()
                            ->orderBy('lan')
                            ->pluck('lan', 'lan')
                            ->toArray();
                    }),

                SelectFilter::make('agandeform')
                    ->label('Ägandeform')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return SwedenPersoner::query()
                            ->whereNotNull('agandeform')
                            ->distinct()
                            ->orderBy('agandeform')
                            ->pluck('agandeform', 'agandeform')
                            ->toArray();
                    }),

                SelectFilter::make('bostadstyp')
                    ->label('Bostadstyp')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return SwedenPersoner::query()
                            ->whereNotNull('bostadstyp')
                            ->distinct()
                            ->orderBy('bostadstyp')
                            ->pluck('bostadstyp', 'bostadstyp')
                            ->toArray();
                    }),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    static::exportToApiBulkAction(),
                    BulkAction::make('transferToRingaData')
                        ->label('Transfer to Ringa Data')
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, array $data): void {
                            $action = new TransferSwedenPersonerToRingaDataAction;

                            $createdCount = $action->handle($records, $data);
                            $skippedCount = max(0, $records->count() - $createdCount);

                            Notification::make()
                                ->title('Success')
                                ->body($createdCount.' records transferred to Ringa Data'.($skippedCount > 0 ? ' ('.$skippedCount.' duplicates skipped)' : ''))
                                ->success()
                                ->send();
                        }),
                ]),
                BulkAction::make('refreshTable')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Refresh Database Counts')
                    ->modalDescription('This will check and update database counts for the selected records. This may take a few moments.')
                    ->modalSubmitActionLabel('Refresh Counts')
                    ->deselectRecordsAfterCompletion()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records): void {
                        $records = $records->filter(fn ($record) => $record instanceof SwedenPersoner);
                        $count = 0;
                        foreach ($records as $record) {
                            UpdateSwedenPersonerAction::execute($record, false);
                            $count++;
                        }

                        Notification::make()
                            ->success()
                            ->title('Database Counts Updated')
                            ->body("Successfully refreshed database counts for {$count} post nummer(s).")
                            ->send();
                    }),
                Action::make('create')
                    ->label(' ')
                    ->color('')
                    ->icon('heroicon-o-user-plus'),
                ExcelImportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->label('CSV'),
                static::importSqlAction(),
                ExportAction::make()
                    ->label('CSV')
                    ->visible(fn () => auth()->user()->role === 'super')
                    ->exporter(SwedenPersonerExporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('danger'),
                static::exportSqlAction(),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->defaultPaginationPageOption(25);
    }

    private static function exportToApiBulkAction(): Action
    {
        return Action::make('exportToApi')
            ->visible(fn () => auth()->user()->role === 'super')
            ->label('Exportera till API')
            ->icon('heroicon-o-cloud-arrow-up')
            ->color('primary')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->accessSelectedRecords()
            ->modalHeading('Exportera markerade till API')
            ->modalSubmitActionLabel('Exportera')
            ->schema([
                TextInput::make('url')
                    ->label('API URL')
                    ->required()
                    ->url()
                    ->placeholder('http://localhost:8000/api/sweden-personer/import-json'),
            ])
            ->action(function (array $data, $records) {
                $url = $data['url'] ?? null;
                if (! $url) {
                    Notification::make()
                        ->danger()
                        ->title('Ingen URL angiven')
                        ->body('Du måste ange en giltig API-URL.')
                        ->send();

                    return;
                }

                $payload = $records->map->toArray()->values()->all();

                try {
                    $response = Http::post($url, [
                        'records' => $payload,
                    ]);

                    if ($response->successful()) {
                        Notification::make()
                            ->success()
                            ->title('Export lyckades')
                            ->body('Data skickades till API:et.')
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Export misslyckades')
                            ->body('API-svaret: '.$response->body())
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Export misslyckades')
                        ->body('Fel: '.$e->getMessage())
                        ->send();
                }
            });
    }

    private static function importSqlAction(): Action
    {
        return Action::make('importSql')
            ->label('SQL')
            ->icon('heroicon-o-arrow-down-on-square')
            ->color('success')
            ->schema([
                FileUpload::make('file')
                    ->label('SQL File')
                    ->maxSize(1048576)
                    ->acceptedFileTypes(['application/sql', 'text/plain', '.sql'])
                    ->storeFiles(false)
                    ->required(),
            ])
            ->action(function (array $data): void {
                self::handleSqlImport($data);
            });
    }

    private static function handleSqlImport(array $data): void
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
            $sqlContent = file_get_contents($fullPath);
            if (! $sqlContent) {
                throw new \Exception('Could not read file');
            }
            $processedSql = self::processSqlForSafeImport($sqlContent, 'sweden_personer');
            DB::unprepared($processedSql);
            Notification::make()
                ->success()
                ->title('Import Successful')
                ->body('SQL data imported safely.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    private static function processSqlForSafeImport(string $sql, ?string $targetTable = null): string
    {
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (! $inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            if ($char === ';' && ! $inString) {
                $trimmed = trim($current);
                if (! empty($trimmed)) {
                    $statements[] = $trimmed;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $trimmed = trim($current);
        if (! empty($trimmed)) {
            $statements[] = $trimmed;
        }

        $processed = [];
        foreach ($statements as $stmt) {
            $upper = strtoupper(ltrim($stmt));

            if (str_starts_with($upper, 'DROP TABLE') ||
                str_starts_with($upper, 'TRUNCATE') ||
                str_starts_with($upper, 'DELETE FROM')) {
                continue;
            }

            if (preg_match('/^\s*INSERT\s+INTO/i', $stmt)) {
                $stmt = preg_replace('/^\s*INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
            }

            if (preg_match('/^\s*CREATE\s+TABLE/i', $stmt)) {
                $stmt = preg_replace('/^\s*CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $stmt);
            }

            if ($targetTable) {
                $stmt = preg_replace(
                    '/(INSERT\s+(?:IGNORE\s+)?INTO|CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?|ALTER\s+TABLE)\s+[`"\']?[^`"\s(]+[`"\']?/i',
                    '$1 `'.$targetTable.'`',
                    $stmt
                );
            }

            $processed[] = $stmt;
        }

        return implode(";\n", $processed).";\n";
    }

    private static function exportSqlAction(): Action
    {
        return Action::make('exportSql')
            ->label('SQL')
            ->visible(fn () => auth()->user()->role === 'super')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('danger')
            ->action(function () {
                return self::handleSqlExport();
            });
    }

    private static function handleSqlExport()
    {
        try {
            $tableName = 'sweden_personer';
            $rows = DB::table($tableName)->get();

            if ($rows->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('Export Failed')
                    ->body('No data to export.')
                    ->send();

                return null;
            }

            // Exclude 'id' column if present
            $allColumns = array_keys((array) $rows->first());
            $columns = array_filter($allColumns, fn ($col) => $col !== 'id');

            $batchSize = 500;
            $sql = '';
            $values = [];
            $rowCount = 0;
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($columns as $column) {
                    $value = $row->{$column} ?? null;
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = "'".addslashes($value)."'";
                    }
                }
                $values[] = '    ('.implode(', ', $rowValues).')';
                $rowCount++;
                if ($rowCount % $batchSize === 0) {
                    $sql .= "INSERT IGNORE INTO `{$tableName}` (`".implode('`, `', $columns)."`) VALUES\n";
                    $sql .= implode(",\n", $values).";\n";
                    $values = [];
                }
            }
            // Write remaining values
            if (! empty($values)) {
                $sql .= "INSERT IGNORE INTO `{$tableName}` (`".implode('`, `', $columns)."`) VALUES\n";
                $sql .= implode(",\n", $values).";\n";
            }

            $filename = "{$tableName}_export_".now()->format('Y-m-d_H-i-s').'.sql';
            $filepath = storage_path('app/'.$filename);

            file_put_contents($filepath, $sql);

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->send();

            return null;
        }
    }
}
