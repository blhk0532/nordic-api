<?php

namespace App\Filament\Resources\Merinfos;

use App\Filament\Resources\Merinfos\Schemas\MerinfoForm;
use App\Filament\Resources\Merinfos\Schemas\MerinfoInfolist;
use App\Filament\Resources\Merinfos\Tables\MerinfosTable;
use App\Models\Merinfo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MerinfoResource extends Resource
{
    protected static ?string $model = Merinfo::class;

    protected static bool $isScopedToTenant = false;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user';

    protected static \UnitEnum|string|null $navigationGroup = 'Database NR';

    protected static ?string $navigationLabel = 'Merinfo NEW';

    public static function getModelLabel(): string
    {
        return __('DB Merinfos');
    }

    public static function getPluralModelLabel(): string
    {
        return __('DB Merinfos');
    }

    public static function form(Schema $schema): Schema
    {
        return MerinfoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerinfoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerinfosTable::configure($table);
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
            'index' => Pages\ListMerinfos::route('/'),
            'create' => Pages\CreateMerinfo::route('/create'),
            'edit' => Pages\EditMerinfo::route('/{record}/edit'),
            'view' => Pages\ViewMerinfo::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}
