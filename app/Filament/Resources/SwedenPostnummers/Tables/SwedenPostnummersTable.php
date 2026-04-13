<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPostnummers\Tables;

use App\Exports\PeopleExporter;
use App\Filament\Resources\SwedenPostnummers\Actions\RunRatsitHittaAction;
use App\Filament\Resources\SwedenPostnummers\Actions\RunRatsitHittaBulkAction;
use App\Jobs\RunHittaDataScriptJob;
use App\Jobs\RunMerinfoDataScriptJob;
use App\Jobs\RunRatsitDataScriptJob;
use App\Jobs\RunScriptForPostnummerJob;
use App\Models\HittaData;
use App\Models\MerinfoData;
use App\Models\Merinfo;
use App\Models\RatsitData;
use App\Models\SwedenPersoner;
use App\Models\SwedenPostnummer;
use App\Services\GoogleSheets\PeopleSheetsSyncService;
use App\Services\Import\PeopleImportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class SwedenPostnummersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('csv_id')
                    ->hidden()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                TextColumn::make('postort')
                    ->label('Postort')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                TextColumn::make('kommun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Kommun')
                    ->searchable(),
                TextColumn::make('lan')
                    ->sortable()
                    ->label('Landskap')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('country')
                    ->hidden()
                    ->label('Land')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),
                TextColumn::make('personer')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label('Pers')
                    ->sortable(),
                TextColumn::make('foretag')
                    ->numeric()
                    ->hidden()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('personer_saved')
                    ->label('Saved')
                    ->hidden()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('live_ratsit_count')
                    ->label('Ratsit')
                    ->numeric()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('live_hitta_count')
                    ->label('Hitta')
                    ->numeric()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('live_merinfo_count')
                    ->label('Mer')
                    ->numeric()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('live_personer_count')
                    ->label('DB')
                    ->numeric()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                ToggleColumn::make('personer_merinfo_queue')
                    ->label('Queue')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->hidden()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->hidden()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->hidden()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->hidden()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50, 100, 200, 500])
            ->defaultPaginationPageOption(10)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withLiveCounts())
            ->filters([
                Filter::make('has_personer')
                    ->label('Has Personer')
                    ->default(true)
                    ->query(fn (Builder $query) => $query->where('personer', '>', 0)),
                SelectFilter::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->multiple()
                    ->options(fn (): array => SwedenPostnummer::query()
                        ->whereNotNull('kommun')
                        ->where('kommun', '<>', '')
                        ->orderBy('kommun')
                        ->pluck('kommun', 'kommun')
                        ->all()),
                SelectFilter::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->multiple()
                    ->options(fn (): array => SwedenPostnummer::query()
                        ->whereNotNull('postort')
                        ->where('postort', '<>', '')
                        ->orderBy('postort')
                        ->pluck('postort', 'postort')
                        ->all()),
                SelectFilter::make('lan')
                    ->label('Län')
                    ->searchable()
                    ->multiple()
                    ->options(fn (): array => SwedenPostnummer::query()
                        ->whereNotNull('lan')
                        ->where('lan', '<>', '')
                        ->orderBy('lan')
                        ->pluck('lan', 'lan')
                        ->all()),
            ])
            ->recordActions([
                RunRatsitHittaAction::make(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RunRatsitHittaBulkAction::make(),
                    DeleteBulkAction::make(),
                    BulkAction::make('syncToGoogleSheets')
                        ->label('Sync to Sheets')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Sync selected people to Google Sheets')
                        ->modalDescription('Syncs the selected records to Google Sheets.')
                        ->schema([
                            TextInput::make('spreadsheet_id')
                                ->label('Spreadsheet ID')
                                ->default(config('services.google_sheets.default_spreadsheet_id'))
                                ->required(),
                            TextInput::make('sheet_name')
                                ->label('Sheet tab name')
                                ->default(config('services.google_sheets.default_sheet_name', 'People'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                $count = app(PeopleSheetsSyncService::class)->syncRecords(
                                    records: $records,
                                    spreadsheetId: (string) ($data['spreadsheet_id'] ?? ''),
                                    sheetName: (string) ($data['sheet_name'] ?? 'People'),
                                );

                                Notification::make()
                                    ->success()
                                    ->title('Google Sheets sync completed')
                                    ->body("Synced {$count} people to Google Sheets.")
                                    ->send();
                            } catch (\Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->danger()
                                    ->title('Google Sheets sync failed')
                                    ->body($exception->getMessage())
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('selectionSummary')
                        ->label('Selection summary')
                        ->icon('heroicon-o-chart-bar')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $total = $records->count();
                            $totalPersoner = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer);
                            $totalRatsit = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_ratsit_saved);
                            $totalHitta = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_hitta_saved);
                            $totalMerinfo = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_merinfo_saved);

                            Notification::make()
                                ->title('Selection Summary')
                                ->success()
                                ->body("Total: {$total} · Persons: {$totalPersoner} · Ratsit: {$totalRatsit} · Hitta: {$totalHitta} · Merinfo: {$totalMerinfo}")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                BulkAction::make('runScript')
                    ->label('Run Queue Script')
                    ->icon('heroicon-o-command-line')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Run script from scripts folder')
                    ->modalDescription('Select a script from /scripts and run it now.')
                    ->schema([
                        Select::make('script_name')
                            ->label('Script')
                            ->options(function (): array {
                                $scriptPaths = glob(base_path('scripts/*')) ?: [];

                                return collect($scriptPaths)
                                    ->filter(fn (string $path): bool => is_file($path))
                                    ->map(fn (string $path): string => basename($path))
                                    ->sort()
                                    ->values()
                                    ->mapWithKeys(fn (string $name): array => [$name => $name])
                                    ->all();
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        try {
                            $scriptName = trim((string) ($data['script_name'] ?? ''));

                            if ($scriptName === '' || ! preg_match('/^[A-Za-z0-9._-]+$/', $scriptName)) {
                                throw new \Exception('Invalid script name.');
                            }

                            $scriptPath = base_path("scripts/{$scriptName}");

                            if (! is_file($scriptPath)) {
                                throw new \Exception("Script not found: {$scriptName}");
                            }

                            $queued = 0;

                            foreach ($records as $record) {
                                dispatch(new RunScriptForPostnummerJob(
                                    scriptName: $scriptName,
                                    postNummer: (string) $record->postnummer,
                                ));

                                $queued++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Script jobs queued')
                                ->body("Queued {$queued} job(s) for {$scriptName} on queue: script.")
                                ->send();
                        } catch (\Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title('Script failed')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('setAllQueueFlags')
                    ->label('Set All Queue = 1')
                    ->icon('heroicon-o-queue-list')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Set Queue Columns')
                    ->modalDescription('This will set personer_hitta_queue, personer_merinfo_queue, and personer_ratsit_queue to 1 for all selected records.')
                    ->modalSubmitActionLabel('Set Queue = 1')
                    ->action(function (Collection $records): void {
                        $updated = 0;

                        foreach ($records as $record) {
                            SwedenPostnummer::query()
                                ->whereKey($record->getKey())
                                ->update([
                                    'personer_hitta_queue' => 1,
                                    'personer_merinfo_queue' => 1,
                                    'personer_ratsit_queue' => 1,
                                ]);

                            $updated++;
                        }

                        Notification::make()
                            ->success()
                            ->title('Queue Columns Updated')
                            ->body("Set all queue columns to 1 for {$updated} record(s).")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('checkDbCounts')
                    ->label('Check DB Counts')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Check Database Counts')
                    ->modalDescription('This will count matching rows in hitta_data, merinfo_data, and ratsit_data and update personer_hitta_saved, personer_merinfo_saved, and personer_ratsit_saved for selected records.')
                    ->modalSubmitActionLabel('Update Counts')
                    ->action(function (Collection $records): void {
                        $updated = 0;

                        foreach ($records as $record) {
                            $postNummer = (string) $record->postnummer;
                            $normalizedPostNummer = $record->csv_id;

                            $hittaCount = HittaData::query()
                                ->where('postnummer', $postNummer)
                                ->count();

                            $merinfoCount = MerinfoData::query()
                                ->where('postnummer', $postNummer)
                                ->count();

                            $ratsitCount = RatsitData::query()
                                ->where('postnummer', $postNummer)
                                ->count();

                            SwedenPostnummer::query()
                                ->whereKey($record->getKey())
                                ->update([
                                    'personer_hitta_saved' => $hittaCount,
                                    'personer_merinfo_saved' => $merinfoCount,
                                    'personer_ratsit_saved' => $ratsitCount,
                                ]);

                            $updated++;
                        }

                        Notification::make()
                            ->success()
                            ->title('DB Counts Updated')
                            ->body("Updated saved counts for {$updated} record(s).")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('runAllData')
                    ->label('Run All Data Scripts')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Run All Data Scripts')
                    ->modalDescription('This will queue data collection jobs (Hitta, Merinfo, Ratsit) for all selected records.')
                    ->modalSubmitActionLabel('Run All Scripts')
                    ->action(function (Collection $records): void {
                        $batchCount = 0;
                        $totalJobs = 0;
                        foreach ($records as $record) {
                            $postNummer = (string) $record->postnummer;
                            $normalizedPostNummer = str_replace(' ', '', $postNummer);
                            $batch = Bus::batch([
                                new RunHittaDataScriptJob($normalizedPostNummer),
                                new RunMerinfoDataScriptJob($normalizedPostNummer),
                                new RunRatsitDataScriptJob($postNummer),
                            ])
                                ->name("SwedenPostnummer {$postNummer} data scripts")
                                ->onConnection(config('queue.default'))
                                ->onQueue('ratsit-hitta')
                                ->allowFailures()
                                ->dispatch();
                            $batchCount++;
                            $totalJobs += 3;
                        }
                        Notification::make()
                            ->success()
                            ->title('Batches Queued')
                            ->body("Queued {$batchCount} batch(es) with {$totalJobs} total job(s) for data collection.")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
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
                        $updated = 0;

                        foreach ($records as $record) {
                            $postNummer = (string) $record->postnummer;
                            $normalizedPostNummer = $record->csv_id;

                            $hittaCount = HittaData::query()
                                ->where('postnummer', $postNummer)
                                ->count();

                            $merinfoCount = Merinfo::query()
                                ->where('address->zip', $postNummer)
                                ->count();

                            $ratsitCount = RatsitData::query()
                                ->where('postnummer', $postNummer)
                                ->count();

                            SwedenPostnummer::query()
                                ->whereKey($record->getKey())
                                ->update([
                                    'personer_hitta_saved' => $hittaCount,
                                    'personer_merinfo_saved' => $merinfoCount,
                                    'personer_ratsit_saved' => $ratsitCount,
                                ]);

                            $updated++;
                        }

                        Notification::make()
                            ->success()
                            ->title('DB Counts Updated')
                            ->body("Updated saved counts for {$updated} record(s).")
                            ->send();
                    }),
                Action::make('importFromFile')
                    ->label('Import')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->schema([
                        FileUpload::make('import_file')
                            ->label('CSV or XLSX file')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->maxSize(50 * 1024), // 50MB
                    ])
                    ->action(function (array $data): void {
                        try {
                            $filePath = $data['import_file'];

                            if (! is_string($filePath)) {
                                throw new \Exception('Invalid file upload');
                            }

                            $count = app(PeopleImportService::class)->importFromFile(
                                storage_path("app/{$filePath}")
                            );

                            Notification::make()
                                ->success()
                                ->title('Import completed')
                                ->body("Imported/updated {$count} people into personer.")
                                ->send();
                        } catch (\Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title('Import failed')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
                Action::make('importFromGoogleSheets')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Import people from Google Sheets')
                    ->modalDescription('Reads rows from the sheet and upserts into personer using personnummer or name+address+postnummer.')
                    ->schema([
                        TextInput::make('spreadsheet_id')
                            ->label('Spreadsheet ID')
                            ->default(config('services.google_sheets.default_spreadsheet_id'))
                            ->required(),
                        TextInput::make('sheet_name')
                            ->label('Sheet tab name')
                            ->default(config('services.google_sheets.default_sheet_name', 'People'))
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        try {
                            $count = app(PeopleSheetsSyncService::class)->importIntoDatabase(
                                spreadsheetId: (string) ($data['spreadsheet_id'] ?? ''),
                                sheetName: (string) ($data['sheet_name'] ?? 'People'),
                            );

                            Notification::make()
                                ->success()
                                ->title('Google Sheets import completed')
                                ->body("Imported {$count} people into personer.")
                                ->send();
                        } catch (\Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title('Google Sheets import failed')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
                ExportAction::make()
                    ->label('Export')
                    ->visible(fn () => Auth::user()->role === 'super')
                    ->exporter(PeopleExporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('danger'),
            ]);
    }

}
