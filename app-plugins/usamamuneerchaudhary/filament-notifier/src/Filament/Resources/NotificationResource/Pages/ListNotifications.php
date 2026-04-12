<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Log;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Services\NotifierManager;
use Illuminate\Contracts\Support\Htmlable;


class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): string|Htmlable|null
{
    return null;
}
}
