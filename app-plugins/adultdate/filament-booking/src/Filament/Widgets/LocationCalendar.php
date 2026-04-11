<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Attributes\CalendarEventContent;
use Adultdate\FilamentBooking\Enums\CalendarViewType;
use Adultdate\FilamentBooking\Enums\Priority;
use Adultdate\FilamentBooking\Filament\Actions\CreateAction;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Schemas\DailyLocationForm;
use Adultdate\FilamentBooking\FilamentBookingPlugin;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\BookingMeeting;
use Adultdate\FilamentBooking\Models\BookingSprint;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventDropInfo;
use Adultdate\FilamentBooking\ValueObjects\EventResizeInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class LocationCalendarWidget extends FullCalendarWidget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected static string $viewIdentifier = 'adultdate/filament-booking::calendar-widget';

    protected string|HtmlString|bool|null $heading = 'Calendar';

    protected bool $eventClickEnabled = true;

    protected bool $eventDragEnabled = true;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected bool $eventResizeEnabled = true;

    protected bool $dateClickEnabled = true;

    protected static ?int $sort = 1;

    public function getView(): string
    {
        return 'adultdate/filament-booking::calendar-widget';
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    public function getConfig(): array
    {
        return array_replace_recursive(
            $this->config(),
            FilamentBookingPlugin::get()->getConfig(),
        );
    }

    public function config(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '07:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '21:00:00';

        $config = [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'title',
                'center' => '',
                'end' => 'dayGridMonth,timeGridWeek today prev,next',
            ],
            'nowIndicator' => true,
            'dateClick' => true,
            'eventClick' => true,
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 30,
                ],
                'timeGridWeek' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 30,
                ],
                'timeGridMonth' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 30,
                ],
            ],
        ];

        return $config;
    }

    public function onDateClick(string $date, bool $allDay, ?array $view, ?array $resource): void
    {
        $allDay = (bool) $allDay;

        $timezone = config('app.timezone');
        $startDate = Carbon::parse($date, $timezone);

        if ($allDay) {
            $this->mountAction('create-daily-location', [
                'date' => $startDate->format('Y-m-d'),
            ]);
        }
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        $allDay = (bool) $allDay;

        $timezone = config('app.timezone');
        $startDate = Carbon::parse($start, $timezone);

        if ($allDay) {
            $this->mountAction('create-daily-location', [
                'date' => $startDate->format('Y-m-d'),
            ]);
        }
    }

    public function createDailyLocationAction(): Action
    {
        return Action::make('create-daily-location')
            ->label('Create Daily Location')
            ->icon('heroicon-o-plus')
            ->schema(fn (Schema $schema) => DailyLocationForm::configure($schema))
            ->action(function (array $data, array $arguments) {
                if (isset($arguments['date'])) {
                    $data['date'] = $arguments['date'];
                }

                // Create the record
                DailyLocation::create($data);

                // Send notification
                Notification::make()
                    ->title('Daily location created successfully')
                    ->success()
                    ->send();
            })
            ->modalWidth('xl')
            ->slideOver();
    }

    public function onEventClick(array $event): void
    {
        if (str_starts_with($event['id'], 'location-')) {
            $id = str_replace('location-', '', $event['id']);
            $this->mountAction('edit-daily-location', ['id' => $id]);
        }
    }

    public function editDailyLocationAction(): Action
    {
        return Action::make('edit-daily-location')
            ->label('Edit Daily Location')
            ->icon('heroicon-o-pencil')
            ->fillForm(function (array $arguments) {
                $dailyLocation = DailyLocation::find($arguments['id']);

                return $dailyLocation ? $dailyLocation->toArray() : [];
            })
            ->schema(fn (Schema $schema) => DailyLocationForm::configure($schema))
            ->action(function (array $data, array $arguments) {
                $dailyLocation = DailyLocation::find($arguments['id']);
                if ($dailyLocation) {
                    $dailyLocation->update($data);
                    Notification::make()
                        ->title('Daily location updated successfully')
                        ->success()
                        ->send();
                }
            })
            ->modalWidth('xl')
            ->slideOver();
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! $event instanceof BookingSprint) {
            Notification::make()
                ->title('Only sprints can be resized')
                ->warning()
                ->send();

            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Sprint duration updated')
            ->success()
            ->send();

        $this->refreshRecords();

        return true;
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        Log::info('getEvents called', ['start' => $start, 'end' => $end]);

        // Also include DailyLocation entries as all-day events on calendar
        $dailyLocations = DailyLocation::query()
            ->whereBetween('date', [$start, $end])
            ->with(['serviceUser'])
            ->get();

        $locationEvents = $dailyLocations->map(function (DailyLocation $loc) {
            $title = $loc->location ?: ($loc->serviceUser?->name ?? 'Location');

            return [
                'id' => 'location-'.$loc->id,
                'title' => $title,
                'start' => $loc->date?->toDateString(),
                'allDay' => true,
                'backgroundColor' => 'primary',
                'borderColor' => 'transparent',
                'textColor' => '#111827',
                'extendedProps' => [
                    'is_location' => true,
                    'daily_location_id' => $loc->id,
                    'service_user_id' => $loc->service_user_id,
                    'location' => $loc->location,
                ],
            ];
        })->toArray();

        return $locationEvents;
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction()->label('View'),
            $this->editAction()->label('Edit'),
            $this->deleteAction()->label('Delete'),
        ];
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createDailyLocationAction(),
        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createAction(BookingSprint::class, 'ctxCreateSprint')
                ->label('Plan sprint')
                ->icon('heroicon-o-flag')
                ->modalHeading('Plan sprint')
                ->modalWidth('4xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateSelectInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->start->toMutable();
                    $end = $info->end->toMutable();

                    if ($info->allDay) {
                        $start->startOfDay();
                        $end->subDay()->endOfDay();
                    }

                    $schema->fill([
                        'title' => '',
                        'priority' => Priority::Medium->value,
                        'starts_at' => $start->format('Y-m-d'),
                        'ends_at' => ($end->greaterThan($start) ? $end : $start->copy()->addDay())->format('Y-m-d'),
                    ]);
                }),
        ];
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! $event instanceof BookingMeeting && ! $event instanceof BookingSprint) {
            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Event rescheduled')
            ->success()
            ->send();

        $this->refreshRecords();

        return true;
    }

    #[CalendarEventContent(model: DailyLocation::class)]
    protected function dailyLocationEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.daily-location')->render();
    }
}
