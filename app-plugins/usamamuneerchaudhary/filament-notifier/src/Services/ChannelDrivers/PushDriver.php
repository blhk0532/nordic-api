<?php

namespace Usamamuneerchaudhary\Notifier\Services\ChannelDrivers;

use Illuminate\Support\Facades\Http;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;

class PushDriver implements ChannelDriverInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $channel = NotificationChannel::where('type', 'push')->first();

            if (! $channel || ! isset($channel->settings['firebase_server_key'])) {
                return false;
            }

            $user = $notification->user;
            if (! $user) {
                return false;
            }

            $fcmToken = $user->fcm_token ?? $user->push_token ?? null;

            if (! $fcmToken) {
                \Log::warning("Push notification failed: No FCM token for user {$user->id}");

                return false;
            }

            $settings = $channel->settings;

            $response = Http::withHeaders([
                'Authorization' => 'key='.$settings['firebase_server_key'],
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $notification->subject,
                    'body' => $notification->content,
                ],
                'data' => $notification->data ?? [],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Push notification failed: '.$e->getMessage());

            return false;
        }
    }

    public function validateSettings(array $settings): bool
    {
        return ! empty($settings['firebase_server_key'] ?? null) &&
               ! empty($settings['firebase_project_id'] ?? null);
    }
}
