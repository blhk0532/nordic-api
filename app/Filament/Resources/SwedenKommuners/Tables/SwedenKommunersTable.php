<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenKommuners\Tables;

use App\Actions\ImportSwedenKommunerCountsFromRatsit;
use App\Actions\UpdateSwedenPersonerAction;
use App\Exports\SwedenKommunerExporter;
use App\Jobs\RunAdresserRatsitJob;
use App\Jobs\RunGatorRatsitJob;
use App\Jobs\RunHittaDataJob;
use App\Jobs\RunPersonerRatsitJob;
use App\Models\SwedenPersoner;
use Devletes\FilamentProgressBar\Tables\Columns\ProgressBarColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;
use Waad\FilamentImportWizard\Actions\ImportWizardAction as ExcelImportAction;

class SwedenKommunersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kommun')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('lan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),

                TextColumn::make('sweden_postorter_count')
                    ->counts('swedenPostorter')
                    ->label('Postort')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sweden_postnummer_count')
                    ->counts('swedenPostnummer')
                    ->label('Postnr')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sweden_gator_count')
                    ->counts('swedenGator')
                    ->label('Gata')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sweden_adresser_count')
                    ->counts('swedenAdresser')
                    ->label('Adress')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('personer')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('foretag')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                ProgressBarColumn::make('personer_count')
                    ->label('DB Progress')
                    ->maxValue(fn ($record) => $record->personer ?: 1)
                    ->showProgressValue()
                    ->showPercentage()
                    ->textPosition('inside')
                    ->warningThreshold(50)
                    ->dangerThreshold(95)
                    ->warningColor('#478b64')
                    ->dangerColor('#478b64')
                    ->size('sm')
                    ->sortable(),
                TextColumn::make('latitude')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('longitude')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('importRatsitCounts')
                        ->label('Import Ratsit Counts')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Import Ratsit counts')
                        ->modalDescription('This updates only the selected sweden_kommuner personer and foretag values from current ratsit_kommuner counts. No rows are deleted or recreated.')
                        ->action(function (Collection $records, ImportSwedenKommunerCountsFromRatsit $importAction): void {
                            try {
                                $stats = $importAction->handle($records->modelKeys());

                                Notification::make()
                                    ->success()
                                    ->title('Ratsit counts imported')
                                    ->body("Processed {$stats['processed']} selected rows, updated {$stats['updated']}, unchanged {$stats['unchanged']}, unmatched {$stats['unmatched']}.")
                                    ->send();
                            } catch (Throwable $throwable) {
                                Notification::make()
                                    ->danger()
                                    ->title('Import failed')
                                    ->body($throwable->getMessage())
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                                            BulkAction::make('runHittaData')
                        ->label('Run Hitta Script')
                        ->icon('heroicon-o-map')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Hitta Script')
                        ->modalDescription('This will run hitta_data.mjs --kommun for each selected kommun. Jobs will run asynchronously via the queue.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if (empty($record->kommun)) {
                                    continue;
                                }

                                dispatch(new RunHittaDataJob($record->kommun));
                                $queued++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Gator Ratsit queued')
                                ->body("Queued {$queued} job(s).")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('runGatorRatsit')
                        ->label('Run Gator Ratsit')
                        ->icon('heroicon-o-map')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Gator Ratsit')
                        ->modalDescription('This will queue sweden_gator_ratsit.mjs --kommun for each selected kommun. Jobs will run asynchronously via the queue.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if (empty($record->kommun)) {
                                    continue;
                                }

                                dispatch(new RunGatorRatsitJob($record->kommun));
                                $queued++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Gator Ratsit queued')
                                ->body("Queued {$queued} job(s).")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('runAdresserRatsit')
                        ->label('Run Adresser Ratsit')
                        ->icon('heroicon-o-home')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Adresser Ratsit')
                        ->modalDescription('This will queue sweden_adresser_ratsit.mjs --kommun for each selected kommun. Jobs will run asynchronously via the queue.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if (empty($record->kommun)) {
                                    continue;
                                }

                                dispatch(new RunAdresserRatsitJob($record->kommun));
                                $queued++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Adresser Ratsit queued')
                                ->body("Queued {$queued} job(s).")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('updatePersonerCount')
                        ->label('Persons DB  Count')
                        ->icon('heroicon-o-calculator')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Persons DB  Count')
                        ->modalDescription('This counts actual records in sweden_personer for each selected kommun and saves the total to persons_count.')
                        ->action(function (Collection $records): void {
                            $updated = 0;

                            foreach ($records as $record) {
                                if (empty($record->kommun)) {
                                    continue;
                                }

                                $count = SwedenPersoner::where('kommun', $record->kommun)->count();
                                $record->update(['personer_count' => $count]);
                                $updated++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Persons DB Count Updated')
                                ->body("Updated {$updated} kommun(s).")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('runPersonerRatsit')
                        ->label('Run Personer Ratsit')
                        ->icon('heroicon-o-users')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Queue Personer Ratsit')
                        ->modalDescription('This will queue sweden_personer_ratsit.mjs --kommun for each selected kommun. Jobs will run asynchronously via the queue.')
                        ->action(function (Collection $records): void {
                            $queued = 0;

                            foreach ($records as $record) {
                                if (empty($record->kommun)) {
                                    continue;
                                }

                                dispatch(new RunPersonerRatsitJob($record->kommun));
                                $queued++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Personer Ratsit queued')
                                ->body("Queued {$queued} job(s).")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
                    ->icon('heroicon-o-plus-circle'),
                Action::make('refreshTable')
                    ->label(' ')
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
                ExcelImportAction::make()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->label('CSV'),
                static::importSqlAction(),
                ExportAction::make()
                    ->label('CSV')
                    ->visible(fn () => auth()->user()->role === 'super')
                    ->exporter(SwedenKommunerExporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('danger'),
                static::exportSqlAction(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->defaultPaginationPageOption(25);
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
            $tableName = 'sweden_kommuner';
            $rows = DB::table($tableName)->get();

            if ($rows->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('Export Failed')
                    ->body('No data to export.')
                    ->send();

                return null;
            }

            $columns = array_keys((array) $rows->first());

            $sql = "INSERT IGNORE INTO `{$tableName}` (`".implode('`, `', $columns)."`) VALUES \n";

            $values = [];
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
            }

            $sql .= implode(",\n", $values).";\n";

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

    private static function importSqlAction(): Action
    {
        return Action::make('importSql')
            ->label('SQL')
            ->icon('heroicon-o-arrow-down-on-square')
            ->color('success')
            ->schema([
                FileUpload::make('file')
                    ->label('SQL File')
                    ->acceptedFileTypes(['application/sql', 'text/plain', '.sql'])
                    ->storeFiles(false)
                    ->maxSize(1048576)
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
            $processedSql = self::processSqlForSafeImport($sqlContent, 'sweden_kommuner');
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
}
