<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners;

use App\Filament\Resources\SwedenPersoners\Pages\CreateSwedenPersoner;
use App\Filament\Resources\SwedenPersoners\Pages\EditSwedenPersoner;
use App\Filament\Resources\SwedenPersoners\Pages\ListSwedenPersoners;
use App\Filament\Resources\SwedenPersoners\Pages\ViewSwedenPersoner;
use App\Filament\Resources\SwedenPersoners\Schemas\SwedenPersonerForm;
use App\Filament\Resources\SwedenPersoners\Schemas\SwedenPersonerInfolist;
use App\Filament\Resources\SwedenPersoners\Tables\SwedenPersonersTable;
use App\Models\SwedenPersoner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SwedenPersonerResource extends Resource
{
    protected static ?string $model = SwedenPersoner::class;

    protected static ?int $navigationSort = -100;

    protected static ?string $navigationLabel = 'Personer';

    protected static string|UnitEnum|null $navigationGroup = 'Sverige GEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = false;

    public static function getModelLabel(): string
    {
        return __('# Personer @GEO');
    }

    public static function getPluralModelLabel(): string
    {
        return __('# Personer @GEO');
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return SwedenPersonerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SwedenPersonerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SwedenPersonersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSwedenPersoners::route('/'),
            'create' => CreateSwedenPersoner::route('/create'),
            'view' => ViewSwedenPersoner::route('/{record}'),
            'edit' => EditSwedenPersoner::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
