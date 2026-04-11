<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Pages\ListTerminalLogs;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Pages\ViewTerminalLog;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Schemas\TerminalLogInfolist;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Tables\TerminalLogsTable;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Widgets\TerminalLogsStatsOverview;
use MWGuerra\WebTerminal\Models\TerminalLog;
use MWGuerra\WebTerminal\WebTerminalPlugin;

class TerminalLogResource extends Resource
{
    protected static ?string $model = TerminalLog::class;

    protected static ?string $slug = 'terminal-logs';

    public static function getModelLabel(): string
    {
        return __('web-terminal::terminal.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('web-terminal::terminal.resource.plural_label');
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return WebTerminalPlugin::current()?->getTerminalLogsNavigationIcon()
            ?? 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return WebTerminalPlugin::current()?->getTerminalLogsNavigationLabel()
            ?? __('web-terminal::terminal.navigation.terminal_logs');
    }

    public static function getNavigationGroup(): ?string
    {
        return WebTerminalPlugin::current()?->getTerminalLogsNavigationGroup()
            ?? __('web-terminal::terminal.navigation.tools');
    }

    public static function getNavigationSort(): ?int
    {
        return WebTerminalPlugin::current()?->getTerminalLogsNavigationSort()
            ?? 101;
    }

    public static function infolist(Schema $schema): Schema
    {
        return TerminalLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TerminalLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TerminalLogsStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTerminalLogs::route('/'),
            'view' => ViewTerminalLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
