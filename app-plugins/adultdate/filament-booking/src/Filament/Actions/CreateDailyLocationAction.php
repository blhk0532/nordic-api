<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Actions;

use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateDailyLocationAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->model(fn (HasCalendar $livewire) => $livewire->getEventModel())
            ->record(fn (HasCalendar $livewire) => $livewire->getEventRecord())
            ->before(function (HasCalendar $livewire) {
                if (! $livewire->getEventRecord()) {
                    $livewire->refreshRecords();

                    return false; // Prevent the action
                }

                return true;
            })
            ->cancelParentActions();
    }

    public function createDailyLocation(): Action
    {
        return Action::make('createDailyLocation')
            ->label('Create Location')
            ->icon('heroicon-o-map-pin')
            ->color('primary')
            ->modalHeading('Create Daily Location')
            ->modalWidth('md')
            ->model(DailyLocation::class)
            ->schema($this->getFormLocation())
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];

                return [
                    'date' => $data['date_val'] ?? $data['service_date'] ?? $data['date'] ?? now()->format('Y-m-d'),
                    'created_by' => Auth::id(),
                ];
            })
            ->action(function (array $data) {
                $data['created_by'] = Auth::id();
                DailyLocation::updateOrCreate(['date' => $data['date'], 'service_user_id' => $data['service_user_id']], $data);
                $this->refreshRecords();
                Notification::make()
                    ->title('Location saved successfully')
                    ->success()
                    ->send();
            });
    }
}
