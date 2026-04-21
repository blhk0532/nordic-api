<?php

namespace App\Filament\Resources\Spreadsheets;

use App\Filament\Resources\Spreadsheets\Pages\CreateSpreadsheet;
use App\Filament\Resources\Spreadsheets\Pages\EditSpreadsheet;
use App\Filament\Resources\Spreadsheets\Pages\ListSpreadsheets;
use App\Filament\Resources\Spreadsheets\Pages\ViewSpreadsheet;
use App\Filament\Resources\Spreadsheets\Schemas\SpreadsheetForm;
use App\Filament\Resources\Spreadsheets\Schemas\SpreadsheetInfolist;
use App\Filament\Resources\Spreadsheets\Tables\SpreadsheetsTable;
use App\Models\Spreadsheet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpreadsheetResource extends Resource
{
    protected static ?string $model = Spreadsheet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Database DB';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SpreadsheetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpreadsheetsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpreadsheetInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpreadsheets::route('/'),
            'create' => CreateSpreadsheet::route('/create'),
            'view' => ViewSpreadsheet::route('/{record}'),
            'edit' => EditSpreadsheet::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
