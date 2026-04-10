<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostnummers\Widgets\MapPickerWidget;
use App\Filament\Widgets\SwedenKommunerWidget;
use Illuminate\Contracts\Support\Htmlable;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Resource;
use UnitEnum;
use App\Actions\ImportSwedenKommunerCountsFromRatsit;
use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Widgets\KommunerMapWidget;
use App\Filament\Widgets\KommunerMapWidgetDb;
use App\Filament\Widgets\LocationMapPickerWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class SwedenKommuner extends Page
{
     protected static string $resource = SwedenKommunerResource::class;

           protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 1;

     protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

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
             KommunerMapWidgetDb::make(),
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
    protected function getFooterWidgets(): array
    {
        return [


        //    LocationMapPickerWidget::class,   // Interactive picker
            SwedenKommunerWidget::class,     // Table with map
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SwedenKommunerResource::getModel()::count();
    }

    }
