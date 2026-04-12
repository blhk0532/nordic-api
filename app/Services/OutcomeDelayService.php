<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OutcomeDelaySetting;
use App\Models\OutcomeSetting;

final readonly class OutcomeDelayService
{
    /**
     * Get the delay in minutes for a given outcome
     *
     * @param  string  $outcomeValue  The outcome enum value
     * @return int|null The delay in minutes, or null if not configured
     */
    public static function getDelay(string $outcomeValue): ?int
    {
        $setting = OutcomeSetting::where('outcome', $outcomeValue)
            ->where('is_active', true)
            ->first();

        if ($setting?->delay_minutes !== null) {
            return $setting->delay_minutes;
        }

        $fallback = OutcomeDelaySetting::where('outcome', $outcomeValue)
            ->where('is_active', true)
            ->first();

        return $fallback?->delay_minutes;
    }

    /**
     * Get all active outcome delay settings
     *
     * @return array<string, int>
     */
    public static function getAllDelays(): array
    {
        return OutcomeDelaySetting::where('is_active', true)
            ->pluck('delay_minutes', 'outcome')
            ->toArray();
    }

    /**
     * Get the maximum retry count for a given outcome
     *
     * @param  string  $outcomeValue  The outcome enum value
     * @return int The max retry count, defaults to 3 if not configured
     */
    public static function getMaxRetryCount(string $outcomeValue): int
    {
        $setting = OutcomeSetting::where('outcome', $outcomeValue)
            ->where('is_active', true)
            ->first();

        if ($setting?->max_retry_count !== null) {
            return $setting->max_retry_count;
        }

        $fallback = OutcomeDelaySetting::where('outcome', $outcomeValue)
            ->where('is_active', true)
            ->first();

        return $fallback?->max_retry_count ?? 3;
    }
}
