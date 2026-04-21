<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\HittaDatas\HittaDataResource;
use App\Filament\Resources\Merinfos\MerinfoResource;
use App\Filament\Resources\People\PersonResource;
use App\Filament\Resources\RatsitDatas\RatsitDataResource;
use App\Filament\Resources\SwedenAdressers\SwedenAdresserResource;
use App\Filament\Resources\SwedenGators\SwedenGatorResource;
use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use App\Filament\Widgets\GeoMapWidget;
use Awcodes\Overlook\Widgets\OverlookWidget;
use BackedEnum;
use Harvirsidhu\FilamentCards\CardGroup;
use Harvirsidhu\FilamentCards\CardItem;
use Harvirsidhu\FilamentCards\Filament\Pages\CardsPage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ControlPanel extends CardsPage
{
    // protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-o-squares-2x2';

    //   protected static string|UnitEnum|null $navigationGroup = 'Dashboards';

    protected static ?string $title = 'Dashboard';

    protected static ?string $slug = 'controlpanel';

    protected static ?string $navigationLabel = 'ⵌ Dashboard';

    protected static ?int $navigationSort = -1;

    public static function getModelLabel(): string
    {
        return __('#');
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public static function getPluralModelLabel(): string
    {
        return __('Dashboards');
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadge(): ?string
    {
        return Auth::user()->name;
    }

    protected static function getCards(): array
    {
        return [
            CardGroup::make()
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    CardItem::make(SwedenKommunerResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Kommuner')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenKommunerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostorterResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Postorter')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPostorterResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostnummerResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Postnummer')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPostnummerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenGatorResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Gator')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenGatorResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenAdresserResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Adresser')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenAdresserResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPersonerResource::class)
                        ->description('@GEO Sverige')
                        ->icon('heroicon-o-map-pin')
                        ->label('Personer')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPersonerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                ]),
            CardGroup::make()
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    CardItem::make(HittaDataResource::class)
                        ->description('@DB Hitta Data')
                        ->icon('heroicon-o-user-plus')
                        ->label('Hitta.se')
                        ->color('success')
                        ->badge(fn () => (string) HittaDataResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/4'),
                    CardItem::make(RatsitDataResource::class)
                        ->description('@DB Ratsit Data')
                        ->icon('heroicon-o-user-plus')
                        ->label('Ratsit.se')
                        ->color('success')
                        ->badge(fn () => (string) RatsitDataResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/4'),
                    CardItem::make(MerinfoResource::class)
                        ->description('@DB Merinfo Data')
                        ->icon('heroicon-o-user-plus')
                        ->label('Merinfo.se')
                        ->color('success')
                        ->badge(fn () => (string) MerinfoResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/4'),
                    CardItem::make(PersonResource::class)
                        ->description('@DB Person Data')
                        ->icon('heroicon-o-user-plus')
                        ->label('Personer')
                        ->color('success')
                        ->badge(fn () => (string) PersonResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/4'),
                ])
                ->columns(4),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getFooterWidgets(): array
    {
        return [
            // OverlookWidget::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //  GeoMapWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
