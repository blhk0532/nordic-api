<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking;

use Adultdate\FilamentBooking\Commands\FilamentBookingCommand;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\BookingCalendar;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar1;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar2;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar3;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar4;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar5;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar6;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\BookingCalendarResource;
use Adultdate\FilamentBooking\Filament\Widgets\BookingCalendarWidget;
use Adultdate\FilamentBooking\Filament\Widgets\BookingFullCalendarWidget;
use Adultdate\FilamentBooking\Filament\Widgets\EventCalendar;
use Adultdate\FilamentBooking\Filament\Widgets\LocationCalendarWidget;
use Adultdate\FilamentBooking\Testing\TestsFilamentBooking;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX1;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX2;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX3;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX4;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX5;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendarX6;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\SingleCalendars;
use App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\SlideoutCalendar;
use App\Filament\App\Pages\AppChatDashboard;
use App\Filament\App\Pages\AppRingLista;
use App\Livewire\ManusIconModal;
use Filament\Facades\Filament;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FilamentBookingServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-booking';

    public static string $viewNamespace = 'filament-booking';

    protected $lang_path;

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(self::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->startWith(function (InstallCommand $installCommand) {
                        $installCommand->info('Creating storage directories...');
                        $this->createStorageDirectories();
                    })
                    ->endWith(function (InstallCommand $installCommand) {
                        $installCommand->info('Running storage:link command...');
                        $this->runStorageLink();
                        $installCommand->info('Filament Booking installed successfully!');
                    })
                    ->askToStarRepoOnGitHub('adultdate/filament-booking');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            // $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(self::$viewNamespace);
        }
    }

    public function packageBooted(): void
    {
        // Register observers
        Models\Booking\Booking::observe(Observers\BookingObserver::class);

        // Asset Registration
        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Register Media Library Routes
        $this->registerMediaRoutes();

        // Register Livewire component aliases for calendar widgets
        if (class_exists('\Livewire\Livewire')) {
            Livewire::component('adultdate.filament-booking.filament.widgets.full-calendar-widget', BookingFullCalendarWidget::class);
            Livewire::component('adultdate.filament-booking.filament.widgets.booking-calendar-widget', BookingCalendarWidget::class);
            Livewire::component('adultdate.filament-booking.filament.widgets.event-calendar', LocationCalendarWidget::class);
            Livewire::component('adultdate.filament-booking.filament.resources.booking.daily-locations.widgets.event-calendar', \Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\EventCalendar::class);
            // Register resource-scoped widget alias so Livewire can resolve
            // widgets referenced by their Filament resource path.
            Livewire::component(
                'adultdate.filament-booking.filament.resources.booking.daily-locations.widgets.location-calendar-widget',
                \Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\LocationCalendarWidget::class
            );

            Livewire::component(
                'adultdate.filament-booking.filament.widgets.booking-calendar',
                BookingCalendar::class
            );
            //    \Livewire\Livewire::component('filament.app.pages.chat-dashboard', \App\Filament\App\Pages\AppChatDashboard::class);
            Livewire::component('adultdate.filament-booking.filament.widgets.event-calendar', EventCalendar::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar1', MultiCalendar1::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar2', MultiCalendar2::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar3', MultiCalendar3::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar4', MultiCalendar4::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar5', MultiCalendar5::class);
            Livewire::component('adultdate.filament-booking.filament.clusters.services.resources.bookings.widgets.multi-calendar6', MultiCalendar6::class);
            Livewire::component('app.filament.app.clusters.services.resources.bookings.widgets.single-calendars', SingleCalendars::class);
        }

        FilamentAsset::register(
            assets: [
                AlpineComponent::make(
                    'calendar',
                    __DIR__.'/../dist/js/calendar.js',
                ),
                AlpineComponent::make(
                    'calendar-context-menu',
                    __DIR__.'/../dist/js/calendar-context-menu.js',
                ),
                AlpineComponent::make(
                    'calendar-event',
                    __DIR__.'/../dist/js/calendar-event.js',
                ),
                AlpineComponent::make('filament-booking-alpine', __DIR__.'/../resources/dist/filament-booking.js'),
                AlpineComponent::make('filament-fullcalendar-alpine', __DIR__.'/../dist/js/filament-fullcalendar.js'),
                Css::make('calendar-styles', 'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.css'),
                Js::make('calendar-script', 'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.js'),
            ],
            package: 'adultdate/filament-booking'
        );

        // Ensure views are available under the legacy namespace used across the package
        $viewsPath = __DIR__.'/../resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'adultdate/filament-booking');
        }

        // Migration Publishing
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'adultdate/filament-booking-migrations');

        // Testing
        Testable::mixin(new TestsFilamentBooking);

        // Register Filament resources when Filament is available
        if (class_exists(Filament::class)) {
            Filament::serving(function (): void {
                $panel = Filament::getCurrentPanel();

                // Only register BookingCalendarResource for specific panels
                if ($panel && in_array($panel->getId(), ['super', 'booking', 'calendar', 'queue'])) {
                    Filament::registerResources([
                        BookingCalendarResource::class,
                    ]);
                }

                // Ensure the app-level chat dashboard page is registered so the
                // navigation item and route `filament.app.pages.chat-dashboard` exist.
                if (class_exists(AppChatDashboard::class)) {
                    Filament::registerPages([
                        AppChatDashboard::class,
                    ]);
                }
            });
        }

        // Register Livewire component aliases for App-level Filament widgets
        // so resource-scoped widget names used in blade/views can be resolved.
        if (class_exists('\Livewire\Livewire')) {
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.booking-calendar',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\BookingCalendar::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar1',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar1::class
            );

            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar2',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar2::class
            );

            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar3',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar3::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar4',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar4::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar5',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar5::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar6',
                \App\Filament\App\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar6::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x1',
                MultiCalendarX1::class
            );

            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x2',
                MultiCalendarX2::class
            );

            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x3',
                MultiCalendarX3::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x4',
                MultiCalendarX4::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x5',
                MultiCalendarX5::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.multi-calendar-x6',
                MultiCalendarX6::class
            );
            Livewire::component(
                'app.filament.app.clusters.services.resources.bookings.widgets.slideout-calendar',
                SlideoutCalendar::class
            );
            Livewire::component(
                'filament.app.pages.chat-dashboard',
                AppChatDashboard::class
            );
            Livewire::component(
                'filament.app.pages.ring-lista',
                AppRingLista::class
            );
        }
    }

    protected function getAssetPackageName(): ?string
    {
        return 'adultdate/filament-booking';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {

        $distPath = __DIR__.'/../../dist/js';

        if (is_dir($distPath)) {
            return [
                // Ensure EventCalendar runtime is loaded first so it exposes a global
                Js::make(
                    'event-calendar-script',
                    'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.js'
                ),
                Css::make(
                    'event-calendar-styles',
                    'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.css'
                ),
                // Calendar Alpine components
                AlpineComponent::make(
                    'calendar',
                    $distPath.'/calendar.js'
                ),
                AlpineComponent::make(
                    'calendar-context-menu',
                    $distPath.'/calendar-context-menu.js'
                ),
                AlpineComponent::make(
                    'calendar-event',
                    $distPath.'/calendar-event.js'
                ),
                AlpineComponent::make(
                    'filament-fullcalendar-alpine',
                    $distPath.'/filament-fullcalendar.js'
                ),
            ];
        }

        return [];

    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentBookingCommand::class,
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
     * @return array<string>
     */
    protected function getRoutes(): array
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
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_booking_addressable_table',
            'create_booking_addresses_table',
            'create_booking_clients_table',
            'create_booking_comments_table',
            'create_booking_exports_table',
            'create_booking_imports_table',
            'create_booking_media_table',
            'create_booking_notifications_table',
            'create_booking_payments_table',
            'create_booking_settings_table',
            'create_booking_brands_table',
            'create_booking_categories_table',
            'create_booking_category_product_table',
            'create_booking_customers_table',
            'create_booking_order_items_table',
            'create_booking_orders_table',
            'create_booking_products_table',
            'create_booking_tag_tables',
        ];
    }

    protected function createStorageDirectories(): void
    {
        $directories = [
            storage_path('app/product-images'),
            storage_path('app/public'),
            storage_path('app/private'),
        ];

        $filesystem = app(Filesystem::class);

        foreach ($directories as $directory) {
            if (! $filesystem->exists($directory)) {
                $filesystem->makeDirectory($directory, 0755, true);
            }
        }
    }

    protected function runStorageLink(): void
    {
        $exitCode = Artisan::call('storage:link');

        if ($exitCode === 0) {
            // Storage link was successful
            return;
        }

        // If storage link failed, try to create the links manually
        $this->createStorageLinks();
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('manus-icon-modal', ManusIconModal::class);
    }

    protected function createStorageLinks(): void
    {
        $filesystem = app(Filesystem::class);
        $publicStorage = public_path('storage');
        $appStorage = storage_path('app/public');

        // Remove existing link if it exists
        if ($filesystem->exists($publicStorage)) {
            $filesystem->delete($publicStorage);
        }

        // Create the storage link
        $filesystem->link($appStorage, $publicStorage);

        // Create product-images link
        $productImagesLink = public_path('storage/product-images');
        $productImagesTarget = storage_path('app/product-images');

        if (! $filesystem->exists($productImagesLink)) {
            $filesystem->link($productImagesTarget, $productImagesLink);
        }
    }

    protected function registerMediaRoutes(): void
    {
        // Register media library routes for serving media files and conversions
        app('router')->get('/storage/product-images/{mediaId}/conversions/{conversionName}', function ($mediaId, $conversionName) {
            $media = Media::findOrFail($mediaId);

            if ($media->hasGeneratedConversion($conversionName)) {
                return response()->file($media->getPath($conversionName));
            }

            abort(404);
        })->name('media.conversion');

        app('router')->get('/storage/product-images/{mediaId}/{filename}', function ($mediaId, $filename) {
            $media = Media::findOrFail($mediaId);

            return response()->file($media->getPath());
        })->name('media.file');
    }
}
