<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\DashboardBooking;
use Adultdate\FilamentBooking\Filament\Pages\BookingCalendar;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\BookingOutcallQueueResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\CustomerResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\DailyLocationResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\EventCalendar;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\OrderResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\BookingServicePeriodResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\UserResource;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\BookingCalendarResource;
use Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\BookingDataLeadResource;
use Adultdate\FilamentBooking\Filament\Widgets\BookingCalendarWidget;
use Adultdate\FilamentBooking\Filament\Widgets\CustomersChart;
use Adultdate\FilamentBooking\Filament\Widgets\LatestOrders;
use Adultdate\FilamentBooking\Filament\Widgets\OrdersChart;
use Adultdate\FilamentBooking\Filament\Widgets\StatsOverviewWidget;
use App\Filament\App\Clusters\Services\Resources\Bookings\Pages\BookingCalendersX2;
use App\Filament\App\Clusters\Services\Resources\Bookings\Pages\BookingCalendersX4;
use App\Filament\App\Clusters\Services\Resources\Bookings\Pages\DashboardBokning;
use App\Filament\App\Clusters\Services\Resources\Bookings\Pages\MultiCalendars3;
use App\Filament\App\Pages\InertiaCalendar as AppInertiaCalendar;
use App\Filament\App\Resources\RingaDatas\Pages\QueueRingaData;
use App\Filament\Booking\Pages\GoogleCalendar;
use App\Filament\Booking\Pages\InertiaCalendar;
use App\UserRole;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;

class FilamentBookingPlugin implements Plugin
{
    use EvaluatesClosures;

    protected array $plugins = ['dayGrid', 'timeGrid', 'interaction', 'list', 'resourceTimeGrid', 'resourceTimeline'];

    protected ?string $schedulerLicenseKey = 'CC-Attribution-NonCommercial-NoDerivatives';

    protected array $config = [];

    protected string|Closure|null $timezone = 'Europe/Stockholm';

    protected string|Closure|null $locale = 'sv';

    protected ?bool $editable = true;

    protected ?bool $selectable = true;

    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'adultdate-booking';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->assets([
                Js::make('event-calendar', 'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.js'),
                Css::make('event-calendar', 'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.css'),
            ])
          //  ->discoverClusters(in: app_path('../plugins/adultdate/filament-booking/src/Filament/Clusters'), for: 'Adultdate\\FilamentBooking\\Filament\\Clusters')
          //  ->discoverResources(in: app_path('../plugins/adultdate/filament-booking/src/Filament/Resources'), for: 'Adultdate\\FilamentBooking\\Filament\\Resources')
            ->databaseNotifications()
            ->pages([
                //    BookingCalendar::class,
            ])
            ->resources([
                //    CustomerResource::class,
                //    OrderResource::class,
                //    DailyLocationResource::class,
                //    BookingServicePeriodResource::class,
                //    BookingOutcallQueueResource::class,
                //    UserResource::class,
                //    BookingCalendarResource::class,
                //    BookingDataLeadResource::class,
            ])
            ->widgets([
                //    BookingCalendarWidget::class,
                //    CustomersChart::class,
                //    LatestOrders::class,
                //    OrdersChart::class,
                //    StatsOverviewWidget::class,
                //    EventCalendar::class,
            ]);

        FilamentAsset::register([
            AlpineComponent::make('calendar', __DIR__.'/../dist/js/calendar.js'),
            AlpineComponent::make('calendar-context-menu', __DIR__.'/../dist/js/calendar-context-menu.js'),
            AlpineComponent::make('calendar-event', __DIR__.'/../dist/js/calendar-event.js'),
            AlpineComponent::make('filament-fullcalendar-alpine', __DIR__.'/../dist/js/filament-fullcalendar.js'),
        ], 'adultdate/filament-booking');
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
                QueueRingaData::class,
                DashboardBooking::class,
                DashboardBokning::class,
                GoogleCalendar::class,
                InertiaCalendar::class,
                AppInertiaCalendar::class,
                BookingCalendersX2::class,
                BookingCalendersX4::class,
                MultiCalendars3::class,
            ],
        );

        //    FilamentView::registerRenderHook(
        //        PanelsRenderHook::USER_MENU_BEFORE,
        //        fn () => view('filament.app.calendar-icon-topbar')
        //    );

        //    FilamentView::registerRenderHook(
        //        PanelsRenderHook::USER_MENU_BEFORE,
        //        fn () => view('filament.app.manus-icon-topbar')
        //    );

        //    FilamentView::registerRenderHook(
        //        PanelsRenderHook::SIDEBAR_LOGO_AFTER,
        //        fn () => view('filament.app.calendar-icon-topbar')
        //    );
    }

    public function plugins(array $plugins, bool $merge = true): static
    {
        $this->plugins = $merge ? array_merge($this->plugins, $plugins) : $plugins;

        return $this;
    }

    public function getDayCount(): int
    {
        return data_get($this->config, 'dayCount', 5);
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function schedulerLicenseKey(string $schedulerLicenseKey): static
    {
        $this->schedulerLicenseKey = $schedulerLicenseKey;

        return $this;
    }

    public function getSchedulerLicenseKey(): ?string
    {
        return 'CC-Attribution-NonCommercial-NoDerivatives';
    }

    public function config(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getWeekends(): bool
    {
        return $this->weekends ?? data_get($this->config, 'weekends', false);
    }

    public function timezone(string|Closure $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->evaluate($this->timezone) ?? config('app.timezone');
    }

    public function locale(string|Closure $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->evaluate($this->locale) ?? mb_strtolower(str_replace('_', '-', app()->getLocale()));
    }

    public function editable(bool $editable = true): static
    {
        $this->editable = $editable;

        return $this;
    }

    public function isEditable(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return in_array($user->role, [UserRole::ADMIN, UserRole::SUPER_ADMIN]);
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function isSelectable(): bool
    {
        return $this->selectable ?? data_get($this->config, 'selectable', true);
    }
}
