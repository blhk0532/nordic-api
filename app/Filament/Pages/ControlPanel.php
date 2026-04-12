<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\Merinfos\MerinfoResource;
use App\Filament\Resources\People\PersonResource;
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
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ControlPanel extends CardsPage
{
    // protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-squares-2x2';

    //   protected static string|UnitEnum|null $navigationGroup = 'Dashboards';

    protected static ?string $title = '';

    protected static ?string $slug = 'controlpanel';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -1;

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
                        ->description('Sverige Kommuner DB')
                        ->icon('heroicon-o-map')
                        ->label('Kommuner')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenKommunerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostorterResource::class)
                        ->description('Sverige Postorter DB')
                        ->icon('heroicon-o-envelope')
                        ->label('Postorter')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPostorterResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostnummerResource::class)
                        ->description('Sverige Postnummer DB')
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
                        ->description('Sverige Gator DB')
                        ->icon('heroicon-o-home-modern')
                        ->label('Gator')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenGatorResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenAdresserResource::class)
                        ->description('Sverige Adresser DB')
                        ->icon('heroicon-o-home')
                        ->label('Adresser')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenAdresserResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPersonerResource::class)
                        ->description('Sverige Personer DB')
                        ->icon('heroicon-o-users')
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
                    CardItem::make(MerinfoResource::class)
                        ->description('Sverige Merinfo DB')
                        ->icon('heroicon-o-users')
                        ->label('Merinfo')
                        ->color('success')
                        ->badge(fn () => (string) MerinfoResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/2'),
                    CardItem::make(PersonResource::class)
                        ->description('Sverige Personer DB')
                        ->icon('heroicon-o-users')
                        ->label('Personer')
                        ->color('success')
                        ->badge(fn () => (string) PersonResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('primary')
                        ->columnSpan('1/2'),
                ])
                ->columns(2),
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
            GeoMapWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
