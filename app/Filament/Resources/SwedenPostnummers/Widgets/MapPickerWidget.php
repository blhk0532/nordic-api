<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPostnummers\Widgets;

use App\Exports\PeopleExporter;
use App\Exports\SwedenPostnummerExporter;
use App\Filament\Resources\SwedenPostnummers\Actions\RunRatsitHittaAction;
use App\Filament\Resources\SwedenPostnummers\Actions\RunRatsitHittaBulkAction;
use App\Jobs\CheckDbCountsJob;
use App\Jobs\RunHittaDataScriptJob;
use App\Jobs\RunMerinfoDataScriptJob;
use App\Jobs\RunRatsitDataScriptJob;
use App\Jobs\RunScriptForPostnummerJob;
use App\Models\SwedenPostnummer;
use App\Services\GoogleSheets\PeopleSheetsSyncService;
use App\Services\Import\PeopleImportService;
use Cheesegrits\FilamentGoogleMaps\Actions\GoToAction;
use Cheesegrits\FilamentGoogleMaps\Filters\MapIsFilter;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Waad\FilamentImportWizard\Actions\ImportWizardAction as ExcelImportAction;

#[\AllowDynamicProperties]
class MapPickerWidget extends MapTableWidget
{
    public $data;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->toolbarActions($this->getToolbarActions())
            ->defaultSort('personer_merinfo_queue', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('personer_merinfo_queue', 'desc')->orderBy('updated_at', 'desc'));
    }

    protected function getTableQuery(): Builder
    {
        return SwedenPostnummer::query()->withLiveCounts();
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate($this->getTableRecordsPerPage() == 'all' ? $query->count() : $this->getTableRecordsPerPage());
    }

    protected function getTableColumns(): array
    {
        return [
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
                ->label('Merinfo')
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
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('has_personer')
                ->label('Has Personer')
                ->default(true)
                ->query(fn (Builder $query) => $query->where('personer', '>', 0)),
            Filter::make('is_queued')
                ->label('Is Queued')
                ->default(false)
                ->query(fn (Builder $query) => $query->where('personer_merinfo_queue', true)),
            SelectFilter::make('postnummer')
                ->label('Postnr')
                ->searchable()
                ->multiple()
                ->options(fn (): array => Cache::remember('filter_options_postnummer', 3600, fn () => SwedenPostnummer::query()
                    ->whereNotNull('postnummer')
                    ->where('postnummer', '<>', '')
                    ->orderBy('postnummer')
                    ->pluck('postnummer', 'postnummer')
                    ->all())),
            SelectFilter::make('postort')
                ->label('Postort')
                ->searchable()
                ->multiple()
                ->options(fn (): array => Cache::remember('filter_options_postort', 3600, fn () => SwedenPostnummer::query()
                    ->whereNotNull('postort')
                    ->where('postort', '<>', '')
                    ->orderBy('postort')
                    ->pluck('postort', 'postort')
                    ->all())),
            SelectFilter::make('kommun')
                ->label('Kommun')
                ->searchable()
                ->multiple()
                ->options(fn (): array => Cache::remember('filter_options_kommun', 3600, fn () => SwedenPostnummer::query()
                    ->whereNotNull('kommun')
                    ->where('kommun', '<>', '')
                    ->orderBy('kommun')
                    ->pluck('kommun', 'kommun')
                    ->all())),
            SelectFilter::make('lan')
                ->label('Län')
                ->searchable()
                ->multiple()
                ->options(fn (): array => Cache::remember('filter_options_lan', 3600, fn () => SwedenPostnummer::query()
                    ->whereNotNull('lan')
                    ->where('lan', '<>', '')
                    ->orderBy('lan')
                    ->pluck('lan', 'lan')
                    ->all())),
            MapIsFilter::make('map')
                ->label('Map Bounds'),
        ];
    }

    protected function getFilters(): ?array
    {
        return null;
    }

    public function getConfig(): array
    {
        $config = parent::getConfig();

        return array_merge($config, [
            'center' => [
                'lat' => 62.5333,
                'lng' => 16.6667,
            ],
            'zoom' => 8,
            'fit' => true,
        ]);
    }

    protected function getTableActions(): array
    {
        return [
            RunRatsitHittaAction::make(),
            //  ViewAction::make(),
            EditAction::make(),
            GoToAction::make()
                ->label('Map')
                ->alpineClickHandler(function (Model $record): HtmlString {
                    $latLngFields = $record::getLatLngAttributes();

                    return new HtmlString(sprintf(
                        "const section = document.getElementById('filament-google-maps-widget-on-table'); if (section) { section.classList.remove('is-collapsed'); section.classList.remove('fi-collapsed'); } \$dispatch('filament-google-maps::widget/setMapCenter', {lat: %f, lng: %f, zoom: %d});",
                        round((float) $record->{$latLngFields['latitude']}, 8),
                        round((float) $record->{$latLngFields['longitude']}, 8),
                        12,
                    ));
                })
                ->zoom(12),
        ];
    }

    protected function getToolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
                BulkAction::make('setAllQueueFlags')
                    ->label('Set All Queued')
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
                BulkAction::make('setAllNotQueueFlags')
                    ->label('Set All No Queue')
                    ->icon('heroicon-o-queue-list')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Set Queue Columns')
                    ->modalDescription('This will set personer_hitta_queue, personer_merinfo_queue, and personer_ratsit_queue to 0 for all selected records.')
                    ->modalSubmitActionLabel('Set Queue = 0')
                    ->action(function (Collection $records): void {
                        $updated = 0;

                        foreach ($records as $record) {
                            SwedenPostnummer::query()
                                ->whereKey($record->getKey())
                                ->update([
                                    'personer_hitta_queue' => 0,
                                    'personer_merinfo_queue' => 0,
                                    'personer_ratsit_queue' => 0,
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
                BulkAction::make('syncToGoogleSheets')
                    ->label('Sync to Sheets')
                    ->icon('heroicon-o-table-cells')
                    ->color('warning')
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
                BulkAction::make('runAllData')
                    ->label('Run All Scrapers')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Run All Scrapers')
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

                BulkAction::make('runScript')
                    ->label('Run Queue Script')
                    ->icon('heroicon-o-command-line')
                    ->color('success')
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
                RunRatsitHittaBulkAction::make(),

                BulkAction::make('checkDbCounts')
                    ->label('Check DB Counts')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Check Database Counts')
                    ->modalDescription('This will count matching rows in hitta_data, merinfo_data, and ratsit_data and update personer_hitta_saved, personer_merinfo_saved, and personer_ratsit_saved for selected records.')
                    ->modalSubmitActionLabel('Update Counts')
                    ->action(function (Collection $records): void {
                        CheckDbCountsJob::dispatch($records);

                        Notification::make()
                            ->info()
                            ->title('Update Started')
                            ->body('The database counts are being updated in the background on the "sweden-postnummer" queue.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('selectionSummary')
                    ->label('Count Databases')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function (Collection $records): void {
                        $total = $records->count();
                        $totalPersoner = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer);
                        $totalRatsit = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_ratsit_saved);
                        $totalHitta = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_hitta_saved);
                        $totalMerinfo = $records->sum(fn (SwedenPostnummer $record): int => (int) $record->personer_merinfo_saved);

                        Notification::make()
                            ->title('Database Match Summary')
                            ->success()
                            ->body("Areas: {$total} · Persons: {$totalPersoner} · Ratsit: {$totalRatsit} · Hitta: {$totalHitta} · Merinfo: {$totalMerinfo}")
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]),
            Action::make('create')
                ->label(' ')
                ->color('')
                ->icon('heroicon-o-plus-circle'),
            Action::make('importFromFile')
                ->label('Import')
                ->icon('heroicon-o-arrow-down-tray')
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
                ->icon('heroicon-o-arrow-down-on-square')
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
                ->visible(fn () => auth()->user()->role === 'super')
                ->label('Export')
                ->exporter(PeopleExporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger'),
            ExcelImportAction::make()
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->label('CSV'),
            static::importSqlAction(),
            ExportAction::make()
                ->label('CSV')
                ->visible(fn () => auth()->user()->role === 'super')
                ->exporter(SwedenPostnummerExporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger'),
            static::exportSqlAction(),
        ];
    }

    public function isMapPicker(): bool
    {
        return true;
    }

    protected function getMapFields(): array
    {
        return [
            'latitude',
            'longitude',
        ];
    }

    protected function getMapLabel(): string
    {
        return 'sverige';
    }

    public function mount(): void
    {
        $this->form->fill([
            'address_search' => null,
            'street' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'location' => [
                'lat' => 62.5333,
                'lng' => 16.6667,
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('address_search')
                            ->label('Address Search')
                            ->placeholder('Search by street, city, or postal code')
                            ->maxLength(255)
                            ->columnSpanFull(),

                    ]),

            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Here you can dispatch an event or emit the selected location
        $this->dispatch('location-selected', [
            'latitude' => $data['location']['lat'],
            'longitude' => $data['location']['lng'],
        ])->self();
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
            $tableName = 'sweden_postnummer';
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
            $processedSql = self::processSqlForSafeImport($sqlContent, 'sweden_postnummer');
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
