<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationTemplateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationTemplateResource;
use Illuminate\Contracts\Support\Htmlable;


class ListNotificationTemplates extends ListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

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
