<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Support\Facades\Cache;
use Usamamuneerchaudhary\Notifier\Models\Notification;

class NotificationRepository
{
    /**
     * Get notification by tracking token from cache/database
     */
    public function findByToken(string $token): ?Notification
    {
        $notificationId = Cache::get("notifier:tracking_token:{$token}");

        if ($notificationId) {
            return Notification::find($notificationId);
        }

        $notifications = Notification::whereJsonContains('data->tracking_token', $token)->get();

        if ($notifications->count() > 0) {
            $notification = $notifications->first();
            Cache::put("notifier:tracking_token:{$token}", $notification->id, now()->addDays(30));

            return $notification;
        }

        return null;
    }
}
