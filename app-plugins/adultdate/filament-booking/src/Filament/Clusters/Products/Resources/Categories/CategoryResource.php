<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories;

use Adultdate\FilamentBooking\Filament\Clusters\Products\ProductsCluster;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Pages\CreateCategory;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Pages\EditCategory;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Pages\ListCategories;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\RelationManagers\ProductsRelationManager;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Schemas\CategoryForm;
use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Tables\CategoriesTable;
use Adultdate\FilamentBooking\Models\Booking\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
