<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs\Tables;

use App\Filament\Resources\Jobs\JobResource;
use App\Models\Job;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s') // Auto-refresh every 5 seconds
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('queue')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Job Name')
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payload')
                    ->label('Job Data')
                    ->limit(10)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function ($record) {
                        return json_encode($record->payload, JSON_PRETTY_PRINT);
                    }),
                TextColumn::make('attempts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved_at')
                    ->label('Reserved At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reserved'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'reserved' => 'warning',
                        'delayed' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('available_at')
                    ->label('Available At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([10, 25, 50, 100, 200, 500, 1000])
            ->defaultPaginationPageOption(25)
            ->filters([
                SelectFilter::make('queue')
                    ->label('Queue')
                    ->options(fn () => Job::query()
                        ->select('queue')
                        ->distinct()
                        ->pluck('queue')
                        ->filter()
                        ->mapWithKeys(fn ($v) => [$v => $v])
                        ->toArray())
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ready' => 'Ready',
                        'reserved' => 'Reserved',
                        'delayed' => 'Delayed',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $value = $data['value'];

                        return match ($value) {
                            'reserved' => $query->whereNotNull('reserved_at'),
                            'delayed' => $query->whereNull('reserved_at')->where('available_at', '>', time()),
                            'ready' => $query->whereNull('reserved_at')->where('available_at', '<=', time()),
                            default => $query,
                        };
                    }),

                TernaryFilter::make('reserved')
                    ->label('Reserved')
                    ->queries(
                        fn ($q) => $q->whereNotNull('reserved_at'),
                        fn ($q) => $q->whereNull('reserved_at'),
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => JobResource::getUrl('view', ['record' => $record->getKey()])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('requeue')
                        ->label('Requeue')
                        ->icon('heroicon-o-play')
                        ->requiresConfirmation()
                        ->modalHeading('Requeue selected jobs')
                        ->modalDescription('This will reset selected jobs so they are available to be picked up by the queue workers again.')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                // Reset the reserved time and attempts and set available_at to now so workers can pick them up
                                $record->update([
                                    'reserved_at' => null,
                                    'attempts' => 0,
                                    'available_at' => time(),
                                ]);
                                $count++;
                            }

                            Notification::make()
                                ->title('Requeue Completed')
                                ->body("Requeued {$count} job(s)")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
