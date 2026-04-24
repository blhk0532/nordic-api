<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Widgets\KommunerMapWidget;
use App\Filament\Widgets\KommunerMapWidgetDb;
use App\Filament\Widgets\LocationMapPickerWidget;
use App\Filament\Widgets\SwedenKommunerWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SwedenKommuner extends Page
{
    protected static string $resource = SwedenKommunerResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Sverige GEO';

    protected static ?string $navigationLabel = 'Kommuner';

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public function getTitle(): string|Htmlable
    {
        return ' ';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KommunerMapWidget::make(),

        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getFooterWidgets(): array
    {
        return [

            //    LocationMapPickerWidget::class,   // Interactive picker
            SwedenKommunerWidget::class,     // Table with map
                     KommunerMapWidgetDb::make(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SwedenKommunerResource::getModel()::count();
    }
}
