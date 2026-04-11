<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat;

use AdultDate\FilamentWirechat\Commands\InstallWirechatCommand;
use AdultDate\FilamentWirechat\Filament\Widgets\WirechatWidget;
use Adultdate\Wirechat\Facades\Wirechat;
use Adultdate\Wirechat\Livewire\Chat\Chat;
use Adultdate\Wirechat\Livewire\Chat\Drawer;
use Adultdate\Wirechat\Livewire\Chat\Group\AddMembers;
use Adultdate\Wirechat\Livewire\Chat\Group\Members;
use Adultdate\Wirechat\Livewire\Chat\Group\Permissions;
use Adultdate\Wirechat\Livewire\Chat\Info;
use Adultdate\Wirechat\Livewire\Chats\Chats;
use Adultdate\Wirechat\Livewire\Modals\Modal;
use Adultdate\Wirechat\Livewire\New\Group;
use Adultdate\Wirechat\Middleware\BelongsToConversation;
use Adultdate\Wirechat\Middleware\EnsureWirechatPanelAccess;
use Adultdate\Wirechat\Middleware\SetCurrentPanel;
use Adultdate\Wirechat\PanelRegistry;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWirechatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-wirechat';

    public static string $viewNamespace = 'filament-wirechat';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('adultdate/filament-wirechat');
            })
            ->hasConfigFile()
            ->hasViews(self::$viewNamespace)
            ->hasTranslations();

        // Publish migrations directly (since they're .php files, not .stub)
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-wirechat-migrations');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/filament-wirechat'),
        ], 'filament-wirechat-assets');
    }

    public function packageRegistered(): void
    {
        // Register wirechat service (check if already bound by standalone wirechat plugin)
        if (! $this->app->bound('wirechat')) {
            $this->app->singleton('wirechat', function ($app) {
                return new Services\WirechatService;
            });
        }

        // Also bind 'adultdate' alias for compatibility with standalone wirechat
        if (! $this->app->bound('adultdate')) {
            $this->app->singleton('adultdate', function ($app) {
                return $app->make('wirechat');
            });
        }

        // Register ColorService
        if (! $this->app->bound(Services\ColorService::class)) {
            $this->app->singleton(Services\ColorService::class, fn () => new Services\ColorService);
            // Also create an alias for backward compatibility with Adultdate\Wirechat namespace
            //    $this->app->alias(\AdultDate\FilamentWirechat\Services\ColorService::class, \AdultDate\FilamentWirechat\Services\ColorService::class);
        }

        // Register PanelRegistry for standalone wirechat panels
        if (class_exists(PanelRegistry::class)) {
            if (! $this->app->bound(PanelRegistry::class)) {
                $this->app->singleton(PanelRegistry::class, function ($app) {
                    return new PanelRegistry;
                });
            }

            // Alias for backward compatibility
            //    $this->app->alias(\Adultdate\Wirechat\PanelRegistry::class, \Adultdate\Wirechat\PanelRegistry::class);
        }
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Register Livewire Components (both filament-wirechat and standalone wirechat)
        $this->registerLivewireComponents();

        // Register standalone wirechat Livewire components (if classes exist)
        $this->registerStandaloneWirechatComponents();

        // Load broadcasting channels
        $this->loadBroadcastingChannels();

        // Load standalone wirechat routes (if they exist)
        $this->loadStandaloneWirechatRoutes();

        // Register middleware for standalone wirechat
        $this->registerStandaloneWirechatMiddleware();

        // Register Blade directives
        $this->registerThemeStyles();
        $this->registerStandaloneWirechatBladeDirectives();

        // Load standalone wirechat views and translations
        $this->loadStandaloneWirechatViews();

        // Testing
        Testable::mixin(new Testing\TestsFilamentWirechat);
    }

    protected function loadBroadcastingChannels(): void
    {
        // Only load Filament channels if Filament is available
        if (! class_exists(Filament::class)) {
            return;
        }

        // Use a static flag to prevent double-loading
        static $loaded = false;
        if ($loaded) {
            return;
        }

        // Check if already defined to prevent double-loading
        if (defined('FILAMENT_WIRECHAT_CHANNELS_LOADED')) {
            $loaded = true;

            return;
        }

        $channelsPath = __DIR__.'/../routes/channels.php';
        if (file_exists($channelsPath)) {
            require $channelsPath;
            $loaded = true;
        }
    }

    protected function registerLivewireComponents(): void
    {
        \Livewire\Livewire::component('filament-wirechat.chats', Chats::class);
        \Livewire\Livewire::component('filament-wirechat.chat', Chat::class);
        \Livewire\Livewire::component('filament-wirechat.chat.drawer', Drawer::class);
        \Livewire\Livewire::component('filament-wirechat.chat.info', Info::class);
        \Livewire\Livewire::component('filament-wirechat.chat.group.info', \Adultdate\Wirechat\Livewire\Chat\Group\Info::class);
        \Livewire\Livewire::component('filament-wirechat.chat.group.members', Members::class);
        \Livewire\Livewire::component('filament-wirechat.chat.group.add-members', AddMembers::class);
        \Livewire\Livewire::component('filament-wirechat.chat.group.permissions', Permissions::class);
        \Livewire\Livewire::component('filament-wirechat.new.chat', \Adultdate\Wirechat\Livewire\New\Chat::class);
        \Livewire\Livewire::component('filament-wirechat.new.group', Group::class);
        \Livewire\Livewire::component('filament-wirechat.modal', Modal::class);
        \Livewire\Livewire::component('filament-wirechat.widget', \Adultdate\Wirechat\Livewire\Widgets\Wirechat::class);
        \Livewire\Livewire::component('filament-wirechat.chats-icon-button', Livewire\Components\ChatsIconButton::class);
        \Livewire\Livewire::component('chats-sidebar', Livewire\Components\ChatsSidebar::class);
        \Livewire\Livewire::component(WirechatWidget::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'adultdate/filament-wirechat';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        if (App::runningInConsole()) {
            return [];
        }

        return [
            // CSS will be included via the main app.css file
            // Load Laravel Echo for real-time broadcasting - must load on all pages for real-time to work
            // Reference the main application's app.js file using Vite
            Js::make('filament-wirechat-echo', Vite::asset('resources/js/app.tsx'))
                ->module(),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            InstallWirechatCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * Register Blade directive for injecting theme CSS variables.
     * Uses Filament's panel colors by default, with config overrides.
     */
    protected function registerThemeStyles(): void
    {
        Blade::directive('filamentWirechatStyles', function () {
            return "<?php echo app('AdultDate\FilamentWirechat\Services\ThemeService')->renderStyles(); ?>";
        });
    }

    /**
     * Register standalone wirechat Livewire components (wirechat.* prefix)
     */
    protected function registerStandaloneWirechatComponents(): void
    {
        // Only register if the standalone wirechat classes exist
        if (! class_exists(\Adultdate\Wirechat\Livewire\Pages\Chats::class)) {
            return;
        }

        \Livewire\Livewire::component('wirechat.pages.index', \Adultdate\Wirechat\Livewire\Pages\Chats::class);
        \Livewire\Livewire::component('wirechat.pages.view', \Adultdate\Wirechat\Livewire\Pages\Chat::class);
        \Livewire\Livewire::component('wirechat.chats', Chats::class);
        \Livewire\Livewire::component('wirechat.modal', Modal::class);
        \Livewire\Livewire::component('wirechat.new.chat', \Adultdate\Wirechat\Livewire\New\Chat::class);
        \Livewire\Livewire::component('wirechat.new.group', Group::class);
        \Livewire\Livewire::component('wirechat.chat', Chat::class);
        \Livewire\Livewire::component('wirechat.chat.info', Info::class);
        \Livewire\Livewire::component('wirechat.chat.group.info', \Adultdate\Wirechat\Livewire\Chat\Group\Info::class);
        \Livewire\Livewire::component('wirechat.chat.drawer', Drawer::class);
        \Livewire\Livewire::component('wirechat.chat.group.add-members', AddMembers::class);
        \Livewire\Livewire::component('wirechat.chat.group.members', Members::class);
        \Livewire\Livewire::component('wirechat.chat.group.permissions', Permissions::class);
        \Livewire\Livewire::component('adultdate', \Adultdate\Wirechat\Livewire\Widgets\Wirechat::class);
    }

    /**
     * Register middleware for standalone wirechat
     */
    protected function registerStandaloneWirechatMiddleware(): void
    {
        if (! class_exists(BelongsToConversation::class)) {
            return;
        }

        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('belongsToConversation', BelongsToConversation::class);
        $router->aliasMiddleware('wirechat.setPanel', SetCurrentPanel::class);
        $router->aliasMiddleware('wirechat.panelAccess', EnsureWirechatPanelAccess::class);
    }

    /**
     * Load standalone wirechat routes
     */
    protected function loadStandaloneWirechatRoutes(): void
    {
        // Only load standalone routes if PanelRegistry exists (standalone wirechat)
        if (! class_exists(PanelRegistry::class) || ! app()->bound(PanelRegistry::class)) {
            return;
        }

        // Load standalone wirechat web routes if they exist
        $standaloneWebRoutes = __DIR__.'/../routes/standalone-web.php';
        if (file_exists($standaloneWebRoutes)) {
            $this->loadRoutesFrom($standaloneWebRoutes);
        }

        // Load standalone wirechat channel routes only if not already loaded via Filament channels
        // Check if Filament is available - if so, Filament channels handle it
        if (! class_exists(Filament::class)) {
            $standaloneChannelRoutes = __DIR__.'/../routes/standalone-channels.php';
            if (file_exists($standaloneChannelRoutes)) {
                $this->loadRoutesFrom($standaloneChannelRoutes);
            }
        }
    }

    /**
     * Register standalone wirechat Blade directives
     */
    protected function registerStandaloneWirechatBladeDirectives(): void
    {
        // Only register if the standalone wirechat facade exists
        if (! class_exists(Wirechat::class)) {
            return;
        }

        // Register wirechatAssets directive - delegates to a view/helper if needed
        // Users can use @wirechatAssets in their views for standalone wirechat integration
        Blade::directive('wirechatAssets', function ($expression): string {
            $panel = $expression ?: 'null';

            return '<?php
                $wirechatFacade = \Adultdate\Wirechat\Facades\Wirechat::class;
                if (class_exists($wirechatFacade)) {
                    $panelArg = '.$panel.';
                    $currentPanel = $panelArg && $panelArg !== \'null\' && $panelArg !== null
                        ? \Adultdate\Wirechat\Facades\Wirechat::getPanel($panelArg)
                        : \Adultdate\Wirechat\Facades\Wirechat::currentPanel();

                    if ($currentPanel) {
                        try {
                            echo view(\'wirechat::components.toast\')->render();
                        } catch (\Exception $e) {
                            // Silently fail if views don\'t exist
                        }
                    }
                }
            ?>';
        });

        // Register wirechatStyles directive
        Blade::directive('wirechatStyles', function ($expression): string {
            $panel = $expression ?: 'null';

            return '<?php
                $wirechatFacade = \Adultdate\Wirechat\Facades\Wirechat::class;
                if (class_exists($wirechatFacade)) {
                    $panelArg = '.$panel.';
                    $currentPanel = $panelArg && $panelArg !== \'null\' && $panelArg !== null
                        ? \Adultdate\Wirechat\Facades\Wirechat::getPanel($panelArg)
                        : \Adultdate\Wirechat\Facades\Wirechat::currentPanel();

                    if ($currentPanel) {
                        $primaryColor = isset($currentPanel->getColors()[\'primary\'])
                            ? $currentPanel->getColors()[\'primary\'][500]
                            : \'oklch(0.623 0.214 259.815)\';

                        echo \'<style>:root { --wc-brand-primary: \' . $primaryColor . \'; --wc-light-primary: #fff; --wc-light-secondary: oklch(0.967 0.001 286.375); --wc-light-accent: oklch(0.985 0 0); --wc-light-border: oklch(0.92 0.004 286.32); --wc-dark-primary: oklch(0.21 0.006 285.885); --wc-dark-secondary: oklch(0.274 0.006 286.033); --wc-dark-accent: oklch(0.37 0.013 285.805); --wc-dark-border: oklch(0.37 0.013 285.805); } [x-cloak] { display: none !important; }</style>\';
                    }
                }
            ?>';
        });
    }

    /**
     * Load standalone wirechat views and translations
     */
    protected function loadStandaloneWirechatViews(): void
    {
        // Load standalone wirechat views from filament-wirechat
        $wirechatViewsPath = __DIR__.'/../resources/wirechat-views';
        if (is_dir($wirechatViewsPath)) {
            $this->loadViewsFrom($wirechatViewsPath, 'wirechat');
        }

        // Load standalone wirechat translations from filament-wirechat
        $wirechatLangPath = __DIR__.'/../lang-standalone';
        if (is_dir($wirechatLangPath)) {
            $this->loadTranslationsFrom($wirechatLangPath, 'wirechat');
        }
    }
}
