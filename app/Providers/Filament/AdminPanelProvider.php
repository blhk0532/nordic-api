<?php

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Awcodes\Overlook\OverlookPlugin;
use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
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
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
use MWGuerra\WebTerminal\WebTerminalPlugin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Filament\Pages\ControlPanel;
use BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

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
            ->homeUrl('dashboard')
            ->colors([
                'primary' => Color::Orange,
            ])
            ->spa()
            ->maxContentWidth(Width::Full)
            ->spaUrlExceptions(['tel:*', 'mailto:*'])
            ->sidebarCollapsibleOnDesktop(true)
            ->favicon(fn () => asset('favicon.svg'))
            ->brandLogo(fn () => view('filament.app.logo'))
            ->brandLogoHeight('48px')
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
                NavigationGroup::make('Pinned STAR')
                    ->collapsed(true)
                    ->icon('heroicon-o-star'),
                NavigationGroup::make('Dashboards')
                    ->collapsed(true)
                    ->icon('heroicon-c-squares-plus'),
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
                NavigationGroup::make('Users TEAM')
                    ->collapsed(true)
                    ->icon('heroicon-o-user'),
                NavigationGroup::make('System DEV')
                    ->collapsed(true)
                    ->icon('heroicon-o-code-bracket-square'),
            ])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn () => Str::ucfirst(Auth::user()->name))
                    ->url(fn () => EditProfilePage::getUrl())
                    ->icon('heroicon-o-user-circle'),
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
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle(__(' '))
                    ->setNavigationLabel(__(' '))
                    ->setNavigationGroup(__(' '))
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEmailForm()
                    ->shouldShowLocaleForm(options: [
                        'en' => __('🇺🇸 English'),
                        'sv' => __('🇸🇪 Svenska'),
                        'th' => __('🇹🇭 ภาษาไทย'),
                    ])
                    ->shouldShowThemeColorForm()
                    ->shouldShowSanctumTokens()
                    ->shouldShowMultiFactorAuthentication()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(true, 'attachments'),
            ])
            ->plugins([
                OverlookPlugin::make()
                ->alphabetical(true)
                    ->sort(2)
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 2,
                        'lg' => 4,
                        'xl' => 5,
                        '2xl' => 6,
                    ]),
            ])
            ->plugin(PinnableNavigationPlugin::make())
            ->plugins([
                WebTerminalPlugin::make()
                    ->terminalNavigation(
                        icon: 'heroicon-o-command-line',
                        label: 'Terminal',
                        sort: 100,
                        group: 'System DEV',
                    ),
            ])
            ->plugins([
                FilamentExceptionsPlugin::make()
                ->navigationGroup('System DEV'),
                FilamentApexChartsPlugin::make(),
                FilamentFailedJobsPlugin::make(),
                SlickScrollbarPlugin::make(),
                ResizedColumnPlugin::make(),
                FilamentLogViewer::make(),
            ]);
    }
}
