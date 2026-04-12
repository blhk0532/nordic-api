<?php

namespace Usamamuneerchaudhary\Notifier;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Usamamuneerchaudhary\Notifier\Commands\CleanupAnalyticsCommand;
use Usamamuneerchaudhary\Notifier\Commands\NotifierInstallCommand;
use Usamamuneerchaudhary\Notifier\Commands\SendTestNotificationCommand;
use Usamamuneerchaudhary\Notifier\Http\Controllers\NotificationPreferenceController;
use Usamamuneerchaudhary\Notifier\Http\Controllers\NotificationTrackingController;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;
use Usamamuneerchaudhary\Notifier\Services\AnalyticsService;
use Usamamuneerchaudhary\Notifier\Services\ChannelDriverFactory;
use Usamamuneerchaudhary\Notifier\Services\NotificationRepository;
use Usamamuneerchaudhary\Notifier\Services\NotifierManager;
use Usamamuneerchaudhary\Notifier\Services\PreferenceService;
use Usamamuneerchaudhary\Notifier\Services\UrlTrackingService;

class NotifierServiceProvider extends PackageServiceProvider
{
    public static string $name = 'notifier';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->hasCommands([
                NotifierInstallCommand::class,
                SendTestNotificationCommand::class,
                CleanupAnalyticsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('notifier', function () {
            return new NotifierManager;
        });

        $this->app->bind('notifier.channel', NotificationChannel::class);
        $this->app->bind('notifier.event', NotificationEvent::class);
        $this->app->bind('notifier.template', NotificationTemplate::class);
        $this->app->bind('notifier.preference', NotificationPreference::class);
        $this->app->bind('notifier.notification', Notification::class);

        $this->app->singleton(PreferenceService::class);
        $this->app->singleton(AnalyticsService::class);
        $this->app->singleton(UrlTrackingService::class);
        $this->app->singleton(NotificationRepository::class);
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notifier');

        $this->registerChannelsFromDatabase();
        $this->registerApiRoutes();
        $this->registerTrackingRoutes();
    }

    /**
     * Register API routes for user preferences
     */
    protected function registerApiRoutes(): void
    {
        Route::prefix('api/notifier/preferences')->group(function () {
            Route::get('/', [NotificationPreferenceController::class, 'index']);
            Route::get('/available', [NotificationPreferenceController::class, 'available']);
            Route::get('/{eventKey}', [NotificationPreferenceController::class, 'show']);
            Route::put('/{eventKey}', [NotificationPreferenceController::class, 'update']);
        });
    }

    /**
     * Register tracking routes for analytics ---- public routes
     */
    protected function registerTrackingRoutes(): void
    {
        Route::prefix('notifier/track')->group(function () {
            Route::get('/open/{token}', [NotificationTrackingController::class, 'trackOpen']);
            Route::get('/click/{token}', [NotificationTrackingController::class, 'trackClick']);
        });
    }

    protected function registerChannelsFromDatabase(): void
    {
        try {
            if (! Schema::hasTable('notifier_channels')) {
                return;
            }

            $notifier = $this->app->make('notifier');
            $channels = NotificationChannel::where('is_active', true)->get();

            $driverFactory = new ChannelDriverFactory;
            foreach ($channels as $channel) {
                $driver = $driverFactory->create($channel->type);
                if ($driver) {
                    $notifier->registerChannel($channel->type, $driver);
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            // Silently fail
        }
    }
}
