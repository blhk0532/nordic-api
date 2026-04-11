<?php

declare(strict_types=1);

namespace BinaryBuilds\CommandRunner\Resources\CommandRuns;

use BackedEnum;
use BinaryBuilds\CommandRunner\Models\CommandRun;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Pages\ListCommandRuns;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Pages\RunCommand;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Pages\ViewCommandRun;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Schemas\CommandRunForm;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Schemas\CommandRunInfolist;
use BinaryBuilds\CommandRunner\Resources\CommandRuns\Tables\CommandRunsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommandRunResource extends Resource
{
    public static string|null|UnitEnum $navigationGroup = 'Settings';

    protected static ?string $slug = 'command-runner';

    protected static ?string $model = CommandRun::class;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedCommandLine;

    public static function getNavigationLabel(): string
    {
        return __('Command Runner');
    }

    public static function getBreadcrumb(): string
    {
        return __('Command Runner');
    }

    public static function infolist(Schema $schema): Schema
    {
        return CommandRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommandRunsTable::configure($table)
            ->defaultSort('id', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return CommandRunForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'create' => RunCommand::route('/create'),
            'index' => ListCommandRuns::route('/'),
            'view' => ViewCommandRun::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }
}
