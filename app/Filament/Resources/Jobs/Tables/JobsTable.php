<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs\Tables;

use App\Filament\Resources\Jobs\JobResource;
use App\Models\Job;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
                Action::make('startWorker')
                    ->label('Start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Start Queue Worker')
                    ->modalDescription('This will start a background queue worker to process jobs from the postnummer-updates queue.')
                    ->modalSubmitActionLabel('Start Worker')
                    ->action(function () {
                        try {
                            // Check if exec function is available
                            if (! function_exists('exec')) {
                                throw new Exception('exec() function is disabled on this server');
                            }

                            // Check if worker is already running
                            $output = [];
                            @exec('ps aux | grep "queue:work" | grep -v grep', $output);

                            if (count($output) > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('Queue Worker Already Running')
                                    ->body('A queue worker is already active.')
                                    ->send();

                                return;
                            }

                            // Start queue worker in background
                            if (! function_exists('shell_exec')) {
                                throw new Exception('shell_exec() function is disabled on this server');
                            }

                            $command = 'cd '.base_path().' && nohup php artisan queue:work --queue=postnummer-updates --tries=3 --timeout=300 > /dev/null 2>&1 & echo $!';
                            $pid = @shell_exec($command);

                            if ($pid) {
                                Notification::make()
                                    ->success()
                                    ->title('Queue Worker Started')
                                    ->body("Queue worker started with PID: {$pid}")
                                    ->send();
                            } else {
                                throw new Exception('Failed to start queue worker');
                            }
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Start Worker')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('stopWorker')
                    ->label('Stop')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Stop Queue Worker')
                    ->modalDescription('This will stop all running queue workers. Jobs in progress will be interrupted.')
                    ->modalSubmitActionLabel('Stop Worker')
                    ->action(function () {
                        try {
                            // Check if exec function is available
                            if (! function_exists('exec')) {
                                throw new Exception('exec() function is disabled on this server');
                            }

                            // Find and kill queue worker processes
                            $output = [];
                            @exec('ps aux | grep "queue:work" | grep -v grep | awk \'\'{print $2}\'\' ', $output);

                            if (empty($output)) {
                                Notification::make()
                                    ->warning()
                                    ->title('No Queue Workers Running')
                                    ->body('There are no active queue workers to stop.')
                                    ->send();

                                return;
                            }

                            foreach ($output as $pid) {
                                @exec("kill {$pid}");
                            }

                            Notification::make()
                                ->success()
                                ->title('Queue Worker Stopped')
                                ->body('All queue workers have been stopped.')
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Stop Worker')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('clearFailedJobs')
                    ->label('Clear')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Clear Failed Jobs')
                    ->modalDescription('This will delete all failed jobs from the database. This action cannot be undone.')
                    ->modalSubmitActionLabel('Clear Failed Jobs')
                    ->action(function () {
                        try {
                            $count = DB::table('failed_jobs')->count();
                            DB::table('failed_jobs')->truncate();

                            Notification::make()
                                ->success()
                                ->title('Failed Jobs Cleared')
                                ->body("Cleared {$count} failed job(s).")
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Clear Jobs')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                CreateAction::make(),

                Action::make('Job Batches')
                    ->label('Job Batches')
                    ->icon('heroicon-o-bolt-slash')
                    ->color('lightning')
                    ->action(function () {}),

            ]);
    }
}
