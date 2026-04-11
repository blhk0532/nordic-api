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
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Actions\CreateAction;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRecords;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\BookingLocation;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\Booking\Service;
use Adultdate\FilamentBooking\Models\BookingMeeting;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\BookingSprint;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventDropInfo;
use Adultdate\FilamentBooking\ValueObjects\EventResizeInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class EventCalendar extends BookingCalendarWidget implements HasCalendar
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

    protected static ?int $sort = 1;

    // Toggleable calendar interaction flags (only dateClickEnabled kept since traits don't define it)
    protected bool $dateClickEnabled = false;

    public function schema(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    public function getModel(): string
    {
        return Booking::class;
    }

    public function getConfig(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '07:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '21:00:00';

        $config = [
            'timeZone' => config('app.timezone'), // Set the calendar timezone
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'prev,next today',
                'center' => 'title',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'nowIndicator' => true,
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                ],
                'timeGridWeek' => [
                    'slotMinTime' => '00:00:00',
                    'slotMaxTime' => '24:00:00',
                ],
                'timeGridMonth' => [
                    'slotMinTime' => '00:00:00',
                    'slotMaxTime' => '24:00:00',
                ],
            ],
        ];

        return $config;
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('booking_client_id')
                ->label('Client')
                ->options(Client::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->maxLength(255),
                    TextInput::make('postal_code')
                        ->maxLength(20),
                    TextInput::make('country')
                        ->default('Sweden')
                        ->dehydrated(false)
                        ->hidden(),
                ])
                ->createOptionUsing(function (array $data) {
                    $data['country'] = 'Sweden';
                    $client = Client::create($data);

                    return $client->id;
                })
                ->required(),

            Select::make('service_id')
                ->label('Service')
                ->options(Service::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('booking_location_id')
                ->label('Location')
                ->options(BookingLocation::where('is_active', true)->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('service_user_id')
                ->label('Service Technician')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->preload(),

            DatePicker::make('service_date')
                ->label('Service Date')
                ->required()
                ->native(false),

            TimePicker::make('start_time')
                ->label('Start Time')
                ->required()
                ->seconds(false)
                ->native(false),

            TimePicker::make('end_time')
                ->label('End Time')
                ->required()
                ->seconds(false)
                ->native(false),

            Select::make('status')->hidden(fn () => ! in_array(Auth::user()?->role, ['admin', 'super', 'manager']))
                ->label('Status')
                ->options(BookingStatus::restrictedOptions())
                ->default(BookingStatus::Booked->value)
                ->required(),

            TextInput::make('total_price')
                ->label('Total Price')
                ->numeric()
                ->prefix('SEK'),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(3),

            Textarea::make('service_note')
                ->label('Service Note')
                ->rows(3),

            Repeater::make('items')
                ->label('Booking Items')
                ->schema([
                    Select::make('booking_service_id')
                        ->label('Service')
                        ->options(Service::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('qty')
                        ->label('Quantity')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),

                    TextInput::make('unit_price')
                        ->label('Unit Price')
                        ->numeric()
                        ->prefix('SEK')
                        ->default(0)
                        ->required(),
                ])
                ->columns(3)
                ->defaultItems(0)
                ->collapsible(),
        ];
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! $event instanceof Booking) {
            Notification::make()
                ->title('Only periods can be resized')
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
        $this->dateClickEnabled = true;
        $this->setDateSelectEnabled(true);
    }

    #[CalendarSchema(model: DailyLocation::class)]
    protected function dailyLocationSchema(Schema $schema): Schema
    {
        return $schema->schema([
            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->native(false),
            Select::make('service_user_id')
                ->label('Service User')
                ->relationship('serviceUser', 'name')
                ->required(),
            TextInput::make('location')->required(),
            Hidden::make('created_by'),
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

    #[CalendarSchema(model: Booking::class)]
    protected function bookingSchema(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')->required(),
            TimePicker::make('start_time')
                ->label('Start Time')
                ->seconds(false)
                ->required(),
            TimePicker::make('end_time')
                ->label('End Time')
                ->seconds(false)
                ->required(),
        ]);
    }

    #[CalendarSchema(model: BookingServicePeriod::class)]
    protected function bookingServicePeriodSchema(Schema $schema): Schema
    {
        return $schema->schema([
            // Ensure service_date is present in the form data even if hidden
            Hidden::make('service_date')
                ->required(),
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

    public function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        Log::info('getEvents called', ['start' => $start, 'end' => $end]);

        $dailyLocations = DailyLocation::query()
            ->whereBetween('date', [$start, $end])
            ->with(['serviceUser'])
            ->get();

        // Query bookings by service_date (bookings use service_date + start_time/end_time, not starts_at/ends_at)
        $bookings = Booking::query()
            ->with(['client', 'service', 'serviceUser', 'bookingUser', 'location', 'items'])
            ->whereBetween('service_date', [$start, $end])
            ->get();

        Log::info('Bookings fetched', ['count' => $bookings->count()]);

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
            ->push(...$bookings)
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
            $this->createAction(Booking::class, 'ctxCreateBooking')
                ->label('New Booking')
                ->form($this->getFormSchema())
                ->mountUsing(function ($formOrSchema, array $arguments) {
                    if ($formOrSchema instanceof Schema) {
                        $formOrSchema->fill([]);
                    } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill([]);
                    }

                    if (! isset($arguments['start']) && ! isset($arguments['service_date'])) {
                        return;
                    }

                    $timezone = config('app.timezone');

                    if (isset($arguments['service_date']) || isset($arguments['start_time'])) {
                        $values = [
                            'service_date' => $arguments['service_date'] ?? null,
                            'start_time' => $arguments['start_time'] ?? null,
                            'end_time' => $arguments['end_time'] ?? null,
                        ];
                    } else {
                        $start = Carbon::parse($arguments['start'], $timezone);
                        $values = ['service_date' => $start->format('Y-m-d')];

                        if ($start->format('H:i:s') !== '00:00:00') {
                            $values['start_time'] = $start->format('H:i');
                        }

                        if (isset($arguments['end'])) {
                            $end = Carbon::parse($arguments['end'], $timezone);
                            if ($end->format('H:i:s') !== '00:00:00') {
                                $values['end_time'] = $end->format('H:i');
                            }
                        }
                    }

                    if ($formOrSchema instanceof Schema) {
                        $formOrSchema->fillPartially($values, array_keys($values));

                        return;
                    }

                    if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill($values);

                        return;
                    }
                })
                ->mutateDataUsing(function (array $data): array {
                    $data['number'] = 'BK-'.now()->format('Ymd').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
                    $data['booking_user_id'] = Auth::id();
                    $data['currency'] = 'SEK';
                    $data['is_active'] = true;

                    if (isset($data['service_date']) && isset($data['start_time'])) {
                        $data['starts_at'] = $data['service_date'].' '.$data['start_time'];
                    }
                    if (isset($data['service_date']) && isset($data['end_time'])) {
                        $data['ends_at'] = $data['service_date'].' '.$data['end_time'];
                    }

                    return $data;
                })
                ->after(function (Booking $record, array $data) {
                    if (isset($data['items']) && is_array($data['items'])) {
                        foreach ($data['items'] as $item) {
                            $record->items()->create([
                                'booking_service_id' => $item['booking_service_id'],
                                'qty' => $item['qty'] ?? 1,
                                'unit_price' => $item['unit_price'] ?? 0,
                            ]);
                        }
                    }
                }),
            $this->createAction(DailyLocation::class, 'ctxCreateDailyLocation')
                ->label('Service Location')
                ->icon('heroicon-o-map-pin')
                ->modalHeading('Service Location')
                ->modalWidth('2xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $schema->fill([
                        'date' => $info->date->toDateString(),
                        'service_user_id' => Auth::id(),
                        'created_by' => Auth::id(),
                    ]);
                }),
            $this->createAction(BookingServicePeriod::class, 'ctxCreateServicePeriod')
                ->label('Add Block Period')
                ->icon('heroicon-o-clock')
                ->modalHeading('Add Block Period')
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

        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createAction(Booking::class, 'ctxCreateBooking')
                ->label('Create Bookings')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Create Bookings')
                ->modalWidth('4xl')
                ->form($this->getFormSchema())
                ->mountUsing(function (...$args) {
                    // Support both invocation signatures:
                    // 1) ($formOrSchema, array $arguments)
                    // 2) (CreateAction $action, ?Schema $schema, ?DateSelectInfo $info)
                    $formOrSchema = $args[0] ?? null;

                    // Log incoming args for debugging date-select payloads
                    try {
                        $debugArgs = array_map(function ($a) {
                            if (is_object($a)) {
                                return json_decode(json_encode($a), true);
                            }

                            return $a;
                        }, $args);

                        Log::info('EventCalendar date-select mount args', ['args' => $debugArgs]);
                    } catch (Throwable $e) {
                        // ignore logging failures
                    }

                    // Reset form state to avoid leftover values
                    if ($formOrSchema instanceof Schema) {
                        $formOrSchema->fill([]);
                    } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill([]);
                    }

                    $values = [];

                    // Case: DateSelectInfo provided as 3rd arg
                    if (isset($args[2]) && $args[2] instanceof DateSelectInfo) {
                        $info = $args[2];
                        $start = $info->start->toMutable();
                        $end = $info->end->toMutable();

                        if ($info->allDay) {
                            $start->startOfDay();
                            $end->subDay()->endOfDay();
                        }

                        $values = [
                            'service_date' => $start->format('Y-m-d'),
                        ];

                        if ($start->format('H:i:s') !== '00:00:00') {
                            $values['start_time'] = $start->format('H:i');
                        }

                        if ($end && $end->format('H:i:s') !== '00:00:00') {
                            $values['end_time'] = $end->format('H:i');
                        }
                    } elseif (isset($args[1]) && (is_array($args[1]) || is_object($args[1]))) {
                        // Case: array or object arguments provided (from JS payload)
                        $arguments = is_object($args[1]) ? json_decode(json_encode($args[1]), true) : $args[1];
                        if (! isset($arguments['start']) && ! isset($arguments['startStr']) && ! isset($arguments['service_date'])) {
                            return;
                        }

                        if (isset($arguments['service_date']) || isset($arguments['start_time'])) {
                            $values = [
                                'service_date' => $arguments['service_date'] ?? null,
                                'start_time' => $arguments['start_time'] ?? null,
                                'end_time' => $arguments['end_time'] ?? null,
                            ];
                        } else {
                            $timezone = config('app.timezone');

                            // Prefer 'startStr'/'endStr' if provided by JS, otherwise fall back to 'start'/'end'.
                            $startRaw = $arguments['startStr'] ?? $arguments['start'] ?? null;
                            $endRaw = $arguments['endStr'] ?? $arguments['end'] ?? null;

                            // If startRaw is an object (stdClass), try to extract string fields
                            if (is_object($startRaw)) {
                                if (isset($startRaw->date)) {
                                    $startRaw = $startRaw->date;
                                } else {
                                    $startRaw = json_encode($startRaw);
                                }
                            }

                            if (is_object($endRaw)) {
                                if (isset($endRaw->date)) {
                                    $endRaw = $endRaw->date;
                                } else {
                                    $endRaw = json_encode($endRaw);
                                }
                            }

                            try {
                                $start = Carbon::parse($startRaw, $timezone);
                            } catch (Throwable $e) {
                                return; // couldn't parse start
                            }

                            $end = null;
                            if ($endRaw) {
                                try {
                                    $end = Carbon::parse($endRaw, $timezone);
                                } catch (Throwable $e) {
                                    $end = null;
                                }
                            }

                            $values = ['service_date' => $start->format('Y-m-d')];

                            if ($start->format('H:i:s') !== '00:00:00') {
                                $values['start_time'] = $start->format('H:i');
                            }

                            if ($end && $end->format('H:i:s') !== '00:00:00') {
                                $values['end_time'] = $end->format('H:i');
                            }
                        }
                    } else {
                        // No recognizable args
                        return;
                    }

                    if ($formOrSchema instanceof Schema) {
                        $formOrSchema->fillPartially($values, array_keys($values));

                        return;
                    }

                    if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill($values);

                        return;
                    }
                }),
            $this->createAction(DailyLocation::class, 'ctxCreateDailyLocation')
                ->label('Service Location')
                ->icon('heroicon-o-map-pin')
                ->modalHeading('Service Location')
                ->modalWidth('2xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $schema->fill([
                        'date' => $info->date->toDateString(),
                        'service_user_id' => Auth::id(),
                        'created_by' => Auth::id(),
                    ]);
                }),
            $this->createAction(BookingServicePeriod::class, 'ctxCreateServicePeriod')
                ->label('Add Block Period')
                ->icon('heroicon-o-clock')
                ->modalHeading('Add Block Period')
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
        ];
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! $event instanceof BookingMeeting && ! $event instanceof BookingSprint && ! $event instanceof DailyLocation && ! $event instanceof BookingServicePeriod) {
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
        return view('adultdate/filament-booking::components.calendar.booking')->render();
    }
}
