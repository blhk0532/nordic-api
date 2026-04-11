<?php

declare(strict_types=1);

namespace App\Filament\Resources\RatsitDatas\Tables;

use App\Actions\TransferRatsitDataToRingaDataAction;
use App\Jobs\BackupRatsitData;
use App\Jobs\ImportRatsitData;
use App\Models\RatsitData;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RatsitDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),
                TextColumn::make('gatuadress')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('postnummer')
                    ->label('Postnr')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postort')
                    ->label('City')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('personnamn')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),
                TextColumn::make('personnummer')
                    ->label('Personr')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('alder')
                    ->label('Age')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('kon')
                    ->label('Gender')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('forsamling')
                    ->label('Parish')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
                TextColumn::make('kommun')
                    ->label('Municipality')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
                TextColumn::make('lan')
                    ->label('County')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
                TextColumn::make('telfonnummer.0')
                    ->label('Phone')
                    ->words(1)
                    ->toggleable()
                    ->toggledHiddenByDefault(false),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All records')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                TernaryFilter::make('has_house')
                    ->label('Owns house')
                    ->default(true)
                    ->query(
                        fn ($query) => $query->whereNotNull('agandeform')
                            ->where('bostadstyp', '!=', 'Lägenhet')
                            ->where(function ($query) {
                                $query->where('agandeform', 'Äganderätt')
                                    ->orWhere('agandeform', 'Tomträtt');
                            })
                    ),
                SelectFilter::make('postort')
                    ->label('City')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('postort')
                            ->distinct()
                            ->orderBy('postort')
                            ->pluck('postort', 'postort')
                            ->toArray();
                    }),

                SelectFilter::make('kommun')
                    ->label('Municipality')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('kommun')
                            ->distinct()
                            ->orderBy('kommun')
                            ->pluck('kommun', 'kommun')
                            ->toArray();
                    }),

                SelectFilter::make('lan')
                    ->label('State')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('lan')
                            ->distinct()
                            ->orderBy('lan')
                            ->pluck('lan', 'lan')
                            ->toArray();
                    }),

                SelectFilter::make('agandeform')
                    ->label('Ownership Form')
                    ->multiple()
                    ->searchable()
                    ->default('Äganderätt')
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('agandeform')
                            ->distinct()
                            ->orderBy('agandeform')
                            ->pluck('agandeform', 'agandeform')
                            ->toArray();
                    }),

                SelectFilter::make('bostadstyp')
                    ->label('Housing Type')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return RatsitData::query()
                            ->whereNotNull('bostadstyp')
                            ->distinct()
                            ->orderBy('bostadstyp')
                            ->pluck('bostadstyp', 'bostadstyp')
                            ->toArray();
                    }),

                // Filter: has phone (telefon not empty)
                TernaryFilter::make('has_telefon')
                    ->label('Has phone')
                    ->default(true)
                    ->query(
                        fn ($query) => $query->whereNotNull('telefon')
                            ->where('telefon', '<>', '')
                            // handle JSON empty array serialized as '[]' or 'null-like' strings
                            ->where('telefon', '<>', '[]')
                    ),
                Filter::make('postnummer')
                    ->label('Postnummer')
                    ->schema([
                        TextInput::make('postnummer')
                            ->label('Postnummer'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['postnummer'] ?? null,
                            fn ($query, $postnummer) => $query->where('postnummer', 'like', "%{$postnummer}%")
                        );
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100, 250, 500, 1000])
            ->defaultPaginationPageOption(10)
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'super'),
                    BulkAction::make('setQueued')
                        ->label('Queue Records')
                        ->icon('heroicon-o-clock')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (RatsitData $record) => $record->update(['is_queued' => true]));

                            Notification::make()
                                ->title('Success')
                                ->body(count($records).' records queued')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('transferToRingaData')
                        ->label('Transfer to Ringa Data')
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, array $data): void {
                            $action = new TransferRatsitDataToRingaDataAction;
                            $action->handle($records, $data);

                            Notification::make()
                                ->title('Success')
                                ->body(count($records).' records transferred to Ringa Data')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
                static::exportSqlAction(),
                Action::make('import')
                    ->visible(fn () => auth()->user()->role === 'super')
                    ->label('Import Data')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->action(function (array $data): void {
                        $this->handleImport($data['file'], $data['file_type']);
                    })
                    ->schema([
                        Select::make('file_type')
                            ->label('File Type')
                            ->options([
                                'csv' => 'CSV',
                                'xlsx' => 'Excel (XLSX/XLS)',
                                'sqlite' => 'SQLite Database',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('file', null); // Clear file when type changes
                            }),

                        FileUpload::make('file')
                            ->label('File')
                            ->required()
                            ->directory('imports')
                            ->visibility('private')
                            ->acceptedFileTypes(function (Get $get) {
                                return match ($get('file_type')) {
                                    'csv' => ['text/csv', 'text/plain'],
                                    'xlsx' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                                    'sqlite' => ['application/x-sqlite3', 'application/octet-stream'],
                                    default => [],
                                };
                            })
                            ->maxSize(function (Get $get) {
                                return match ($get('file_type')) {
                                    'sqlite' => 51200, // 50MB for SQLite
                                    default => 10240, // 10MB for others
                                };
                            })
                            ->helperText(function (Get $get) {
                                return match ($get('file_type')) {
                                    'csv' => 'Upload a CSV file with headers matching database columns.',
                                    'xlsx' => 'Upload an Excel file (.xlsx or .xls) with data in the first sheet.',
                                    'sqlite' => 'Upload a SQLite database file containing a ratsit_data table.',
                                    default => '',
                                };
                            }),
                    ])
                    ->modalHeading('Import Ratsit Data')
                    ->modalDescription('Choose a file type and upload your data file to import into the Ratsit database.')
                    ->modalSubmitActionLabel('Start Import'),

                Action::make('backupDatabase')
                    ->label('Backup DB')
                    ->visible(fn () => auth()->user()->role === 'super')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Backup Ratsit Data Table')
                    ->modalDescription('This will queue a background job to create a SQLite backup of the ratsit_data table in the database/export folder. You will receive a notification when the backup is complete.')
                    ->modalSubmitActionLabel('Queue Backup Job')
                    ->action(function (): void {
                        try {
                            // Dispatch the backup job
                            BackupRatsitData::dispatch();

                            Notification::make()
                                ->title('Backup Job Queued')
                                ->body('The Ratsit data backup job has been queued and will run in the background.')
                                ->success()
                                ->send();

                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed to Queue Backup')
                                ->body('Error queuing backup job: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                CreateAction::make()
                    ->label(' ')
                    ->icon('heroicon-o-plus')
                    ->color('gray'),
            ]);
    }

    private static function exportSqlAction(): Action
    {
        return Action::make('exportSql')
            ->label('SQL')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('danger')
            ->visible(fn () => auth()->user()->role === 'super')
            ->action(function () {
                return self::handleSqlExport();
            });
    }

    protected function handleImport(array|string $files, string $fileType): void
    {
        $filePath = is_array($files) ? $files[0] : $files;
        /** @var User|null $authUser */
        $authUser = auth()->user();
        $userId = $authUser?->id;

        try {
            // Dispatch the appropriate import job
            match ($fileType) {
                'csv' => ImportRatsitData::dispatch($filePath, 'csv', $userId),
                'xlsx' => ImportRatsitData::dispatch($filePath, 'xlsx', $userId),
                'sqlite' => ImportRatsitData::dispatch($filePath, 'sqlite', $userId),
            };

            Notification::make()
                ->title('Import Job Queued')
                ->body("The {$fileType} import job has been queued and will run in the background. You will receive a notification when it completes.")
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Failed to Queue Import')
                ->body('Error queuing import job: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    private static function handleSqlExport()
    {
        try {
            $tableName = 'ratsit_data';
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

        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->send();

            return null;
        }
    }
}
