<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs;

use BackedEnum;
use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Pages\ListFailedJobs;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Pages\ViewFailedJob;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Schemas\FailedJobInfolist;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Tables\FailedJobsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FailedJobResource extends Resource
{
    public static string|null|UnitEnum $navigationGroup = 'Settings';

    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    public static function infolist(Schema $schema): Schema
    {
        return FailedJobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FailedJobsTable::configure($table)
            ->defaultSort('id', 'desc');
    }

    public static function canAccess(): bool
    {
        return self::getPlugin()->isAuthorized();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Queue JOBS';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) FailedJob::query()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationLabel(): string
    {
        return self::getPlugin()->getNavigationLabel() ?: 'Failed Jobs';
    }

    public static function getNavigationSort(): ?int
    {
        return self::getPlugin()->getNavigationSort();
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-queue-list';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
            'view' => ViewFailedJob::route('/{record}'),
        ];
    }

    protected static function getPlugin(): FilamentFailedJobsPlugin
    {
        /** @var FilamentFailedJobsPlugin */
        return filament('failed-jobs');
    }
}
