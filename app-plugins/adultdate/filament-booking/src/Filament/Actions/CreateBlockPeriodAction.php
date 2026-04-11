<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Actions;

use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Carbon\Carbon;
use Filament\Actions\Action;

class CreateBlockPeriodAction extends Action
{
    public ?array $calendarData = null;

    public ?array $mountedActions = null;

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

    public function adminAction(): Action
    {
        $this->calendarData = $this->arguments['data'];
        $this->mountedActions = $this->livewire->mountedActions;

        return Action::make('createBlockPeriod')
            ->label('Block Time')
            ->color('danger')
            ->icon('heroicon-o-clock')
            ->action(function () {
                $startDate = Carbon::parse($this->calendarData['start'])->format('Y-m-d');
                $startTime = Carbon::parse($this->calendarData['start'])->format('H:i');
                $endTime = Carbon::parse($this->calendarData['end'])->format('H:i');
                $startVal = $this->calendarData['start_val'];
                $endVal = $this->calendarData['end_val'];
                $dateVal = $this->calendarData['date_val'];
                if ($endTime === $startTime) {
                    //  $endTime = \Carbon\Carbon::parse($startTime)->addHour()->format('H:i');
                    $startDate = Carbon::parse($dateVal)->format('Y-m-d');
                    $startTime = Carbon::parse($startVal)->format('H:i');
                    $endTime = Carbon::parse($endVal)->format('H:i');
                }
                $data = [
                    'date' => $startDate->format('Y-m-d'),
                    ...$data,
                    'start' => $startTime,
                    'end' => $endTime,
                    'service_date' => $startDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'start_val' => $startVal,
                    'end_val' => $endVal,
                    'date_val' => $dateVal,
                ];
                logger()->info('BookingCalendarWidget: BLOCK PERIOD DATA', $data);
                $this->replaceMountedAction('createServicePeriod', ['data' => $data]);
                $newIndex = max(0, count($this->mountedActions) - 1);
                $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
            });
    }
}
