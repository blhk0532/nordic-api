<?php

namespace App\Providers\Filament;

use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Lartisan\Architect\ArchitectPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->spa()
            ->maxContentWidth(Width::Full)
            ->spaUrlExceptions(['tel:*', 'mailto:*'])
            ->sidebarCollapsibleOnDesktop(true)
            ->favicon(fn () => asset('favicon.svg'))
            ->brandLogo(fn () => view('filament.app.logo'))
            ->brandLogoHeight('32px')
            ->sidebarWidth('21rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('Noridic Digital')
            ->defaultThemeMode(ThemeMode::Dark)
            ->revealablePasswords(true)
            ->unsavedChangesAlerts()
            ->passwordReset()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->emailChangeVerification()
            ->spaUrlExceptions(['tel:*', 'mailto:*'])
            ->navigationGroups([
                NavigationGroup::make('Dialers TELE')
                    ->collapsed(true)
                    ->icon('heroicon-o-phone-arrow-up-right'),
                NavigationGroup::make('Sverige MAP')
                    ->collapsed(true)
                    ->icon('heroicon-o-map'),
                NavigationGroup::make('Queue JOBS')
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
                NavigationGroup::make('Sweden GEO')
                    ->collapsed(true)
                    ->icon('heroicon-o-map-pin'),
                NavigationGroup::make('Database SE')
                    ->collapsed(true)
                    ->icon('heroicon-o-chart-pie'),
                NavigationGroup::make('Database PS')
                    ->collapsed(true)
                    ->icon('heroicon-o-shield-check'),

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                //    Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(PinnableNavigationPlugin::make())
            ->plugins([
                //    ArchitectPlugin::make(),
            ]);
    }
}
