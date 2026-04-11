<?php

declare(strict_types=1);

namespace App\Filament\Resources\HittaDatas\Tables;

use App\Actions\TransferHittaDataToRingaDataAction;
use App\Exports\HittaDataExporter;
use App\Jobs\BackupHittaData;
use App\Jobs\ImportHittaData;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HittaDatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('personnamn')
                    ->label('Personnamn')
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),

                TextColumn::make('gatuadress')
                    ->label('Gatuadress')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('postnummer')
                    ->label('Postnummer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('postort')
                    ->label('Postort')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kon')
                    ->label('Kön')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Man' => 'info',
                        'Kvinna' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('alder')
                    ->label('Ålder')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_hus')
                    ->label('Är Hus')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('telefon')
                    ->label('Telefon')
                    ->copyable()
                    ->tooltip('Klicka för att kopiera')
                    ->limit(20)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('telefonnummer')
                    ->label('Telefoner')
                    ->sortable()
                //    ->limit(12)
                    ->toggleable()
                    ->toggledHiddenByDefault(true),

                TextColumn::make('bostadspris')
                    ->label('Bostadspris')
                    ->money('SEK')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                IconColumn::make('is_telefon')
                    ->label('Har Telefon')
                    ->boolean()
                    ->toggleable()
                    ->toggledHiddenByDefault(true),

                IconColumn::make('is_ratsit')
                    ->label('I Ratsit')
                    ->boolean()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('created_at')
                    ->label('Skapad')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('updated_at')
                    ->label('Uppdaterad')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                TernaryFilter::make('is_telefon')
                    ->label('Har Telefon')
                    ->default(true),

                TernaryFilter::make('is_active')
                    ->label('Aktiv'),

                TernaryFilter::make('is_ratsit')
                    ->label('I Ratsit'),

                TernaryFilter::make('is_hus')
                    ->label('Är Hus')
                    ->default(true),

                SelectFilter::make('kon')
                    ->label('Kön')
                    ->options([
                        'Man' => 'Man',
                        'Kvinna' => 'Kvinna',
                    ]),

                Filter::make('postnummer')
                    ->schema([
                        TextInput::make('postnummer')
                            ->label('Postnummer')
                            ->placeholder('Sök efter exakt postnummer (t.ex. 184 44)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['postnummer'],
                                fn (Builder $query, $search): Builder => $query->where('postnummer', '=', $search)
                            );
                    }),

                Filter::make('postort')
                    ->schema([
                        TextInput::make('postort')
                            ->label('Postort')
                            ->placeholder('Sök efter postort'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['postort'],
                                fn (Builder $query, $search): Builder => $query->where('postort', 'like', "%{$search}%")
                            );
                    }),
            ])
        //    ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                //       ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->role === 'super')
                        ->exporter(HittaDataExporter::class),
                    DeleteBulkAction::make(),
                    BulkAction::make('transferToRingaData')
                        ->label('Transfer to Ringa Data')
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, array $data): void {
                            $action = new TransferHittaDataToRingaDataAction;
                            $action->handle($records, $data);

                            Notification::make()
                                ->title('Success')
                                ->body(count($records).' records transferred to Ringa Data')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
                Action::make('import')
                    ->label('Import Data')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->action(function (array $data): void {
                        $this->handleImport([$data['file']], $data['file_type']);
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
                                    'sqlite' => 'Upload a SQLite database file containing a hitta_data table.',
                                    default => '',
                                };
                            }),
                    ])
                    ->modalHeading('Import Hitta Data')
                    ->modalDescription('Choose a file type and upload your data file to import into the Hitta database.')
                    ->modalSubmitActionLabel('Start Import'),

                Action::make('backupDatabase')
                    ->label('Backup DB')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->visible(fn () => auth()->user()->role === 'super')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Backup Hitta Data Table')
                    ->modalDescription('This will queue a background job to create a SQLite backup of the hitta_data table in the database/export folder. You will receive a notification when the backup is complete.')
                    ->modalSubmitActionLabel('Queue Backup Job')
                    ->action(function (): void {
                        try {
                            // Dispatch the backup job
                            BackupHittaData::dispatch();

                            Notification::make()
                                ->title('Backup Job Queued')
                                ->body('The Hitta data backup job has been queued and will run in the background.')
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
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->striped();
    }

    protected function handleImport(array $files, string $fileType): void
    {
        $filePath = $files[0]; // FileUpload returns array
        /** @var User|null $authUser */
        $authUser = auth()->user();
        $userId = $authUser?->id;

        try {
            // Dispatch the appropriate import job
            match ($fileType) {
                'csv' => ImportHittaData::dispatch($filePath, 'csv', $userId),
                'xlsx' => ImportHittaData::dispatch($filePath, 'xlsx', $userId),
                'sqlite' => ImportHittaData::dispatch($filePath, 'sqlite', $userId),
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
}
