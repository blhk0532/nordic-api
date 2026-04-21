<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource;

class ListNotificationEvents extends ListRecords
{
    protected static string $resource = NotificationEventResource::class;

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
