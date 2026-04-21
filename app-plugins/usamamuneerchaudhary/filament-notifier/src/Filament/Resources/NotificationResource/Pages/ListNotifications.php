<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource;

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
