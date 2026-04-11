<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands;

use Adultdate\FilamentBooking\Filament\Clusters\Products\ProductsCluster;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Pages\CreateBrand;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Pages\EditBrand;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Pages\ListBrands;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\RelationManagers\AddressesRelationManager;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\RelationManagers\ProductsRelationManager;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Schemas\BrandForm;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Tables\BrandsTable;
use Adultdate\FilamentBooking\Models\Booking\Brand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return BrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
