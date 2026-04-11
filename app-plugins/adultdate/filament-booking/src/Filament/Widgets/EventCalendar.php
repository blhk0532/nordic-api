<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Attributes\CalendarEventContent;
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Enums\CalendarViewType;
use Adultdate\FilamentBooking\Enums\Priority;
use Adultdate\FilamentBooking\Filament\Actions\CreateAction;
use Adultdate\FilamentBooking\FilamentBookingPlugin;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\BookingMeeting;
use Adultdate\FilamentBooking\Models\BookingSprint;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventDropInfo;
use Adultdate\FilamentBooking\ValueObjects\EventResizeInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class EventCalendar extends CalendarWidget
{
    protected static string $viewIdentifier = 'adultdate/filament-booking::calendar-widget';

    //    protected string|HtmlString|bool|null $heading = 'Calendar';

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
            'view' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'title',
                'center' => '',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay, today prev,next',
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
        if (! $event instanceof BookingSprint && ! $event instanceof Booking) {
            Notification::make()
                ->title('Only sprints and bookings can be resized')
                ->warning()
                ->send();

            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Duration updated')
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

        $meetings = BookingMeeting::query()
            ->withCount('users')
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $sprints = BookingSprint::query()
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $bookings = Booking::query()
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $events = collect()
            ->push(...$meetings)
            ->push(...$sprints)
            ->push(...$bookings);

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
            $this->createAction(Booking::class, 'ctxCreateBooking')
                ->label('Create booking')
                ->icon('heroicon-o-calendar')
                ->modalHeading('Create booking')
                ->modalWidth('6xl')
                ->schema([
                    Section::make('Booking Details')
                        ->schema([
                            TextInput::make('number')
                                ->default('BK-'.random_int(100000, 999999))
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(32)
                                ->columnSpanFull(),

                            Select::make('booking_client_id')
                                ->label('Client')
                                ->relationship('client', 'name')
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),

                            Select::make('service_user_id')
                                ->label('Service User')
                                ->options(User::where('role', 'service')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),

                            DateTimePicker::make('starts_at')
                                ->label('Start Date & Time')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->columnSpanFull(),

                            DateTimePicker::make('ends_at')
                                ->label('End Date & Time')
                                ->required()
                                ->native(false)
                                ->seconds(false)
                                ->rule('after:starts_at')
                                ->columnSpanFull(),

                            ToggleButtons::make('status')->hidden(fn () => ! in_array(Auth::user()?->role, ['admin', 'super', 'manager']))
                                ->label('Status')
                                ->inline()
                                ->options(BookingStatus::restrictedOptions())
                                ->default(BookingStatus::Booked)
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label('Notes')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['booking_user_id'] = Auth::id();

                    return $data;
                })
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
                        'number' => 'OR-'.random_int(100000, 999999),
                        'starts_at' => $start->toDateTimeString(),
                        'ends_at' => $end->toDateTimeString(),
                    ]);
                })
                ->after(fn () => $this->refreshRecords()),

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
        if (! $event instanceof BookingMeeting && ! $event instanceof BookingSprint && ! $event instanceof Booking) {
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

    #[CalendarEventContent(model: Booking::class)]
    protected function bookingEventContent(): string
    {
        return view('adultdate/filament-booking::components.calendar.events.booking')->render();
    }
}
