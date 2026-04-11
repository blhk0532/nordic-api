<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use App\Filament\Widgets\KommunerMapWidget2;
use App\Filament\Widgets\KommunerMapWidget2Db;
use App\Filament\Widgets\SwedenPostorterWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SwedenPostorter extends Page
{
    protected static string $resource = SwedenPostorterResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

    protected static ?string $navigationLabel = 'Postorter';

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string|Htmlable
    {
        return ' ';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KommunerMapWidget2::make(),
            KommunerMapWidget2Db::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [

            SwedenPostorterWidget::class,

        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SwedenPostorterResource::getModel()::count();
    }
}
