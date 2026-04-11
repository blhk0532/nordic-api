<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets;

use Adultdate\FilamentBooking\Attributes\CalendarEventContent;
use Adultdate\FilamentBooking\Attributes\CalendarSchema;
use Adultdate\FilamentBooking\Concerns\CanRefreshCalendar;
use Adultdate\FilamentBooking\Concerns\HasOptions;
use Adultdate\FilamentBooking\Concerns\HasSchema;
use Adultdate\FilamentBooking\Concerns\InteractsWithCalendar;
use Adultdate\FilamentBooking\Concerns\InteractsWithEventRecord;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Enums\Priority;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRecords;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\BookingMeeting;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\BookingSprint;
// use Adultdate\FilamentBooking\Filament\Actions\CreateAction;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventDropInfo;
use Adultdate\FilamentBooking\ValueObjects\EventResizeInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LocationCalendarWidget extends Widget implements HasCalendar
{
    use CanBeConfigured, CanRefreshCalendar, HasOptions, HasSchema, InteractsWithCalendar, InteractsWithEventRecord, InteractsWithEvents, InteractsWithRawJS, InteractsWithRecords {
        // Prefer the contract-compatible refreshRecords (chainable) from CanRefreshCalendar
        CanRefreshCalendar::refreshRecords insteadof InteractsWithEvents;

        // Keep the frontend-only refresh available under an alias if needed
        InteractsWithEvents::refreshRecords as refreshRecordsFrontend;

        // Resolve getOptions collision: prefer HasOptions' getOptions which merges config and options
        HasOptions::getOptions insteadof CanBeConfigured;

        InteractsWithEvents::onEventClickLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onDateSelectLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onEventDropLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onEventResizeLegacy insteadof InteractsWithCalendar;
    }

    protected bool $dateClickEnabled = true;

    protected bool $dateSelectEnabled = true;

    protected static ?int $sort = 1;

    public function schema(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    public function getConfig(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '07:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '21:00:00';

        $config = [
            'view' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'title',
                'center' => '',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay today prev,next',
            ],
            'nowIndicator' => true,
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
                'timeGridWeek' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
                'timeGridMonth' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
            ],
        ];

        return $config;
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

    public function mount(): void
    {
        $this->eventClickEnabled = true;
        $this->dateClickEnabled = true;
        $this->eventDragEnabled = true;
        $this->eventResizeEnabled = true;
        $this->dateSelectEnabled = true;
    }

    #[CalendarSchema(model: DailyLocation::class)]
    protected function dailyLocationSchema(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('location')->required(),
        ]);
    }

    #[CalendarSchema(model: BookingMeeting::class)]
    protected function bookingMeetingSchema(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')->required(),
        ]);
    }

    #[CalendarSchema(model: BookingSprint::class)]
    protected function bookingSprintSchema(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')->required(),
        ]);
    }

    #[CalendarSchema(model: BookingServicePeriod::class)]
    protected function bookingServicePeriodSchema(Schema $schema): Schema
    {
        return $schema->schema([
            Hidden::make('service_date')->required(),
            Select::make('service_user_id')
                ->label('Service User')
                ->relationship('serviceUser', 'name')
                ->required(),
            TextInput::make('service_location')
                ->label('Location')
                ->required(),
            TimePicker::make('start_time')
                ->label('Start Time')
                ->seconds(false)
                ->required(),
            TimePicker::make('end_time')
                ->label('End Time')
                ->seconds(false)
                ->required(),
            TextInput::make('period_type')
                ->label('Period Type')
                ->default('unavailable')
                ->required(),
        ]);
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        Log::info('getEvents called', ['start' => $start, 'end' => $end]);

        $dailyLocations = DailyLocation::query()
            ->whereBetween('date', [$start, $end])
            ->with(['serviceUser'])
            ->get();

        $meetings = BookingMeeting::query()
            ->withCount('users')
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $sprints = BookingSprint::query()
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $events = collect()
            ->push(...$dailyLocations)
            ->push(...$meetings)
            ->push(...$sprints);

        Log::info('Events returned', ['count' => $events->count()]);

        return $events;

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
            $this->createAction(DailyLocation::class, 'ctxCreateDailyLocation')
                ->label('Create Daily Location')
                ->icon('heroicon-o-map-pin')
                ->modalHeading('Create Daily Location')
                ->modalWidth('2xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $schema->fill([
                        'date' => $info->date->toDateString(),
                        'created_by' => Auth::id(),
                    ]);
                }),
            $this->createAction(BookingServicePeriod::class, 'ctxCreateServicePeriod')
                ->label('Add Service Period')
                ->icon('heroicon-o-clock')
                ->modalHeading('Add Service Period')
                ->modalWidth('sm')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $schema->fill([
                        'service_date' => $info->date->toDateString(),
                        'created_by' => Auth::id(),
                    ]);
                }),
            $this->createAction(BookingSprint::class, 'ctxCreateSprint')
                ->label('Plan sprint')
                ->icon('heroicon-o-flag')
                ->modalHeading('Plan sprint')
                ->modalWidth('sm')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->date->toMutable();

                    $schema->fill([
                        'priority' => Priority::Medium->value,
                        'starts_at' => $start->format('Y-m-d'),
                        'ends_at' => $start->copy()->addDay()->format('Y-m-d'),
                    ]);
                }),
            $this->createAction(BookingMeeting::class, 'ctxCreateMeeting')
                ->label('Schedule meeting')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Schedule meeting')
                ->modalWidth('4xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->date->toMutable()->setTime(12, 0);

                    $schema->fill([
                        'starts_at' => $start->toDateTimeString(),
                        'ends_at' => $start->copy()->addHour()->toDateTimeString(),
                    ]);
                }),
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

    #[CalendarEventContent(model: BookingMeeting::class)]
    protected function meetingEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.meeting')->render();
    }

    #[CalendarEventContent(model: BookingSprint::class)]
    protected function sprintEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.sprint')->render();
    }

    #[CalendarEventContent(model: DailyLocation::class)]
    protected function locationEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.location')->render();
    }

    #[CalendarEventContent(model: Booking::class)]
    protected function bookingEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.booking')->render();
    }
}
