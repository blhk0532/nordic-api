<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostnummers\Widgets\MapPickerWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SwedenPostnummer extends Page
{
    protected string $view = 'filament.pages.sweden-postnummer';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

    protected static ?string $model = SwedenPostnummer::class;

    protected static ?string $navigationLabel = 'Postnummer';

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MapPickerWidget::class,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return ' ';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SwedenPostnummerResource::getModel()::count();
    }
}
