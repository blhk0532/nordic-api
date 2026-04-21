<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Notifications\Notification;

trait SendsFilamentNotifications
{
    /**
     * Send a Filament notification
     */
    public function sendNotification(string $title, string $message = '', string $type = 'success'): void
    {
        $notification = Notification::make()
            ->title($title);

        if ($message) {
            $notification->body($message);
        }

        match ($type) {
            'success' => $notification->success(),
            'danger' => $notification->danger(),
            'warning' => $notification->warning(),
            'info' => $notification->info(),
            default => $notification->success(),
        };

        $notification->send();
    }
}
