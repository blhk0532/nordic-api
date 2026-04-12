<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class RateLimitingService
{
    /**
     * Check if notification can be sent based on rate limits
     */
    public function canSend(): bool
    {
        $rateLimiting = NotificationSetting::getRateLimiting();

        if (! ($rateLimiting['enabled'] ?? config('notifier.settings.rate_limiting.enabled', true))) {
            return true;
        }

        $maxPerMinute = $rateLimiting['max_per_minute'] ?? config('notifier.settings.rate_limiting.max_per_minute', 60);
        $maxPerHour = $rateLimiting['max_per_hour'] ?? config('notifier.settings.rate_limiting.max_per_hour', 1000);
        $maxPerDay = $rateLimiting['max_per_day'] ?? config('notifier.settings.rate_limiting.max_per_day', 10000);

        return $this->checkLimit('minute', $maxPerMinute)
            && $this->checkLimit('hour', $maxPerHour)
            && $this->checkLimit('day', $maxPerDay);
    }

    /**
     * Get current count for a time period
     */
    public function getCurrentCount(string $period): int
    {
        $key = $this->getCacheKey($period);

        return Cache::get($key, 0);
    }

    /**
     * Increment rate limit counter
     */
    public function increment(): void
    {
        $now = now();

        $minuteKey = $this->getCacheKey('minute', $now);
        Cache::increment($minuteKey);
        Cache::put($minuteKey, Cache::get($minuteKey, 0), now()->addMinutes(2));

        $hourKey = $this->getCacheKey('hour', $now);
        Cache::increment($hourKey);
        Cache::put($hourKey, Cache::get($hourKey, 0), now()->addHours(2));

        $dayKey = $this->getCacheKey('day', $now);
        Cache::increment($dayKey);
        Cache::put($dayKey, Cache::get($dayKey, 0), now()->addDays(2));
    }

    /**
     * Check if limit is exceeded for a given period
     */
    protected function checkLimit(string $period, int $maxLimit): bool
    {
        $key = $this->getCacheKey($period);
        $count = Cache::get($key, 0);

        if ($count >= $maxLimit) {
            Log::warning("Rate limit exceeded: {$period} limit ({$maxLimit})", [
                'current_count' => $count,
                'limit' => $maxLimit,
                'period' => $period,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get cache key for a time period
     */
    protected function getCacheKey(string $period, ?Carbon $time = null): string
    {
        $time = $time ?? now();

        return match ($period) {
            'minute' => 'notifier:rate_limit:minute:'.$time->format('Y-m-d-H-i'),
            'hour' => 'notifier:rate_limit:hour:'.$time->format('Y-m-d-H'),
            'day' => 'notifier:rate_limit:day:'.$time->format('Y-m-d'),
            default => "notifier:rate_limit:{$period}:".$time->timestamp,
        };
    }

    /**
     * Get rate limit status information
     */
    public function getStatus(): array
    {
        $rateLimiting = NotificationSetting::getRateLimiting();

        return [
            'enabled' => $rateLimiting['enabled'] ?? config('notifier.settings.rate_limiting.enabled', true),
            'limits' => [
                'minute' => [
                    'max' => $rateLimiting['max_per_minute'] ?? config('notifier.settings.rate_limiting.max_per_minute', 60),
                    'current' => $this->getCurrentCount('minute'),
                ],
                'hour' => [
                    'max' => $rateLimiting['max_per_hour'] ?? config('notifier.settings.rate_limiting.max_per_hour', 1000),
                    'current' => $this->getCurrentCount('hour'),
                ],
                'day' => [
                    'max' => $rateLimiting['max_per_day'] ?? config('notifier.settings.rate_limiting.max_per_day', 10000),
                    'current' => $this->getCurrentCount('day'),
                ],
            ],
        ];
    }
}
