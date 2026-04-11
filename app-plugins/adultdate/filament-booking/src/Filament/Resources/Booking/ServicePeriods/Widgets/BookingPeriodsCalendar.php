<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Widgets;

use Adultdate\FilamentBooking\Attributes\CalendarEventContent;
use Adultdate\FilamentBooking\Concerns\CanRefreshCalendar;
use Adultdate\FilamentBooking\Concerns\HasOptions;
use Adultdate\FilamentBooking\Concerns\HasSchema;
use Adultdate\FilamentBooking\Concerns\InteractsWithCalendar;
use Adultdate\FilamentBooking\Concerns\InteractsWithEventRecord;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRecords;
use Adultdate\FilamentBooking\Filament\Widgets\SimpleCalendarWidget;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\BookingLocation;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\Booking\Service;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventClickInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use App\Models\Admin;
use App\Models\User;
use App\UserRole;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionObject;
use stdClass;

class BookingPeriodsCalendar extends SimpleCalendarWidget implements HasCalendar
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

    public ?array $calendarData = null;

    protected $settings;

    // protected bool $dateClickEnabled = true;

    protected static ?int $sort = -1;

    public function getHeading(): string
    {
        return 'Calendar';
    }

    public function getFooterActions(): array
    {
        return [
            Action::make('create')
                ->requiresConfirmation(true)
                ->action(function (array $arguments) {
                    // TODO: Implement create action
                }),
        ];
    }

    public function getModelAlt(): string
    {
        return DailyLocation::class;
    }

    public function getModelPeriod(): string
    {
        return BookingServicePeriod::class;
    }

    public function getModel(): string
    {
        return $this->model instanceof Model ? $this->model : Booking::class;
    }

    public function getEventModel(): string
    {
        return $this->model instanceof Model ? $this->getModel() : Booking::class;
    }

    public function getEventRecord(): ?Model
    {
        return $this->record instanceof Model ? $this->record : null;
    }

    public function config(): array
    {
        $this->settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $this->settings?->opening_hour_start?->format('H:i:s') ?? '07:00:00';
        $openingEnd = $this->settings?->opening_hour_end?->format('H:i:s') ?? '21:00:00';

        return [
            'view' => 'dayGridMonth',
            'timeZone' => 'UTC',
            // Start week on Monday (0 = Sunday, 1 = Monday)
            'firstDay' => 1,
            'dayHeaderFormat' => [
                'weekday' => 'short',
                'day' => 'numeric',
            ],
            'headerToolbar' => [
                'start' => 'prev,today,next',
                'center' => 'title',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'nowIndicator' => true,
            'selectable' => true,
            'dateClick' => true,
            'eventClick' => true,
            'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
            'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
                    'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
                ],
                'timeGridWeek' => [
                    'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
                    'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
                ],
                'timeGridMonth' => [
                    'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
                    'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
                ],
            ],
        ];
    }

    public function adminAction(): Action
    {
        return Action::make('admin')
            ->label('Admin Actions')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading('Skapa booking & hantera schema')
            ->modalDescription('Choose what to create')
            ->modalWidth('sm')
            ->mountUsing(function (array $arguments) {
                $this->calendarData = $arguments['data'];
            })
            ->modalFooterActions([

                Action::make('createBooking')
                    ->label('Bokning')
                    ->color('success')
                    ->icon('heroicon-o-calendar-days')
                    ->action(function () {
                        $startDate = Carbon::parse($this->calendarData['start']);
                        $startVal = $this->calendarData['start_val'];
                        $endVal = $this->calendarData['end_val'];
                        $dateVal = $this->calendarData['date_val'];
                        $timeStamp = time();
                        $dateStamp = date('dmY', $timeStamp);
                        $bookingNumber = Str::upper(Auth::user()->name).$timeStamp;
                        if ($this->calendarData['allDay']) {
                            $startTime = '00:00';
                            $endTime = '23:59';
                        } else {
                            $startTime = Carbon::parse($this->calendarData['start_val'])->format('H:i');
                            $endTime = Carbon::parse($this->calendarData['end_val'])->format('H:i');
                        }
                        if ($endTime === $startTime) {
                            $startDate = Carbon::parse($dateVal);
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = ['number' => $bookingNumber, 'notes' => '', 'service_user_id' => null, 'booking_client_id' => null, ...($this->calendarData ?? []),
                            'start' => $startTime, 'end' => $endTime, 'service_date' => $startDate->format('Y-m-d'), 'start_time' => $startTime, 'end_time' => $endTime, 'start_val' => $startVal, 'end_val' => $endVal, 'date_val' => $dateVal];
                        logger()->info('BookingCalendarWidget: B BOOK DATA', $data);
                        $this->replaceMountedAction('create', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

                Action::make('createLocation')
                    ->label('Schema')
                    ->color('primary')
                    ->icon('heroicon-o-map-pin')
                    ->action(function () {
                        $startDate = Carbon::parse($this->calendarData['start']);
                        $startVal = $this->calendarData['start_val'];
                        $endVal = $this->calendarData['end_val'];
                        $dateVal = $this->calendarData['date_val'];
                        if ($this->calendarData['allDay']) {
                            $startTime = '00:00';
                            $endTime = '23:59';
                        } else {
                            $startTime = Carbon::parse($this->calendarData['start'])->format('H:i');
                            $endTime = Carbon::parse($this->calendarData['end'])->format('H:i');
                        }
                        if ($endTime === $startTime) {
                            $startDate = Carbon::parse($dateVal);
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = [
                            'date' => Carbon::parse($startDate)->format('Y-m-d'),
                            'start' => $startTime,
                            'end' => $endTime,
                            'service_date' => Carbon::parse($startDate)->format('Y-m-d'),
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'start_val' => $startVal,
                            'end_val' => $endVal,
                            'date_val' => $dateVal,
                        ];
                        logger()->info('BookingCalendarWidget: LOCATION DATA', $data);
                        $this->replaceMountedAction('createDailyLocation', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

                Action::make('createBlockPeriod')
                    ->label('Blocka')
                    ->color('danger')
                    ->icon('heroicon-o-clock')
                    ->action(function () {
                        $startDate = Carbon::parse($this->calendarData['start']);
                        $startTime = Carbon::parse($this->calendarData['start'])->format('H:i');
                        $endTime = Carbon::parse($this->calendarData['end'])->format('H:i');
                        $startVal = $this->calendarData['start_val'];
                        $endVal = $this->calendarData['end_val'];
                        $dateVal = $this->calendarData['date_val'];
                        if ($endTime === $startTime) {
                            $startDate = Carbon::parse($dateVal);
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = [
                            'date' => $startDate->format('Y-m-d'),
                            'start' => $startTime,
                            'end' => $endTime,
                            'service_date' => $startDate,
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
                    }),

                //   Action::make('close')
                //   ->label('')
                //   ->color('gray')
                //   ->icon('heroicon-o-x-circle')
                //   ->close(true)
                //   ->action(function () { }),

            ]);
    }

    public function createDailyLocationAction(): Action
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

    public function createServicePeriodAction(): Action
    {
        return Action::make('createServicePeriod')
            ->label('Create Service Period')
            ->icon('heroicon-o-map-pin')
            ->color('primary')
            ->modalHeading('Create Service Period')
            ->modalWidth('md')
            ->model(BookingServicePeriod::class)
            ->schema($this->getFormPeriod())
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];

                return [
                    'service_date' => $data['date_val'] ?? $data['service_date'] ?? $data['date'],
                    'service_user_id' => $data['service_user_id'] ?? null,
                    'start_time' => $data['start_val'] ?? $data['start_time'] ?? $data['start'],
                    'end_time' => $data['end_val'] ?? $data['end_time'] ?? $data['end'],
                    'created_by' => Auth::id(),
                    'service_location' => $data['service_location'] ?? '',
                    'period_type' => $data['period_type'] ?? 'unavailable',
                ];
            })
            ->action(function (array $data) {
                $data['created_by'] = Auth::id();
                BookingServicePeriod::updateOrCreate(
                    [
                        'service_date' => $data['service_date'],
                        'service_user_id' => $data['service_user_id'],
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                    ],
                    $data
                );
                $this->refreshRecords();
                Notification::make()
                    ->title('Period saved successfully')
                    ->success()
                    ->send();
            });
    }

    public function createBookingAction(): Action
    {
        return Action::make('create')
            ->label('Create Booking')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->modalHeading('Create Booking')
            ->modalWidth('lg')
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];

                return [
                    'number' => $this->generateNumber(),
                    'service_date' => $data['service_date'] ?? now()->format('Y-m-d'),
                    'service_user_id' => $data['service_user_id'] ?? null,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'status' => BookingStatus::Booked->value,
                ];
            })
            ->schema($this->getFormSchema())
            ->action(function (array $data) {
                $booking = Booking::create($data);

                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (isset($item['booking_service_id'])) {
                            $booking->items()->create([
                                'booking_service_id' => $item['booking_service_id'],
                                'qty' => $item['qty'] ?? 1,
                                'unit_price' => $item['unit_price'] ?? 0,
                            ]);
                        }
                    }
                }

                $booking->updateTotalPrice();
                $this->refreshRecords();
                Notification::make()
                    ->title('Booking created successfully')
                    ->success()
                    ->send();
            });
    }

    public function manageBlockAction(): Action
    {
        $widget = $this;

        return Action::make('manageBlock')
            ->label('Manage Block')
            ->icon('heroicon-o-cog')
            ->color('gray')
            ->modalWidth('sm')
            ->modalHeading('Manage Service Period')
            ->modalDescription('Choose an action for this block period')
            ->modalFooterActions([
                Action::make('edit')
                    ->label('Edit')
                    ->color('primary')
                    ->icon('heroicon-o-pencil')
                    ->cancelParentActions()
                    ->action(function () use ($widget) {
                        $widget->mountAction('editBlock');
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Service Period')
                    ->modalDescription('Are you sure you want to delete this service period? This action cannot be undone.')
                    ->action(function () use ($widget) {
                        $widget->record->delete();
                        $widget->refreshRecords();
                        Notification::make()
                            ->title('Service period deleted successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function optionsAction(): Action
    {
        return Action::make('options')
            ->label('Admin Actions')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading('Edit booking')
            ->modalDescription('')
            ->modalWidth('sm')
            ->model(Booking::class)
            ->mountUsing(function (array $arguments) {
                $this->calendarData = $arguments['data'];
            })
            ->modalFooterActions([

                Action::make('view')
                    ->label('')
                    ->color('gray')
                    ->icon('heroicon-o-eye')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('view', []);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),
                Action::make('confirm')
                    ->label('')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('confirmBooking', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),
                Action::make('edit')
                    ->label('Changes')
                    ->color('warning')
                    ->icon('heroicon-o-calendar')
                    ->requiresConfirmation(false)
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('edit', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

                Action::make('delete')
                    ->label(' ')
                    ->color('danger')
                    ->requiresConfirmation(true)
                    ->icon('heroicon-o-trash')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('delete', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

                Action::make('cancel')
                    ->label('')
                    ->color('gray')
                    ->close(true)
                    ->icon('heroicon-o-arrow-down-circle')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),

            ]);
    }

    public function getFormPeriod(): array
    {
        return [
            Select::make('service_user_id')
                ->label('Service User')
                ->relationship('serviceUser', 'name')
                ->required(),
            TextInput::make('service_location')
                ->label('Location')
                ->hidden()
                ->required(),
            DatePicker::make('service_date')
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
                ->hidden()
                ->required(),
        ];
    }

    public function getFormLocation(): array
    {
        return [

            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->native(false),
            Select::make('service_user_id')
                ->label('Service User')
                ->relationship('serviceUser', 'name')
                ->searchable()
                ->preload()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $date = $get('date');
                    if ($date && $state) {
                        $existingLocation = DailyLocation::where('date', $date)
                            ->where('service_user_id', $state)
                            ->value('location');
                        if ($existingLocation) {
                            $set('location', $existingLocation);
                        }
                    }
                })
                ->required(),
            TextInput::make('location')->required(),
            Hidden::make('created_by'),
        ];
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

    public function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        $blockingPeriods = BookingServicePeriod::query()
            ->where('period_type', '=', 'unavailable')
            ->get();

        $blockingEvents = $blockingPeriods->map(fn (BookingServicePeriod $blockingPeriod) => $blockingPeriod->toCalendarEvent())->toArray();

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
                'number' => 0,
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

        return collect(array_merge($bookingEvents));
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

    public function getDateSelectContextMenuActions(): array
    {
        return [
            $this->adminAction(),
        ];
    }

    public function mount(): void
    {
        $this->eventClickEnabled = true;
        //    $this->dateClickEnabled = true;
        $this->eventDragEnabled = true;
        $this->eventResizeEnabled = true;
        $this->dateSelectEnabled = true;
    }

    protected function getEloquentQuery(string $model): Builder
    {
        return $model::query();
    }

    protected function onDateClick(DateClickInfo $info): void
    {
        $startDate = $info->date;

        $this->mountAction('create', [
            'service_date' => $startDate->format('Y-m-d'),
        ]);
    }

    protected function onDateSelectLegacy(DateSelectInfo $info): void
    {
        $allDay = $info->allDay;

        logger()->info('BookingCalendarWidget CALENDAR WAS CLICKED', [
            'start' => $info->start,
            'end' => $info->end,
            'allDay' => $allDay,
            'view' => $info->view,
        ]);

        $timezone = config('app.timezone');
        $startDate = $info->start;

        $startVal = $info->start->toISOString();
        $endVal = $info->end ? $info->end->toISOString() : null;
        $dateVal = $startDate;

        $startTime = $startVal;
        $endTime = $endVal;

        if ($allDay) {
            logger()->info('BookingCalendarWidget: ALL-DAY CLICK DETECTED!');

            $this->mountAction('createDailyLocation', [
                'date' => $startDate->format('Y-m-d'),
            ]);

            return;
        }

        $data = $this->getDefaultFormData([
            'service_date' => $startDate->format('Y-m-d'),
        ]);

        if (! $allDay && $startDate->format('H:i:s') !== '00:00:00') {
            $data['start_time'] = $startDate->format('H:i');

            if ($info->end) {
                $endDate = $info->end;
                if ($endDate->format('H:i:s') !== '00:00:00') {
                    $data['end_time'] = $endDate->format('H:i');
                }
            }
        }
        if ($allDay) {
            $startTime = '00:00';
            $endTime = '23:59';
            $endDate = $info->end;
        }

        $data = [
            ...$data,
            'start' => $startTime,
            'end' => $endTime,
            'allDay' => $allDay,
            'view' => $info->view,
            'resource' => null,
            'date' => $startDate->format('Y-m-d'),
            'service_date' => $startDate->format('Y-m-d'),
            'timezone' => $timezone,
            'start_val' => $startVal,
            'end_val' => $endVal,
            'date_val' => $dateVal,
        ];

        $this->mountAction('admin', ['data' => $data]);
        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        logger()->info('xxx: EVENT zzz PAYLOAD', ['event' => $info->event]);
        //    logger()->info('BookingCalendarWidget: EVENT CLICK PAYLOAD', ['title' => $info->event->getTitle()]);

        if ($info->event->getTitle() === 'ⓘ upptagen') {

            $extended = method_exists($info->event, 'getExtendedProps') ? $info->event->getExtendedProps() : [];
            $recId = $extended['booking_id'] ?? null;
            $this->model = BookingServicePeriod::class;
            $this->record = $recId ? $this->resolveRecord($recId) : null;
            $payload = $this->record->toArray();
            $user = Auth::user();
            $canEdit = Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin';
            $action = $canEdit ? 'edit' : '';
            $this->mountAction($action, [
                'type' => 'click',
                'event' => $info->event,
                'data' => $payload,
            ]);
        }
        $isAllDay = false;
        $event = $info->event;

        if (is_object($event)) {
            if (method_exists($event, 'getAllDay')) {
                $isAllDay = $event->getAllDay() === true;
            } elseif (method_exists($event, 'getExtendedProps')) {
                $extended = $event->getExtendedProps();
                $isAllDay = isset($extended['allDay']) && $extended['allDay'] === true;
            } elseif ($event instanceof stdClass) {
                //  $isAllDay = isset($event->allDay) && $event->allDay === true;
            } elseif (property_exists($event, 'allDay')) {
                // Safely attempt to read the property only if it's publicly accessible,
                // otherwise fall back to known getter-like methods or extended props.
                try {
                    $ref = new ReflectionObject($event);
                    if ($ref->hasProperty('allDay')) {
                        $prop = $ref->getProperty('allDay');
                        if ($prop->isPublic()) {

                            //   $isAllDay = $event->allDay === true;
                        } else {
                            // Avoid calling a potentially undefined isAllDay method;
                            // prefer existing getters or extended props instead.
                            if (method_exists($event, 'getAllDay')) {
                                $isAllDay = $event->getAllDay() === true;
                            } elseif (method_exists($event, 'getExtendedProps')) {
                                $extended = $event->getExtendedProps();
                                $isAllDay = isset($extended['allDay']) && $extended['allDay'] === true;
                            } else {
                                $isAllDay = false;
                            }
                        }
                    } else {
                        $isAllDay = false;
                    }
                } catch (ReflectionException $e) {
                    $isAllDay = false;
                }
            }
        } elseif (is_array($event)) {
            $isAllDay = isset($event['allDay']) && $event['allDay'] === true;
        }

        if ($isAllDay) {

            $extended = method_exists($info->event, 'getExtendedProps') ? $info->event->getExtendedProps() : [];
            $recId = $extended['daily_location_id'] ?? null;
            $this->model = DailyLocation::class;
            $this->record = $this->resolveRecord($recId);
            $this->eventRecord = $this->record;
            $this->recordId = $this->record->id;
            $payload = $this->record->toArray();
            $user = Auth::user();
            $canEdit = Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin';
            $action = $canEdit ? 'edit' : '';
            $this->mountAction($action, [
                'type' => 'click',
                'event' => $info->event,
                'data' => $payload,
            ]);
        }

        // Regular booking events (not the blocked or all-day ones)
        if ($info->event->getTitle() !== 'ⓘ upptagen' && $info->event->getAllDay() === false) {
            $recId = $info->record?->getKey();
            $this->model = Booking::class;
            $this->record = $this->resolveRecord($recId);
            $this->eventRecord = $this->record;
            $this->record?->load('items');
            $this->recordId = $this->record?->id;
            $payload = $this->record?->toArray() ?? [];
            $payload['service_date'] = $this->record->service_date?->format('Y-m-d') ?? ($payload['service_date'] ?? null);
            $booking = $this->record;
            $user = Auth::user();
            $canEdit = $booking && ($user->id === $booking->booking_user_id || $user->role === 'admin' || $user->role === 'super_admin');
            $action = $canEdit ? 'options' : '';
            $this->mountAction($action, [
                'data' => $payload,
            ]);
        }
    }

    protected function getDateClickContextMenuActions(): array
    {
        $user = Auth::user();

        if (! $user || ! $this->isAdmin($user)) {
            return [];
        }

        return [
            $this->adminAction(),
        ];
    }

    protected function isAdmin(Authenticatable $user): bool
    {
        if ($user instanceof Admin) {
            return true; // Admins can perform admin actions
        }

        if ($user instanceof User) {
            return $user->role === UserRole::ADMIN || $user->role === UserRole::SUPER_ADMIN;
        }

        return false;
    }

    protected function getDefaultFormData(array $seed = []): array
    {
        return array_replace([
            'number' => $this->generateNumber(),
            'booking_client_id' => null,
            'service_id' => null,
            'booking_user_id' => null,
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

    protected function generateNumber(): string
    {
        return Str::upper(auth()->user()->name).'-'.Str::upper(filament()->getTenant()?->slug).'-'.now()->timestamp;
    }

    #[CalendarEventContent(model: Booking::class)]
    protected function bookingEventContent(): string
    {
        // Pass the booking model to the blade so the view can access all booking data
        return view('adultdate/filament-booking::components.calendar.booking', [
            'title' => $this->settings['title'] ?? 'Booking',
        ])->render();
    }
}
