<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Actions as BookingActions;
use Adultdate\FilamentBooking\Concerns\CanRefreshCalendar;
use Adultdate\FilamentBooking\Concerns\HasOptions;
use Adultdate\FilamentBooking\Concerns\HasSchema;
use Adultdate\FilamentBooking\Concerns\InteractsWithCalendar;
use Adultdate\FilamentBooking\Concerns\InteractsWithEventRecord;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Enums\Context;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Schemas\BookingForm;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRecords;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\BookingLocation;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\Booking\Service;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema as FilamentSchema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

class BookingCalendarWidget extends FullCalendarWidget implements HasCalendar
{
    use CanBeConfigured, CanRefreshCalendar, HasOptions, HasSchema, InteractsWithCalendar, InteractsWithEventRecord, InteractsWithEvents, InteractsWithRawJS, InteractsWithRecords {
        // Prefer the contract-compatible refreshRecords (chainable) from CanRefreshCalendar
        CanRefreshCalendar::refreshRecords insteadof InteractsWithEvents;

        // Keep the frontend-only refresh available under an alias if needed
        InteractsWithEvents::refreshRecords as refreshRecordsFrontend;

        // Resolve getOptions collision: prefer HasOptions' getOptions which merges config and options
        HasOptions::getOptions insteadof CanBeConfigured;

    }
    use InteractsWithEvents {
        InteractsWithEvents::onEventClickLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onDateSelectLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onEventDropLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::onEventResizeLegacy insteadof InteractsWithCalendar;
        InteractsWithEvents::refreshRecords insteadof InteractsWithCalendar;
    }

    public ?int $recordId = null;

    public Model|string|null $model = null;

    /**
     * Whether clicking on a date cell is enabled for the calendar.
     * Set via setDateClickEnabled() in HandlesDateClick trait.
     */
    protected bool $dateClickEnabled = false;

    protected static ?int $sort = 10;

    protected static bool $isDiscovered = true;

    protected static ?string $title = 'Booking Calendar';

    protected static string $viewIdentifier = 'booking-calendar-widget';

    protected int|string|array $columnSpan = 'full';

    public function getModel(): string
    {
        return Booking::class;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getModelAlt(): string
    {
        return Booking::class;
    }

    public function getEventModel(): string
    {
        return Booking::class;
    }

    public function getEventRecord(): ?Booking
    {
        return $this->record;
    }

    public function config(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        return [
            'initialView' => 'timeGridWeek',
            'timeZone' => $settings?->calendar_timezone ?? config('app.timezone'),
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'list dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'nowIndicator' => true,
            'selectable' => true,
            'dateClick' => true,
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '21:00:00',
            'slotDuration' => '00:30:00',
            'allDayText' => '🗓️',
            'allDaySlot' => true,
            'weekends' => $settings?->calendar_weekends ?? true,
            'themeSystem' => $settings?->calendar_theme?->value ?? 'standard',
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '21:00:00',
                ],
                'timeGridWeek' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '21:00:00',
                ],
            ],
            'dayRender' => 'function(info) { info.el.addEventListener("contextmenu", function(e) { e.preventDefault(); $wire.dispatch("calendar--open-menu", { context: "DateClick", data: { date: info.dateStr } }); }); }',
        ];
    }

    public function isEventClickEnabled(): bool
    {
        return true;
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('number')
                ->label('Booking #')
                ->default(fn (): string => $this->generateNumber())
                ->afterStateHydrated(function ($state, ?string $set): void {
                    if (filled($state)) {
                        return;
                    }

                    $set('number', $this->generateNumber());
                })
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32),

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
                ])
                ->createOptionUsing(function (array $data) {
                    return Client::create($data)->id;
                }),

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
                ->required()
                ->dehydrated(),

            Select::make('service_user_id')
                ->label('Service Technician')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->preload(),

            DatePicker::make('service_date')
                ->label('Service Date')
                ->required()
                ->native(false)
                ->dehydrated(),

            TimePicker::make('start_time')
                ->label('Start Time')
                ->required()
                ->seconds(false)
                ->native(false)
                ->dehydrated(),

            TimePicker::make('end_time')
                ->label('End Time')
                ->required()
                ->seconds(false)
                ->native(false)
                ->dehydrated(),

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
                ->label('Internal Notes')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('service_note')
                ->label('Service Notes')
                ->rows(3)
                ->columnSpanFull(),

            $this->getItemsRepeater(),
        ];
    }

    public function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        $bookings = Booking::query()
            ->with(['client', 'service', 'serviceUser', 'bookingUser', 'location'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
                    ->when(
                        Schema::hasColumn('booking_bookings', 'starts_at'),
                        fn ($q) => $q->orWhereBetween('starts_at', [$start, $end]),
                    );
            })
            ->where('is_active', true)
            ->get();

        // Transform bookings to calendar events
        $bookingEvents = $bookings->map(fn (Booking $booking) => $booking->toCalendarEvent())->toArray();

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

        return collect(array_merge($bookingEvents, $locationEvents));
    }

    public function fetchEvents(array $info): array
    {
        // FullCalendar may send `start`/`end` without `startStr`/`endStr`; ensure both for FetchInfo VO.
        $info['startStr'] ??= $info['start'] ?? null;
        $info['endStr'] ??= $info['end'] ?? null;

        if (! ($info['startStr'] && $info['endStr'])) {
            return [];
        }

        return $this->getEventsJs($info);
    }

    public function getHeaderActions(): array
    {
        return [
            BookingActions\CreateAction::make('create')
                ->label('New Booking')
                ->icon('heroicon-o-plus')
                ->hidden()
                ->modalHeading('Create Booking')
                ->modalSubmitActionLabel('Create')
                ->modalWidth('2xl')
                ->schema(fn (FilamentSchema $schema) => $this->getFormSchemaForModel($schema, $this->getModel()))
                ->mountUsing(function ($form, array $arguments) {
                    $data = $arguments['data'] ?? [];
                    $form->fill($this->getDefaultFormData([
                        'service_date' => $data['service_date'] ?? $arguments['service_date'] ?? null,
                        'start_time' => $data['start_time'] ?? $arguments['start_time'] ?? null,
                        'end_time' => $data['end_time'] ?? $arguments['end_time'] ?? null,
                    ]));
                })
                ->using(function (array $data) {
                    $data = $this->normalizeBookingFormData($data);
                    logger()->debug('booking.create.using', $data);
                    $items = $data['items'] ?? [];
                    unset($data['items']);

                    $booking = Booking::create($data);

                    if (! empty($items)) {
                        $this->syncBookingItems($booking, $items);
                    }

                    return $booking;
                })
                ->after(fn () => $this->dispatch('refresh-calendar'))
                ->successNotificationTitle('Booking created successfully'),

        ];
    }

    public function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            // Handle block period action from the create modal footer
            'block-period' => 'onBlockPeriod',
        ]);
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        $allDay = (bool) $allDay;

        logger()->info('BookingCalendarWidget onDateSelect', [
            'start' => $start,
            'end' => $end,
            'allDay' => $allDay,
            'view' => $view,
            'resource' => $resource,
        ]);

        $timezone = config('app.timezone');
        $startDate = Carbon::parse($start, $timezone);

        if ($allDay) {
            logger()->info('BookingCalendarWidget: ALL-DAY CLICK DETECTED!');

            $this->mountAction('create-daily-location', [
                'date' => $startDate->format('Y-m-d'),
            ]);

            return;
        }

        $data = $this->getDefaultFormData([
            'service_date' => $startDate->format('Y-m-d'),
        ]);

        $data['start_time'] = $startDate->format('H:i');
        if ($end) {
            $data['end_time'] = Carbon::parse($end, $timezone)->format('H:i');
        }

        if (! $allDay && $startDate->format('H:i:s') !== '00:00:00') {
            $data['start_time'] = $startDate->format('H:i');

            if ($end) {
                $endDate = Carbon::parse($end, $timezone);
                if ($endDate->format('H:i:s') !== '00:00:00') {
                    $data['end_time'] = $endDate->format('H:i');
                }
            }
        }

        $this->mountAction('create', ['data' => $data]);

        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    public function onBlockPeriod(): void
    {
        $this->mountAction('blockPeriod');
    }

    public function blockPeriodAction(): Action
    {
        return Action::make('blockPeriod')
            ->label('Block Period')
            ->icon('heroicon-o-ban')
            ->color('danger')
            ->form([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
                Textarea::make('reason')
                    ->label('Reason for blocking')
                    ->placeholder('Optional reason for blocking this period'),
            ])
            ->action(function (array $data) {
                // Here you would implement the logic to block the period
                // For example, create blocked bookings or mark dates as unavailable
                // For now, just show a success message
                Notification::make()
                    ->title('Period blocked successfully')
                    ->body("Blocked from {$data['start_date']} to {$data['end_date']}")
                    ->success()
                    ->send();

                // Refresh the calendar to show the blocked period
                $this->refreshRecords();
            });
    }

    public function onEventClick(array $event): void
    {
        // Skip clicks on all-day location events to prevent 404 errors
        if (isset($event['allDay']) && $event['allDay'] === true) {
            return;
        }

        // Skip location events (they have IDs starting with 'location-')
        if (isset($event['id']) && str_starts_with($event['id'], 'location-')) {
            return;
        }

        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }
        if ($this->getModelAlt()) {
            $this->record = $this->resolveRecord($event['id']);
        }
        if (! $this->record) {
            return;
        }

        $this->eventRecord = $this->record;
        $this->record->load('items');
        $this->recordId = $this->record->id;

        $booking = $this->record;
        $user = Auth::user();

        $canEdit = $user->id === $booking->booking_user_id || $this->isAdmin($user);

        $action = $canEdit ? 'edit' : 'view';

        $payload = $this->record->toArray();
        $payload['service_date'] = $this->record->service_date?->format('Y-m-d') ?? ($payload['service_date'] ?? null);

        $this->mountAction($action, [
            'type' => 'click',
            'event' => $event,
            'data' => $payload,
        ]);
    }

    public function getFormSchemaForModel(FilamentSchema $schema, ?string $model = null): FilamentSchema
    {
        return BookingForm::configure($schema);
    }

    public function cacheInteractsWithCalendarActions(): void
    {
        // Cache header actions
        $this->cacheHeaderActions();

        // Cache default actions
        $this->cacheHasDefaultActions();

        // Cache footer actions if the trait is used
        if (method_exists($this, 'cacheFooterActions')) {
            $this->cacheFooterActions();
        }
    }

    public function bootedHasContextMenu(): void
    {
        Log::info('bootedHasContextMenu called in BookingCalendarWidget');

        $this->cacheContextMenuActions();

        $this->setDateClickEnabled($this->hasContextMenu(Context::DateClick));
    }

    protected function getEloquentQuery(): Builder
    {
        return $this->getModel()::query();
    }

    protected function getDailyLocationFormSchema(): array
    {
        return [
            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->native(false)
                ->dehydrated(),

            Select::make('service_user_id')
                ->label('Service User')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('location')
                ->label('Location')
                ->required()
                ->maxLength(255),

            Hidden::make('created_by')
                ->default(fn () => Auth::id()),
        ];
    }

    protected function getItemsRepeater(): Repeater
    {
        return BookingForm::getItemsRepeater()
            ->relationship('items')
            ->defaultItems(0)
            ->minItems(0)
            ->dehydrated(true)
            ->columnSpanFull();
    }

    protected function generateNumber(): string
    {
        return Str::upper(auth()->user()->name).'-'.Str::upper(filament()->getTenant()?->slug).'-'.now()->timestamp;
    }

    protected function getDefaultFormData(array $seed = []): array
    {
        return array_replace([
            'number' => $this->generateNumber(),
            'booking_client_id' => null,
            'service_id' => null,
            'booking_location_id' => null,
            'service_user_id' => null,
            'service_date' => null,
            'start_time' => null,
            'end_time' => null,
            'status' => BookingStatus::Booked->value,
            'total_price' => null,
            'notes' => null,
            'service_note' => null,
            'items' => [],
        ], $seed);
    }

    protected function normalizeBookingFormData(array $data): array
    {
        logger()->debug('booking.form.normalize.before', $data);

        if (! empty($data['service_date']) && $data['service_date'] instanceof CarbonInterface) {
            $data['service_date'] = $data['service_date']->toDateString();
        }

        if (! empty($data['start_time']) && $data['start_time'] instanceof CarbonInterface) {
            $data['start_time'] = $data['start_time']->format('H:i:s');
        }

        if (! empty($data['end_time']) && $data['end_time'] instanceof CarbonInterface) {
            $data['end_time'] = $data['end_time']->format('H:i:s');
        }

        // Derive missing date/time parts from calendar start/end if form lost them.
        if (empty($data['service_date']) && ! empty($data['start'])) {
            $start = Carbon::parse($data['start']);
            $data['service_date'] = $start->toDateString();
            $data['start_time'] ??= $start->format('H:i:s');
        }

        if (empty($data['end_time']) && ! empty($data['end_time'])) {
            $end = Carbon::parse($data['end']);
            $data['end_time'] = $end->format('H:i:s');
        }

        $data['number'] = $data['number'] ?? $this->generateNumber();
        $data['booking_user_id'] = $data['booking_user_id'] ?? Auth::id();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['status'] = $data['status'] ?? BookingStatus::Booked->value;

        // Only set starts_at/ends_at when columns exist.
        if (Schema::hasColumn('booking_bookings', 'starts_at') && isset($data['service_date'], $data['start_time'])) {
            $data['starts_at'] = Carbon::parse($data['service_date'].' '.$data['start_time']);
        }

        if (Schema::hasColumn('booking_bookings', 'ends_at') && isset($data['service_date'], $data['end_time'])) {
            $data['ends_at'] = Carbon::parse($data['service_date'].' '.$data['end_time']);
        }

        logger()->debug('booking.form.normalize.after', $data);

        return $data;
    }

    protected function syncBookingItems(Booking $booking, array $items): void
    {
        $booking->items()->delete();

        foreach ($items as $index => $item) {
            if (empty($item['booking_service_id'])) {
                continue;
            }

            $booking->items()->create([
                'booking_service_id' => $item['booking_service_id'],
                'qty' => $item['qty'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'sort' => $item['sort'] ?? $index,
            ]);
        }

        $booking->refresh()->updateTotalPrice();
    }

    protected function isAdmin(Model $user): bool
    {
        // Admin model is always treated as admin
        if ($user instanceof Admin) {
            return true;
        }

        $userRole = $user->role ?? null;

        if ($userRole instanceof UnitEnum) {
            $roleValue = $userRole->value;
        } else {
            $roleValue = (string) $userRole;
        }

        return in_array($roleValue, ['admin', 'super', 'super_admin'], true);
    }

    protected function getDateClickContextMenuActions(): array
    {
        $user = Auth::user();

        if (! $user || ! $this->isAdmin($user)) {
            return [];
        }

        Log::info('getDateClickContextMenuActions called for admin user', ['user_id' => $user->id]);

        return [
            $this->createAction(Booking::class, 'ctxCreateBooking')
                ->label('New Booking')
                ->form($this->getFormSchema())
                ->mountUsing(function ($formOrSchema, array $arguments) {
                    if ($formOrSchema instanceof FilamentSchema) {
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

                    if ($formOrSchema instanceof FilamentSchema) {
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
                ->mountUsing(function ($action, ?FilamentSchema $schema, ?DateClickInfo $info): void {
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
                ->mountUsing(function ($action, ?FilamentSchema $schema, ?DateClickInfo $info): void {
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
        $user = Auth::user();

        if (! $user || ! $this->isAdmin($user)) {
            return [];
        }

        return [
            $this->createAction(Booking::class, 'ctxCreateBooking')
                ->label('Create Bookings')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Create Bookings')
                ->modalWidth('4xl')
                ->form($this->getFormSchema())
                ->mountUsing(function (...$args) {
                    $formOrSchema = $args[0] ?? null;

                    try {
                        $debugArgs = array_map(function ($a) {
                            if (is_object($a)) {
                                return json_decode(json_encode($a), true);
                            }

                            return $a;
                        }, $args);

                        Log::info('BookingCalendar date-select mount args', ['args' => $debugArgs]);
                    } catch (Throwable $e) {
                        // ignore logging failures
                    }

                    if ($formOrSchema instanceof FilamentSchema) {
                        $formOrSchema->fill([]);
                    } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill([]);
                    }

                    $values = [];

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

                            $startRaw = $arguments['startStr'] ?? $arguments['start'] ?? null;
                            $endRaw = $arguments['endStr'] ?? $arguments['end'] ?? null;

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
                        return; // no recognizable args
                    }

                    if ($formOrSchema instanceof FilamentSchema) {
                        $formOrSchema->fillPartially($values, array_keys($values));

                        return;
                    }

                    if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill($values);

                        return;
                    }
                }),
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->modalHeading('View Booking')
                ->modalWidth('full')
                ->model(fn () => Booking::class)
                ->record(fn () => Booking::with('items')->find($this->recordId))
                ->schema(fn (FilamentSchema $schema) => $this->getFormSchemaForModel($schema, $this->getModel()))
                ->mountUsing(function ($form) {
                    $record = Booking::with('items')->find($this->recordId);
                    if (! $record) {
                        logger()->warning('BookingCalendarWidget: view mountUsing found no record', ['recordId' => $this->recordId]);

                        return;
                    }

                    $data = $record->toArray();
                    $data['service_date'] = $record->service_date?->format('Y-m-d') ?? ($data['service_date'] ?? null);

                    $form->fill($data);
                    $form->disabled();
                }),

            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->modalHeading('Edit Booking')
                ->modalSubmitActionLabel('Save')
                ->modalWidth('full')
                ->model(fn () => Booking::class)
                ->record(fn () => $this->record)
                ->schema(fn (FilamentSchema $schema) => $this->getFormSchemaForModel($schema, $this->getModel()))
                ->mountUsing(function ($form) {
                    if (! $this->record) {
                        logger()->warning('BookingCalendarWidget: edit mountUsing found no record', ['recordId' => $this->recordId]);

                        return;
                    }

                    $data = $this->record->toArray();
                    $data['service_date'] = $this->record->service_date?->format('Y-m-d') ?? ($data['service_date'] ?? null);

                    $form->fill($data);
                })
                ->action(function (array $data) {
                    $record = $this->record;
                    if (! $record) {
                        logger()->warning('BookingCalendarWidget: edit action found no record', ['recordId' => $this->recordId]);

                        return;
                    }

                    $data = $this->normalizeBookingFormData($data);
                    logger()->debug('booking.edit.using', $data);
                    $items = $data['items'] ?? [];
                    unset($data['items']);

                    $record->update($data);
                    $this->syncBookingItems($record, $items);
                    $this->dispatch('refresh-calendar');
                    Notification::make()
                        ->title('Booking updated successfully')
                        ->success()
                        ->send();
                }),

            DeleteAction::make('delete')
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Booking')
                ->modalDescription('Are you sure you want to delete this booking?')
                ->visible(fn () => $this->recordId !== null)
                ->action(function () {
                    $record = Booking::with('items')->find($this->recordId);
                    if ($record) {
                        $record->delete();
                        $this->dispatch('refresh-calendar');
                    }
                })
                ->successNotificationTitle('Booking deleted successfully'),
        ];
    }
}
