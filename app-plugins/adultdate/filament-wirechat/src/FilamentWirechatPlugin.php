<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\DashboardBooking;
use AdultDate\FilamentWirechat\Filament\Pages\ChatDashboard;
use AdultDate\FilamentWirechat\Filament\Pages\ChatPage;
use AdultDate\FilamentWirechat\Filament\Pages\ChatsPage;
use AdultDate\FilamentWirechat\Filament\Pages\FullWidthChatPage;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\ConversationResource;
use AdultDate\FilamentWirechat\Filament\Resources\Messages\MessageResource;
use App\Filament\Booking\Clusters\Services\Resources\Bookings\Pages\DashboardBokning;
use App\Filament\Booking\Clusters\Services\Resources\Bookings\Pages\DashboardBooking as AppDashboardBooking;
use App\Filament\Booking\Pages\GoogleCalendar;
use App\Filament\Booking\Pages\InertiaCalendar;
use App\Models\User;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class FilamentWirechatPlugin implements Plugin
{
    private bool $showChatsIcon = true;

    private bool $showSidebar = true;

    private bool $showStyles = true;

    private bool $suppressLivewireErrors = true;

    /**
     * Pages to exclude from registration.
     *
     * @var array<string>
     */
    private array $excludedPages = [];

    /**
     * Pages to include (if set, only these pages will be registered).
     *
     * @var array<string>|null
     */
    private ?array $onlyPages = null;

    /**
     * Resources to exclude from registration.
     *
     * @var array<string>
     */
    private array $excludedResources = [];

    /**
     * Resources to include (if set, only these resources will be registered).
     *
     * @var array<string>|null
     */
    private ?array $onlyResources = null;

    public static function makeIfAuthenticated(): ?static
    {
        $user = Auth::user();

        return $user ? new self : null;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-wirechat';
    }

    public function register(Panel $panel): void
    {
        // Register resources from the plugin (with filtering if configured)
        if ($this->onlyResources !== null || ! empty($this->excludedResources)) {
            // Manually register resources with filtering
            $allResources = [
                ConversationResource::class,
                MessageResource::class,
            ];

            $resourcesToRegister = $this->filterResources($allResources);

            if (! empty($resourcesToRegister)) {
                $panel->resources($resourcesToRegister);
            }
        } else {
            // Use auto-discovery if no filtering is configured
            $panel->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'AdultDate\\FilamentWirechat\\Filament\\Resources'
            );
        }

        // Only register pages that are NOT in WirechatPanel directory
        // WirechatPanel pages should only be registered in the wirechat panel
        if ($panel->getId() !== 'wirechat') {
            // Get all available pages
            $allPages = [
                ChatDashboard::class,
                ChatsPage::class,
                ChatPage::class,
                FullWidthChatPage::class,
            ];

            // Filter pages based on configuration
            $pagesToRegister = $this->filterPages($allPages);

            if (! empty($pagesToRegister)) {
                $panel->pages($pagesToRegister);
            }
        }

        $panel->discoverWidgets(
            in: __DIR__.'/Filament/Widgets',
            for: 'AdultDate\\FilamentWirechat\\Filament\\Widgets'
        );

        // Register render hooks for chat integration
        $this->registerRenderHooks($panel);
    }

    public function boot(Panel $panel): void
    {
        // Open sidebar on all pages by default
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => <<<'HTML'
<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sidebar')) {
            Alpine.store('sidebar').open();
        }
    });
</script>
HTML
        );

        // Close sidebar on specific pages
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => <<<'HTML'
<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sidebar')) {
            Alpine.store('sidebar').close();
        }
    });
</script>
HTML,
            scopes: [
                DashboardBooking::class,
                GoogleCalendar::class,
                DashboardBokning::class,
                InertiaCalendar::class,
                AppDashboardBooking::class,
                ChatDashboard::class,
                ChatsPage::class,
                ChatPage::class,
                FullWidthChatPage::class,
            ],
        );

        // Close sidebar on specific pages
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => <<<'HTML'
<style>

</style>
HTML,
            scopes: [
                ChatDashboard::class,
                ChatsPage::class,
                ChatPage::class,
                FullWidthChatPage::class,
            ],
        );
    }

    public function showChatsIcon(bool $condition = true): static
    {
        $this->showChatsIcon = $condition;

        return $this;
    }

    public function showSidebar(bool $condition = true): static
    {
        $this->showSidebar = $condition;

        return $this;
    }

    public function showStyles(bool $condition = true): static
    {
        $this->showStyles = $condition;

        return $this;
    }

    public function suppressLivewireErrors(bool $condition = true): static
    {
        $this->suppressLivewireErrors = $condition;

        return $this;
    }

    /**
     * Exclude specific pages from being registered.
     *
     * @param  array<string>  $pages  Array of page class names to exclude
     */
    public function excludePages(array $pages): static
    {
        $this->excludedPages = array_merge($this->excludedPages, $pages);

        return $this;
    }

    /**
     * Only register specific pages (excludes all others).
     *
     * @param  array<string>  $pages  Array of page class names to include
     */
    public function onlyPages(array $pages): static
    {
        $this->onlyPages = $pages;

        return $this;
    }

    /**
     * Exclude specific resources from being registered.
     *
     * @param  array<string>  $resources  Array of resource class names to exclude
     */
    public function excludeResources(array $resources): static
    {
        $this->excludedResources = array_merge($this->excludedResources, $resources);

        return $this;
    }

    /**
     * Only register specific resources (excludes all others).
     *
     * @param  array<string>  $resources  Array of resource class names to include
     */
    public function onlyResources(array $resources): static
    {
        $this->onlyResources = $resources;

        return $this;
    }

    private function registerRenderHooks(Panel $panel): void
    {

        // Register chats icon button in user menu
        if ($this->showChatsIcon) {
            $panel->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('@livewire(\'filament-wirechat.chats-icon-button\', [], key(\'chats-icon-button\'))')
            );
        }

        // Register sidebar and modal components in body
        if ($this->showSidebar) {
            $panel->renderHook(
                PanelsRenderHook::PAGE_END,
                fn (): string => Blade::render('
                    <div id="chats-sidebar-container" x-data="{ open: false }">
                        <div id="chats-sidebar" x-show="open" x-on:open-modal.window="if ($event.detail.id === \'chats-sidebar\') { open = true }" x-on:close-modal.window="if ($event.detail.id === \'chats-sidebar\') { open = false }" style="display: none;" class="fixed inset-0 z-50 overflow-hidden">
                            <div style="display:flex; justify-content:flex-end;" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="w-full h-full flex inset-0 bg-black/50 transition-opacity" x-on:click="open = false"></div>
                            <div style="width: 100%;max-width: 500px;" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-full" class="fixed inset-y-0 right-0 z-50 w-full sm:w-[400px] md:w-[350px] lg:w-[500px] bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
                                <div class="h-full chats-sidebar-modal-widget" style="min-height: 100%;max-width: 500px; overflow: hidden;">
                                    @livewire(\'chats-sidebar\', [])
                                </div>
                            </div>
                        </div>
                        @livewire(\'filament-wirechat.modal\', [])
                    </div>
                ')
            );
        }

        // Register theme styles in head
        if ($this->showStyles) {
            $panel->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => app(Services\ThemeService::class)->renderStyles()
            );
        }

        // Register script to suppress Livewire errors
        if ($this->suppressLivewireErrors) {
            $panel->renderHook(
                PanelsRenderHook::SCRIPTS_BEFORE,
                fn (): string => view('filament-wirechat::components.filament.suppress-livewire-errors')->render()
            );
        }

    }

    /**
     * Filter pages based on exclude/only configuration.
     *
     * @param  array<string>  $allPages
     * @return array<string>
     */
    private function filterPages(array $allPages): array
    {
        // If onlyPages is set, only return those pages
        if ($this->onlyPages !== null) {
            return array_intersect($allPages, $this->onlyPages);
        }

        // Otherwise, exclude the specified pages
        if (! empty($this->excludedPages)) {
            return array_diff($allPages, $this->excludedPages);
        }

        // Return all pages if no filtering is configured
        return $allPages;
    }

    /**
     * Filter resources based on exclude/only configuration.
     *
     * @param  array<string>  $allResources
     * @return array<string>
     */
    private function filterResources(array $allResources): array
    {
        // If onlyResources is set, only return those resources
        if ($this->onlyResources !== null) {
            return array_intersect($allResources, $this->onlyResources);
        }

        // Otherwise, exclude the specified resources
        if (! empty($this->excludedResources)) {
            return array_diff($allResources, $this->excludedResources);
        }

        // Return all resources if no filtering is configured
        return $allResources;
    }
}
