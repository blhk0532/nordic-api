<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Usamamuneerchaudhary\Notifier\Jobs\SendNotificationJob;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;

class NotifierManager
{
    protected array $channels = [];

    protected array $events = [];

    public function registerChannel(string $type, $handler): void
    {
        $this->channels[$type] = $handler;
    }

    public function registerEvent(string $key, array $config): void
    {
        $this->events[$key] = $config;
    }

    public function send($user, string $eventKey, array $data = []): void
    {
        try {
            $eventConfig = $this->getEventConfig($eventKey);
            if (! $eventConfig) {
                Log::warning("Event configuration not found for: {$eventKey}");

                return;
            }

            $preferenceService = app(PreferenceService::class);
            $preferences = $preferenceService->getUserPreferences($user, $eventKey);

            $template = $this->getTemplate($eventConfig['template'] ?? null);
            if (! $template) {
                Log::warning("Template not found for event: {$eventKey}");

                return;
            }

            $preferenceService = app(PreferenceService::class);
            foreach ($eventConfig['channels'] ?? [] as $channelType) {
                if (! $preferenceService->shouldSendToChannel($user, $channelType, $preferences)) {
                    continue;
                }

                $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
                if (! $notification) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for event {$eventKey}: ".$e->getMessage());
        }
    }

    public function sendNow($user, string $eventKey, array $data = []): void
    {
        $this->send($user, $eventKey, $data);
    }

    public function sendToChannel($user, string $eventKey, string $channelType, array $data = []): void
    {
        try {
            $eventConfig = $this->getEventConfig($eventKey);
            if (! $eventConfig) {
                Log::warning("Event configuration not found for: {$eventKey}");

                return;
            }

            $template = $this->getTemplate($eventConfig['template'] ?? null);
            if (! $template) {
                Log::warning("Template not found for event: {$eventKey}");

                return;
            }

            $channel = NotificationChannel::where('type', $channelType)
                ->where('is_active', true)
                ->first();

            if (! $channel) {
                Log::warning("Channel {$channelType} is not available or active");

                return;
            }

            $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
            if (! $notification) {
                return;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification to channel {$channelType} for event {$eventKey}: ".$e->getMessage());
        }
    }

    public function schedule($user, string $eventKey, Carbon $scheduledAt, array $data = []): void
    {
        $eventConfig = $this->getEventConfig($eventKey);
        $template = $this->getTemplate($eventConfig['template'] ?? null);

        if (! $template) {
            return;
        }

        foreach ($eventConfig['channels'] ?? [] as $channelType) {
            $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
            if (! $notification) {
                continue;
            }

            $notification->update(['scheduled_at' => $scheduledAt]);
            Queue::later($scheduledAt, new SendNotificationJob($notification->id));
        }
    }

    protected function getEventConfig(string $eventKey): ?array
    {
        if (isset($this->events[$eventKey])) {
            return $this->events[$eventKey];
        }

        $event = NotificationEvent::where('key', $eventKey)->first();
        if ($event) {
            $template = $event->templates()->first();
            if ($template) {
                $channels = $event->settings['channels'] ?? ['email'];

                return [
                    'channels' => $channels,
                    'template' => $template,
                ];
            }
        }

        return null;
    }

    protected function getTemplate($template): ?NotificationTemplate
    {
        if (! $template) {
            return null;
        }

        if ($template instanceof NotificationTemplate) {
            return $template;
        }

        return NotificationTemplate::where('name', $template)->first();
    }

    protected function createNotification($user, NotificationTemplate $template, string $channelType, array $data, string $eventKey): ?Notification
    {
        $rateLimitingService = app(RateLimitingService::class);
        if (! $rateLimitingService->canSend()) {
            Log::warning('Notification creation blocked due to rate limit', [
                'user_id' => $user->id ?? null,
                'event_key' => $eventKey,
                'channel' => $channelType,
            ]);

            return null;
        }

        // Generate tracking token for analytics
        $trackingToken = Str::random(32);
        $dataWithUser = array_merge($data, ['user' => $user, 'tracking_token' => $trackingToken]);

        $templateRenderingService = app(TemplateRenderingService::class);
        $renderedContent = $templateRenderingService->render($template, $dataWithUser, $channelType);

        $notificationData = array_merge($data, ['tracking_token' => $trackingToken]);

        $notification = Notification::create([
            'notification_template_id' => $template->id,
            'user_id' => $user->id,
            'channel' => $channelType,
            'subject' => $renderedContent['subject'] ?? '',
            'content' => $renderedContent['content'] ?? '',
            'data' => $notificationData,
            'status' => 'pending',
        ]);

        Cache::put(
            "notifier:tracking_token:{$trackingToken}",
            $notification->id,
            now()->addDays(30)
        );

        $rateLimitingService->increment();

        Queue::push(new SendNotificationJob($notification->id));

        return $notification;
    }

    public function getRegisteredChannels(): array
    {
        return array_keys($this->channels);
    }

    public function getRegisteredEvents(): array
    {
        return array_keys($this->events);
    }
}
