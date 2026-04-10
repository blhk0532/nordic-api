<?php

declare(strict_types=1);

namespace App\Filament\Resources\JobBatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->limit(24)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('failed_jobs')
                    ->label('Failed')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                TextColumn::make('pending_jobs')
                    ->label('Pending')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('completed_jobs')
                    ->label('Success')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_jobs')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->color(fn (?string $state = null): string => match ($state) {
                        'pending' => 'warning',
                        'complete' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('options')
                    ->label('Options')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->options ? json_encode($record->options, JSON_PRETTY_PRINT) : 'None';
                    })
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cancelled_at')
                    ->label('Cancelled At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('finished_at')
                    ->label('Job Batch Finished At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not finished'),

                TextColumn::make('failed_job_ids')
                    ->label('Fail IDs')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return json_encode($record->failed_job_ids, JSON_PRETTY_PRINT);
                    })
                    ->placeholder('—'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
