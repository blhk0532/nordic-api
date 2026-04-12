<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\ChannelDriverInterface;
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\DiscordDriver;
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\EmailDriver;
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\PushDriver;
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\SlackDriver;
use Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\SmsDriver;

class ChannelDriverFactory
{
    /**
     * Create a driver instance for the given channel type
     */
    public function create(string $channelType): ?ChannelDriverInterface
    {
        return match ($channelType) {
            'email' => new EmailDriver,
            'slack' => new SlackDriver,
            'sms' => new SmsDriver,
            'push' => new PushDriver,
            'discord' => new DiscordDriver,
            default => null,
        };
    }

    /**
     * Get all supported channel types
     */
    public function getSupportedTypes(): array
    {
        return ['email', 'slack', 'sms', 'push', 'discord'];
    }
}
