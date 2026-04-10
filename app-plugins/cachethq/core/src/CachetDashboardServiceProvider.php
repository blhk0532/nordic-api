<?php

namespace Cachet;

use Cachet\Data\Cachet\ThemeData;
use Cachet\Filament\Pages\EditProfile;
use Cachet\Filament\Pages\StatusOverview;
use Cachet\Http\Middleware\SetAppLocale;
use Cachet\Livewire\EditStatusSettingsForm;
use Cachet\Settings\AppSettings;
use Cachet\Settings\CustomizationSettings;
use Cachet\Settings\ThemeSettings;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CachetDashboardServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $appSettings = app(AppSettings::class);

        return $panel
            ->id('cachet')
            ->when(
                ! $this->app->runningInConsole() && $appSettings->enable_external_dependencies,
                fn ($panel) => $panel->font('switzer', 'https://fonts.cdnfonts.com/css/switzer'),
                fn ($panel) => $panel->font('ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" ', provider: LocalFontProvider::class),
            )
            ->default()
            ->login()
            ->passwordReset()
            ->profile(EditProfile::class, isTenantProfile: false)
            ->brandLogo(fn () => view('cachet::filament.brand-logo'))
            ->brandLogoHeight('2rem')
            ->colors([
                'primary' => Color::generateV3Palette('rgb(4, 193, 71)'),
                'purple' => Color::Purple,
                'gray' => Color::Zinc,
            ])
            ->favicon('/vendor/cachethq/cachet/favicon.ico')
            ->viteTheme([
                'resources/css/dashboard/theme.css',
                'resources/css/cachet.css',
            ], 'vendor/cachethq/cachet/build')
            ->assets([
                Js::make('cachet-filament', asset('vendor/cachethq/cachet/cachet-filament.js'))->defer(),
            ], package: 'vendor/cachethq/cachet')
            ->discoverResources(__DIR__.'/Filament/Resources', 'Cachet\\Filament\\Resources')
            ->pages([
                StatusOverview::class,
                EditStatusSettingsForm::class,
            ])
            ->routes(function ($router) {
                $router->get('{tenant}/my-profile', TenantProfile::class)
                    ->name('tenant.profile')
                    ->middleware('web');
            })
            ->discoverWidgets(__DIR__.'/Filament/Widgets', 'Cachet\\Filament\\Widgets')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => __('cachet::navigation.settings.label'))
                    ->collapsed()
                    ->icon('cachet-settings'),
                NavigationGroup::make()
                    ->label(fn (): string => __('cachet::navigation.integrations.label'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn (): string => __('cachet::navigation.resources.label'))
                    ->collapsible(false),
            ])
        //    ->navigationItems([
        //        NavigationItem::make()
        //            ->label(fn (): string => __('cachet::navigation.resources.items.status_page'))
        //            ->url(fn (): string => StatusOverview::getUrl(panel: 'cachet'))
        //            ->icon('cachet-component-performance-issues'),
        //    ])
            ->renderHook(PanelsRenderHook::HEAD_END, function () use ($appSettings): string {
                $customizationSettings = app(CustomizationSettings::class);
                $theme = new ThemeData(app(ThemeSettings::class));

                return (string) view('cachet::filament.hooks.head-customizations', [
                    'refresh_rate' => $appSettings->refresh_rate,
                    'cachet_header' => $customizationSettings->header,
                    'cachet_css' => $customizationSettings->stylesheet,
                    'theme' => $theme,
                ]);
            })
            ->renderHook(PanelsRenderHook::SCRIPTS_AFTER, function (): string {
                $customizationSettings = app(CustomizationSettings::class);

                return (string) view('cachet::filament.hooks.body-after', [
                    'cachet_footer' => $customizationSettings->footer,
                ]);
            })
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('cachet::filament.widgets.add-incident-button'))
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
                SetAppLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->path(Cachet::dashboardPath())
            ->bootUsing(function (): void {
                Section::configureUsing(fn (Section $section) => $section->columnSpanFull());
            });
    }
}
