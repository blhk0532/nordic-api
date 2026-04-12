<?php

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use Adultdate\FilamentBooking\FilamentBookingPlugin;
use App\Filament\Pages\AuthLogin;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Http\Middleware\ApplyTenantScopes;
use App\Http\Middleware\CurrentTenant;
use App\Models\Team;
use App\Models\User;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Awcodes\Overlook\OverlookPlugin;
use BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BinaryBuilds\CommandRunner\CommandRunnerPlugin;
use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Devletes\FilamentPinnableNavigation\PinnableNavigationPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
// use Flexpik\FilamentStudio\FilamentStudioPlugin;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Flexpik\FilamentStudio\Resources\DynamicCollectionResource;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;
use Hammadzafar05\MobileBottomNav\MobileBottomNavItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use MmesDesign\FilamentFileManager\FileManagerPlugin;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
use MWGuerra\WebTerminal\WebTerminalPlugin;
use Wezlo\FilamentWorkspaceTabs\WorkspaceTabsPlugin;
use Wallacemartinss\FilamentIconPicker\FilamentIconPickerPlugin;
use Usamamuneerchaudhary\Notifier\FilamentNotifierPlugin;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->homeUrl('dashboard')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->tenant(Team::class, slugAttribute: 'slug', ownershipRelationship: null)
            ->tenantProfile(EditTeamProfile::class)
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
            ->breadcrumbs(false)
            ->revealablePasswords(true)
            ->unsavedChangesAlerts()
            ->passwordReset()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->emailChangeVerification()
            ->spaUrlExceptions(['tel:*', 'mailto:*'])
            ->tenantMiddleware([
                ApplyTenantScopes::class,
                CurrentTenant::class,
            ], isPersistent: true)
            ->tenantMenuItems([
                'register' => fn (Action $action) => $action->label('Register team')
                    ->icon('heroicon-m-user-plus')
                    ->visible(fn () => ! filament()->getTenant()),
                'profile' => fn (Action $action) => $action->label('Team Settings')
                    ->sort(-1),
            ])
            ->navigationGroups([
                NavigationGroup::make('Pinned STAR')
                    ->collapsed(false)
                    ->collapsible(false)
                    ->icon('heroicon-o-star'),
                NavigationGroup::make('Dashboard')
                    ->collapsed(true)
                    ->icon('heroicon-c-squares-plus'),
                NavigationGroup::make('Dialers TELE')
                    ->collapsed(true)
                    ->icon('heroicon-o-phone-arrow-up-right'),
                NavigationGroup::make('Sverige GEO')
                    ->collapsed(true)
                    ->icon('heroicon-o-map-pin'),
                NavigationGroup::make('Kartor MAPS')
                    ->collapsed(true)
                    ->icon('heroicon-o-map'),
                NavigationGroup::make('Database PS')
                    ->collapsed(true)
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make('Queue JOBS')
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
                NavigationGroup::make('Users TEAM')
                    ->collapsed(true)
                    ->icon('heroicon-o-user'),
                NavigationGroup::make('Settings SYS')
                    ->collapsed(true)
                    ->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make('Database NR')
                    ->collapsed(true)
                    ->icon('heroicon-o-chart-pie'),
                NavigationGroup::make('System LOGS')
                    ->collapsed(true)
                    ->icon('heroicon-o-megaphone'),
                            NavigationGroup::make('Notifications')
                    ->collapsed(true)
                    ->icon('heroicon-o-bell'),

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
            ->plugin(
                AuthDesignerPlugin::make()
                    ->defaults(
                        fn ($config) => $config
                            ->media(asset('assets/pattaya.webp'))
                            ->mediaPosition(MediaPosition::Cover)
                            ->blur(1)
                    )
                    ->login(
                        fn ($config) => $config
                            ->media(asset('video/beach-at-sunset.1920x1080.mp4'))
                            ->usingPage(AuthLogin::class)
                    )
                    ->passwordReset()
                    ->emailVerification()
                    ->themeToggle()
            )
            ->plugins([
                MobileBottomNav::make()
                    ->items([
                        MobileBottomNavItem::make('Home')
                            ->icon('heroicon-o-home')
                            ->activeIcon('heroicon-s-home')
                            ->url('/admin')
                            ->isActive(true),
                        MobileBottomNavItem::make('Inbox')
                            ->icon('heroicon-o-inbox')
                            ->url('/chats')
                            ->badge(5, 'danger'),
                        MobileBottomNavItem::make('Profile')
                            ->icon('heroicon-o-user')
                            ->url(fn () => EditProfilePage::getUrl()),
                    ]),

            ])
            ->plugin(CommandRunnerPlugin::make())
            ->plugins([
                FileManagerPlugin::make()
                    ->defaultDisk('public')
                    ->navigationGroup('System LOGS')
                    ->navigationIcon('heroicon-o-folder')
                    ->navigationSort(5),
            ])
            ->plugins([
                WorkspaceTabsPlugin::make(),
                FilamentIconPickerPlugin::make(),
                 FilamentNotifierPlugin::make()
            ])
            ->plugins([
                FilamentBookingPlugin::make(),
            ])
            ->plugin(
                FilamentSocialitePlugin::make()
                    // (required) Add providers corresponding with providers in `config/services.php`.
                    ->providers([

                    ])
                    // (optional) Override the panel slug to be used in the oauth routes. Defaults to the panel's configured path.
                    ->slug('admin')
                    // (optional) Enable/disable registration of new (socialite-) users.
                    ->registration(true)
                    // (optional) Enable/disable registration of new (socialite-) users using a callback.
                    // In this example, a login flow can only continue if there exists a user (Authenticatable) already.
                    ->registration(fn (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) => (bool) $user)
                    // (optional) Change the associated model class.
                    ->userModelClass(User::class)
                    // (optional) Change the associated socialite class (see below).
                    ->socialiteUserModelClass(User::class)
            )
            ->plugins([
                FilamentShieldPlugin::make()
                    ->scopeToTenant(false),
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
                    ->alphabetical(false)
                    ->sort(2)
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 2,
                        'lg' => 4,
                        'xl' => 5,
                        '2xl' => 6,
                    ])
                    ->excludes([
                        DynamicCollectionResource::class,
                    ]),
            ])
            ->plugins([
                //    FilamentStudioPlugin::make()
            ])
            ->plugin(
                FilamentExceptionsPlugin::make()
                    ->scopeToTenant(false)
                    ->navigationGroup('System LOGS')
            )
            ->plugins([
                WebTerminalPlugin::make()
                    ->terminalNavigation(
                        icon: 'heroicon-o-command-line',
                        label: 'Terminal',
                        sort: 100,
                        group: 'System LOGS',
                    ),
            ])
            ->plugins([
                FilamentGeneralSettingsPlugin::make()
                    ->canAccess(true)
                    ->setSort(3)
                    ->setIcon('heroicon-o-cog')
                    ->setNavigationGroup('Settings SYS')
                    ->setTitle('Settings')
                    ->setNavigationLabel('Settings'),
            ])
            ->plugins([
                PinnableNavigationPlugin::make(),
                FilamentApexChartsPlugin::make(),
                FilamentFailedJobsPlugin::make(),
                SlickScrollbarPlugin::make(),
                ResizedColumnPlugin::make(),
                FilamentLogViewer::make(),
            ]);
    }
}
