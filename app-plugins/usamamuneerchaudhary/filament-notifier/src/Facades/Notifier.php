<?php

namespace Usamamuneerchaudhary\Notifier\Facades;

use Illuminate\Support\Facades\Facade;
use Usamamuneerchaudhary\Notifier\Services\NotifierManager;

/**
 * @method static void send($user, string $eventKey, array $data = [])
 * @method static void sendNow($user, string $eventKey, array $data = [])
 * @method static void sendToChannel($user, string $eventKey, string $channelType, array $data = [])
 * @method static void schedule($user, string $eventKey, \Carbon\Carbon $scheduledAt, array $data = [])
 * @method static void registerChannel(string $type, $handler)
 * @method static void registerEvent(string $key, array $config)
 * @method static array getRegisteredChannels()
 * @method static array getRegisteredEvents()
 *
 * @see NotifierManager
 */
class Notifier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'notifier';
    }
}
