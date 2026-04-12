<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Tables;

use BinaryBuilds\FilamentFailedJobs\Actions\DeleteJobAction;
use BinaryBuilds\FilamentFailedJobs\Actions\DeleteJobsBulkAction;
use BinaryBuilds\FilamentFailedJobs\Actions\RetryJobAction;
use BinaryBuilds\FilamentFailedJobs\Actions\RetryJobsBulkAction;
use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class FailedJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(array_filter([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable(),

                FilamentFailedJobsPlugin::get()->hideConnectionOnIndex ? null : TextColumn::make('connection')->searchable(),

                FilamentFailedJobsPlugin::get()->hideQueueOnIndex ? null : TextColumn::make('queue')->searchable(),

                TextColumn::make('payload')->label('Job')
                    ->formatStateUsing(function ($state) {
                        return json_decode($state, true)['displayName'];
                    })->searchable(),

                TextColumn::make('exception')->wrap()->limit(100),

                TextColumn::make('failed_at')->searchable(),
            ]))
            ->filters(self::getFiltersForIndex(), FilamentFailedJobsPlugin::get()->getFiltersLayout())
            ->recordActions([
                RetryJobAction::make()->iconButton()->tooltip(__('Retry Job')),
                ViewAction::make()->iconButton()->tooltip(__('View Job')),
                DeleteJobAction::make()->iconButton()->tooltip(__('Delete Job')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RetryJobsBulkAction::make(),
                    DeleteJobsBulkAction::make(),
                ]),
                  Action::make(__('Retry Jobs'))
                ->requiresConfirmation()
                ->schema(function () {

                    $queues = FailedJob::query()
                        ->select('queue')
                        ->distinct()
                        ->pluck('queue')
                        ->toArray();

                    $options = [
                        'all' => 'All Queues',
                    ];

                    $descriptions = [
                        'all' => 'Retry all Jobs',
                    ];

                    foreach ($queues as $queue) {
                        $options[$queue] = $queue;
                        $descriptions[$queue] = 'Retry jobs from '.$queue.' queue';
                    }

                    return [
                        Radio::make('queue')
                            ->options($options)
                            ->descriptions($descriptions)
                            ->default('all')
                            ->required(),
                    ];
                })
                ->successNotificationTitle(__('Jobs pushed to queue successfully!'))
                ->action(fn (array $data) => Artisan::call('queue:retry '.$data['queue'])),

            Action::make(__('Prune Jobs'))
                ->requiresConfirmation()
                ->schema([
                    TextInput::make('hours')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->helperText(__("Prune's all failed jobs older than given hours.")),
                ])
                ->color('danger')
                ->successNotificationTitle(__('Jobs pruned successfully!'))
                ->action(fn (array $data) => Artisan::call('queue:prune-failed --hours='.$data['hours'])),
            ]);
    }

    private static function getFiltersForIndex(): array
    {
        $jobs = FailedJob::query()
            ->select(['connection', 'queue', 'payload'])
            ->get()
            ->map(function (FailedJob $failedJob) {
                $failedJob->job = json_decode($failedJob->payload, true)['displayName'];

                return $failedJob;
            });

        $connections = $jobs->pluck('connection', 'connection')->map(fn ($conn) => ucfirst($conn))->toArray();
        $queues = $jobs->pluck('queue', 'queue')->map(fn ($queue) => ucfirst($queue))->toArray();
        $jobNames = $jobs
            ->pluck('job', 'job')
            ->map(fn ($job) => mb_trim($job, '"'))
            ->map(fn ($job) => Str::replace('\\\\', '\\', $job))
            ->toArray();

        return [
            SelectFilter::make('Connection')->options($connections),
            SelectFilter::make('Queue')->options($queues),
            Filter::make('Job')
                ->schema([
                    Select::make('job')->options($jobNames),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['job'],
                            fn (Builder $query, $job): Builder => $query->whereLike('payload', '%'.Str::trim(Str::afterLast($job, '\\'), '"').'%'),
                        );
                }),
            Filter::make('failed_at')
                ->schema([
                    DatePicker::make('failed_at'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['failed_at'],
                            fn (Builder $query, $date): Builder => $query->whereDate('failed_at', '>=', $date),
                        );
                }),
        ];
    }
}
