<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\SwedenAdressers\SwedenAdresserResource;
use App\Filament\Resources\SwedenGators\SwedenGatorResource;
use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use BackedEnum;
use Harvirsidhu\FilamentCards\CardGroup;
use Harvirsidhu\FilamentCards\CardItem;
use Harvirsidhu\FilamentCards\Filament\Pages\CardsPage;
use Illuminate\Support\Facades\Auth;

class ControlPanel extends CardsPage
{
    // protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-squares-2x2';

    protected static ?string $title = '';

    protected static ?string $slug = 'dashboard';

    protected static ?string $navigationLabel = 'DASHBOARD';

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
            CardGroup::make('Sweden GEO')
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    CardItem::make(SwedenKommunerResource::class)
                        ->description('Sweden Kommuner Database')
                        ->icon('heroicon-o-map')
                        ->label('Sverige Kommuner')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenKommunerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostorterResource::class)
                        ->description('Sweden Postorter Database')
                        ->icon('heroicon-o-envelope')
                        ->label('Sverige Postorter')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPostorterResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPostnummerResource::class)
                        ->description('Sweden Postnummer Database')
                        ->icon('heroicon-o-map-pin')
                        ->label('Sverige Postnummer')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPostnummerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenGatorResource::class)
                        ->description('Sweden Gator Database')
                        ->icon('heroicon-o-home-modern')
                        ->label('Sverige Gator')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenGatorResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenAdresserResource::class)
                        ->description('Sweden Adresser Database')
                        ->icon('heroicon-o-home')
                        ->label('Sverige Adresser')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenAdresserResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                    CardItem::make(SwedenPersonerResource::class)
                        ->description('Sweden Personer Database')
                        ->icon('heroicon-o-users')
                        ->label('Sverige Personer')
                        ->color('primary')
                        ->badge(fn () => (string) SwedenPersonerResource::getModel()::count())
                        ->extraAttributes([
                            'style' => 'background:#18181b;padding-top:2rem;padding-bottom:2rem;',
                        ])
                        ->badgeColor('success')
                        ->columnSpan('1/3'),
                ]),

        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getFooterWidgets(): array
    {
        return [

        ];
    }
}
