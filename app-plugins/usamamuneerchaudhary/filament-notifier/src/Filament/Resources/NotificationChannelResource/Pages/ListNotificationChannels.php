<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource;

class ListNotificationChannels extends ListRecords
{
    protected static string $resource = NotificationChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //   Actions\CreateAction::make(),
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
