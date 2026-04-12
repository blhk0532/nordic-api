<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource;
use Usamamuneerchaudhary\Notifier\Jobs\SendNotificationJob;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resend')
                ->label('Resend')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'failed')
                ->action(function () {
                    $this->record->update(['status' => 'pending']);
                    SendNotificationJob::dispatch($this->record->id);

                    Notification::make()
                        ->title('Notification queued for resending')
                        ->success()
                        ->send();
                }),
        ];
    }
}
