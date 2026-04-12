<?php

namespace Usamamuneerchaudhary\Notifier\Database\Seeders;

use Illuminate\Database\Seeder;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;

class NotifierDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default channels
        NotificationChannel::create([
            'title' => 'Email',
            'type' => 'email',
            'icon' => 'heroicon-o-envelope',
            'is_active' => true,
        ]);

        NotificationChannel::create([
            'title' => 'Slack',
            'type' => 'slack',
            'icon' => 'heroicon-o-chat-bubble-left-right',
            'is_active' => true,
        ]);

        NotificationChannel::create([
            'title' => 'SMS',
            'type' => 'sms',
            'icon' => 'heroicon-o-device-phone-mobile',
            'is_active' => true,
        ]);

        // Create default events
        NotificationEvent::create([
            'group' => 'Projects',
            'name' => 'New Project Created',
            'key' => 'project.created',
            'description' => 'When a new project is created',
            'is_active' => true,
        ]);

    }
}
