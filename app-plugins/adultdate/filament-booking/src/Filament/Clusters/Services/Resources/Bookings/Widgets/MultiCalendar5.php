<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets;

use Adultdate\FilamentBooking\Concerns\CanRefreshCalendar;
use Adultdate\FilamentBooking\Concerns\HasOptions;
use Adultdate\FilamentBooking\Concerns\HasSchema;
use Adultdate\FilamentBooking\Concerns\InteractsWithCalendar;
use Adultdate\FilamentBooking\Concerns\InteractsWithEventRecord;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Schemas\BookingForm;
use Adultdate\FilamentBooking\Filament\Widgets\BookingFullCalendarWidget;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRecords;
use Adultdate\FilamentBooking\Jobs\SyncBookingToGoogleCalendar;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\Booking\Service;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\EventResizeInfo;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use App\Models\Admin;
use App\Models\BookingCalendar;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Auth\Authenticatable;
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

final class MultiCalendar5 extends BookingFullCalendarWidget implements HasCalendar
{
    use CanBeConfigured, CanRefreshCalendar, HasOptions, HasSchema, InteractsWithCalendar, InteractsWithEventRecord, InteractsWithEvents, InteractsWithPageFilters, InteractsWithRawJS, InteractsWithRecords {
        // Prefer the contract-compatible refreshRecords (chainable) from CanRefreshCalendar
        CanRefreshCalendar::refreshRecords insteadof InteractsWithEvents;

        // Keep the frontend-only refresh available under an alias if needed
        InteractsWithEvents::refreshRecords as refreshRecordsFrontend;

        // Resolve __get collision: prefer InteractsWithPageFilters for pageFilters access
        InteractsWithPageFilters::__get insteadof InteractsWithCalendar;

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

    public ?array $lastMountedData = null;

    public Model|int|string|null $record;

    public ?Model $eventRecord = null;

    public Model|string|null $model = null;

    public ?array $calendarData = null;

    protected $settings;

    protected $listeners = ['refreshCalendar' => 'refreshCalendar'];

    //    protected bool $eventDragEnabled = true;
    //    protected bool $eventResizeEnabled = true;
    //    protected bool $dateClickEnabled = true;
    //    protected bool $dateSelectEnabled = true;

    protected static ?int $sort = -5;

    protected string $view = 'adultdate/filament-booking::multi-fullcalendar';
    // protected int | string | array $columnSpan = 3;

    public function getHeading(): string|Htmlable
    {
        $technician = $this->selectedTechnician ? BookingCalendar::with('owner')->find($this->selectedTechnician)?->owner?->name : 'All Tekniker';

        return '#5 ◴ '.$technician;
    }

    public function getFooterActions(): array
    {
        return [
            Action::make('create')
                ->requiresConfirmation(true)
                ->action(function (array $arguments) {
                    dd('Admin action called', $arguments);
                }),
        ];
    }

    public function getHeadingActions(): array
    {
        return [
            Action::make('create')
                ->requiresConfirmation(true)
                ->action(function (array $arguments) {
                    dd('Admin action called', $arguments);
                }),
        ];
    }

    public function editServicePeriodAction(): Action
    {
        return Action::make('editServicePeriod')
            ->label('Edit Service Period')
            ->icon('heroicon-o-clock')
            ->color('primary')
            ->modalHeading('Edit Service Period')
            ->modalWidth('md')
            ->model(BookingServicePeriod::class)
            ->schema($this->getFormPeriod())
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];
                $serviceUserId = $this->getSelectedServiceUserId();

                return [
                    'service_date' => $data['service_date'] ?? null,
                    'service_user_id' => $data['service_user_id'] ?? $serviceUserId,
                    'start_time' => $data['start_time'] ?? null,
                    'end_time' => $data['end_time'] ?? null,
                    'service_location' => $data['service_location'] ?? '',
                    'period_type' => $data['period_type'] ?? 'unavailable',
                ];
            })
            ->action(function (array $data, array $arguments) {
                $id = $arguments['data']['id'] ?? null;
                if ($id) {
                    BookingServicePeriod::whereKey($id)->update($data);
                }
                $this->refreshRecords();
                Notification::make()
                    ->title('Period updated successfully')
                    ->success()
                    ->send();
            })
            ->modalSubmitActionLabel('Update')
            ->modalCancelActionLabel('Cancel')
            ->extraModalFooterActions([
                Action::make('deleteFromModal')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Service Period')
                    ->modalDescription('Are you sure? This action cannot be undone.')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function (array $arguments) {
                        $id = $arguments['data']['id'] ?? null;
                        if ($id) {
                            BookingServicePeriod::whereKey($id)->delete();
                        }
                        $this->refreshRecords();
                        Notification::make()
                            ->title('Period deleted successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function deleteServicePeriodAction(): Action
    {
        return Action::make('deleteServicePeriod')
            ->label('Delete Period')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete Service Period')
            ->modalDescription('Are you sure you want to delete this service period? This action cannot be undone.')
            ->action(function (array $arguments) {
                $id = $arguments['data']['id'] ?? null;
                if ($id) {
                    BookingServicePeriod::whereKey($id)->delete();
                }
                $this->refreshRecords();
                Notification::make()
                    ->title('Period deleted successfully')
                    ->success()
                    ->send();
            });
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

        $user = Auth::user();
        $isAdmin = $user ? $this->isAdmin($user) : false;

        return [
            'initialView' => 'timeGridWeek',
            // Start week on Monday (0 = Sunday, 1 = Monday)
            'firstDay' => 1,
            'dayCount' => 5,
            'weekends' => false,
            'dayHeaderFormat' => [
                'weekday' => 'short',
                'day' => 'numeric',
            ],
            'headerToolbar' => [
                'start' => 'prev,next',
                'center' => 'title',
                'end' => 'timeGridWeek,timeGridDay',
            ],
            'nowIndicator' => true,
            'selectable' => true,
            // Only admins/super_admins can drag/resize events
            'editable' => $isAdmin,
            'eventStartEditable' => $isAdmin,
            'eventDurationEditable' => $isAdmin,
            'dateClick' => true,
            'eventClick' => true,
            'eventDrop' => 'onEventDrop',
            'timeZone' => 'Europe/Stockholm',
            'now' => now()->setTimezone('Europe/Stockholm')->addHour()->toISOString(),
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '20:00:00',
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
                    'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
                ],
                'timeGridWeek' => [
                    'slotMinTime' => '07:00:00',
                    'slotMaxTime' => '20:00:00',
                ],
                'timeGridMonth' => [
                    'slotMinTime' => $openingStart ? $openingStart : '08:00:00',
                    'slotMaxTime' => $openingEnd ? $openingEnd : '20:00:00',
                ],
            ],
        ];
    }

    public function onDateClick(string $date, bool $allDay, ?array $view, ?array $resource): void
    {
        $startDate = Carbon::parse($date);

        $action = $this->resolveDateSelectAction($allDay, $view);

        if ($action === 'createDailyLocation') {
            $this->mountAction('createDailyLocation', [
                'date' => $startDate->format('Y-m-d'),
            ]);

            return;
        }

        $bookingCalendarId = $resource['id'] ?? $this->getSelectedCalendarId() ?? $this->getDefaultCalendarId();

        $this->mountAction('create', [
            'service_date' => $startDate->format('Y-m-d'),
            'start_time' => $startDate->format('H:i'),
            'end_time' => $startDate->copy()->addHour()->format('H:i'),
            'booking_calendar_id' => $bookingCalendarId,
            'notes' => '',
        ]);
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        $allDay = (bool) $allDay;

        logger()->info('BookingCalendarWidget CALENDAR WAS CLICKED', [
            'start' => $start,
            'end' => $end,
            'allDay' => $allDay,
            'view' => $view,
            'resource' => $resource,
        ]);

        $timezone = config('app.timezone');
        $startDate = $allDay ? Carbon::parse($start) : Carbon::parse($start, $timezone);

        $startVal = $start;
        $endVal = $end;
        $dateVal = $startDate;

        $startTime = $startVal;
        $endTime = $endVal;

        // For non-admins, even all-day selection should open Create Booking
        $action = $this->resolveDateSelectAction($allDay, $view);
        if ($action === 'createDailyLocation') {
            logger()->info('BookingCalendarWidget: ALL-DAY CLICK DETECTED!', ['dataVal' => $dateVal]);
            $this->mountAction('createDailyLocation', [
                'date' => $dateVal->format('Y-m-d'),
            ]);

            return;
        }

        $data = $this->getDefaultFormData([
            'service_date' => $startDate->format('Y-m-d'),
        ]);

        if (! $allDay && $startDate->format('H:i:s') !== '00:00:00') {
            $data['start_time'] = $startDate->format('H:i');

            if ($end) {
                $endDate = Carbon::parse($end, $timezone);
                if ($endDate->format('H:i:s') !== '00:00:00') {
                    $data['end_time'] = $endDate->format('H:i');
                }
            }
        }
        if ($allDay) {
            $startTime = '00:00';
            $endTime = '23:59';
            $endDate = Carbon::parse($end, $timezone);
        }

        $serviceUserId = $this->getSelectedServiceUserId();
        if ($resource && isset($resource['id'])) {
            $calendar = BookingCalendar::find($resource['id']);
            $serviceUserId = $calendar?->owner_id ?? $serviceUserId;
        }

        // Get booking_calendar_id based on resource, filter, or service user
        $bookingCalendarId = null;
        if ($resource && isset($resource['id'])) {
            $bookingCalendarId = $resource['id'];
        } elseif ($selectedCalendarId = $this->getSelectedCalendarId()) {
            $bookingCalendarId = $selectedCalendarId;
        } elseif ($serviceUserId) {
            // Find calendar for this service user
            $calendar = BookingCalendar::where('owner_id', $serviceUserId)->first();
            $bookingCalendarId = $calendar?->id;
        }

        $data = [
            ...$data,
            'start' => $startTime,
            'end' => $endTime,
            'allDay' => $allDay,
            'view' => $view,
            'resource' => $resource,
            'date' => $startDate->format('Y-m-d'),
            'service_date' => $startDate->format('Y-m-d'),
            'service_user_id' => $serviceUserId,
            'booking_calendar_id' => $bookingCalendarId,
            'timezone' => $timezone,
            'start_val' => $startVal,
            'end_val' => $endVal,
            'date_val' => $dateVal,
        ];

        $data['number'] = $this->generateNumber();

        if ($action === 'admin') {
            $this->mountAction('admin', ['data' => $data]);
            $newIndex = max(0, count($this->mountedActions) - 1);
            $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
        } else {
            // Open Create Booking directly for non-admins
            $data['booking_client_id'] = null;
            $data['notes'] = '';
            $this->mountAction('create', ['data' => $data]);
            $newIndex = max(0, count($this->mountedActions) - 1);
            $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
        }
    }

    /**
     * Accept raw calendar payload from the frontend and delegate to the legacy handler.
     * This prevents the container attempting to auto-resolve EventResizeInfo.
     */
    public function onEventResize(array $event, array $oldEvent, array $relatedEvents = [], array $startDelta = [], array $endDelta = []): bool
    {
        logger()->info('BookingCalendarWidget RESIZE EVENT DETECTED', [
            'event' => $event,
            'oldEvent' => $oldEvent,
            'relatedEvents' => $relatedEvents,
            'startDelta' => $startDelta,
            'endDelta' => $endDelta,
        ]);
        // Try to persist the resized times so the calendar reflects changes
        // after the request finishes instead of snapping back.

        // Resolve record from event id
        $record = null;
        if ($this->getModel()) {
            $record = $this->resolveRecord($event['id'] ?? null);
        }

        if (! $record) {
            $record = Booking::find($event['id'] ?? null);
        }

        if ($record instanceof Booking) {
            try {
                $tz = config('app.timezone');
                $start = isset($event['start']) ? Carbon::parse($event['start'], $tz) : null;
                $end = isset($event['end']) ? Carbon::parse($event['end'], $tz) : null;

                if ($record->service_date) {
                    if ($start) {
                        $record->service_date = $start->format('Y-m-d');
                        $record->start_time = $start->format('H:i');
                    }

                    if ($end) {
                        $record->end_time = $end->format('H:i');
                    }
                } else {
                    if ($start) {
                        $record->starts_at = $start;
                    }

                    if ($end) {
                        $record->ends_at = $end;
                    }
                }

                $record->save();

                Notification::make()
                    ->title('Booking duration updated')
                    ->success()
                    ->send();

                $this->refreshRecords();

                // Sync to Google Calendar and send WhatsApp
                SyncBookingToGoogleCalendar::dispatch($record, sendWhatsapp: true);

                // Return false to avoid calling FullCalendar revert() in the
                // bundle currently shipped with this plugin (which treats
                // `true` as a revert signal).
                return false;
            } catch (Throwable $e) {
                logger()->error('Error persisting resized booking', ['err' => $e->getMessage()]);
                Notification::make()
                    ->title('Failed to update booking')
                    ->danger()
                    ->send();

                return false;
            }
        }

        // Fallback: delegate to legacy handler which mounts edit modal
        $this->onEventResizeLegacy($event, $oldEvent, $relatedEvents, $startDelta, $endDelta);

        return false;
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
                        $startDate = Carbon::parse($this->calendarData['start'])->format('Y-m-d');
                        $startVal = $this->calendarData['start_val'];
                        $endVal = $this->calendarData['end_val'];
                        $dateVal = $this->calendarData['date_val'];
                        $serviceUserId = $this->calendarData['service_user_id'] ?? null;
                        $timeStamp = time();
                        $dateStamp = date('Ymd', $timeStamp);
                        $startStamp = date('Ymd', strtotime($startDate));
                        $bookingNumber = Str::upper(Auth::user()->name).$timeStamp;
                        if ($this->calendarData['allDay']) {
                            $startTime = '00:00';
                            $endTime = '23:59';
                        } else {
                            $startTime = Carbon::parse($this->calendarData['start_val'])->format('H:i');
                            $endTime = Carbon::parse($this->calendarData['end_val'])->format('H:i');
                        }
                        if ($endTime === $startTime) {
                            $startDate = Carbon::parse($dateVal)->format('Y-m-d');
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = ['number' => $bookingNumber, 'notes' => '', 'service_user_id' => $serviceUserId, 'booking_client_id' => null, 'booking_calendar_id' => $this->calendarData['booking_calendar_id'] ?? null, 'date' => $startDate->format('Y-m-d'), 'start' => $startTime, 'end' => $endTime, 'service_date' => $startDate->format('Y-m-d'), 'start_time' => $startTime, 'end_time' => $endTime, 'start_val' => $startVal, 'end_val' => $endVal, 'date_val' => $dateVal];
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
                        $startDate = Carbon::parse($this->calendarData['start'])->format('Y-m-d');
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
                            $startDate = Carbon::parse($dateVal)->format('Y-m-d');
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = ['date' => $startDate->format('Y-m-d'), 'start' => $startTime, 'end' => $endTime, 'service_date' => $startDate->format('Y-m-d'), 'start_time' => $startTime, 'end_time' => $endTime, 'start_val' => $startVal, 'end_val' => $endVal, 'date_val' => $dateVal];
                        logger()->info('BookingCalendarWidget: LOCATION DATA', $data);
                        $this->replaceMountedAction('createDailyLocation', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

                Action::make('createBlockPeriod')
                    ->label('')
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
                            $startDate = Carbon::parse($dateVal)->format('Y-m-d');
                            $startTime = Carbon::parse($startVal)->format('H:i');
                            $endTime = Carbon::parse($endVal)->format('H:i');
                        }
                        $data = ['date' => $startDate->format('Y-m-d'), 'start' => $startTime, 'end' => $endTime, 'service_date' => $startDate->format('Y-m-d'), 'start_time' => $startTime, 'end_time' => $endTime, 'start_val' => $startVal, 'end_val' => $endVal, 'date_val' => $dateVal];
                        logger()->info('BookingCalendarWidget: BLOCK PERIOD DATA', $data);
                        $this->replaceMountedAction('createServicePeriod', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),

                Action::make('close')
                    ->label('')
                    ->color('gray')
                    ->icon('heroicon-o-x-circle')
                    ->close(true)
                    ->action(function () {}),

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
                $serviceUserId = $this->getSelectedServiceUserId();

                return [
                    'date' => $data['date_val'] ?? $data['service_date'] ?? $data['date'] ?? now()->format('Y-m-d'),
                    'service_user_id' => $data['service_user_id'] ?? $serviceUserId,
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

    public function editDailyLocationAction(): Action
    {
        return Action::make('editDailyLocation')
            ->label('Edit Location')
            ->icon('heroicon-o-map-pin')
            ->color('primary')
            ->modalHeading('Edit Daily Location')
            ->modalWidth('md')
            ->model(DailyLocation::class)
            ->schema($this->getFormLocation())
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];
                $serviceUserId = $this->getSelectedServiceUserId();

                return [
                    'date' => $data['date'] ?? now()->format('Y-m-d'),
                    'service_user_id' => $data['service_user_id'] ?? $serviceUserId,
                    'location' => $data['location'] ?? null,
                    'created_by' => Auth::id(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $id = $arguments['data']['id'] ?? null;
                if ($id) {
                    DailyLocation::whereKey($id)->update($data);
                }
                $this->refreshRecords();
                Notification::make()
                    ->title('Location updated successfully')
                    ->success()
                    ->send();
            })
            ->modalSubmitActionLabel('Update')
            ->extraModalFooterActions(function (array $arguments) {
                $id = $arguments['data']['id'] ?? null;
                if (! $id) {
                    return [];
                }

                return [
                    Action::make('deleteLocation')
                        ->label('Delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function () use ($id) {
                            DailyLocation::whereKey($id)->delete();
                            $this->refreshRecords();
                            Notification::make()
                                ->title('Location deleted successfully')
                                ->success()
                                ->send();
                        }),
                ];
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
                $serviceUserId = $this->getSelectedServiceUserId();

                return [
                    'service_date' => $data['date_val'] ?? $data['service_date'] ?? $data['date'],
                    'service_user_id' => $data['service_user_id'] ?? $serviceUserId,
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

    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Create Booking')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->modalHeading('Create Booking')
            ->modalWidth('lg')
            ->model(Booking::class)
            ->fillForm(function (array $arguments) {
                $data = $arguments['data'] ?? [];
                $defaults = $this->getDefaultFormData();
                $merged = array_merge($defaults, $data);
                $user = Auth::user();
                $roleValue = $user && $user->role instanceof UnitEnum ? $user->role->value : (string) $user->role;
                $isAdmin = in_array($roleValue, ['admin', 'super', 'super_admin'], true);
                // Preserve service_user_id from data if provided (from calendar context), otherwise use current user
                if (! isset($merged['service_user_id']) || empty($merged['service_user_id'])) {
                    $merged['service_user_id'] = Auth::id();
                }

                return $merged;
            })
            ->schema($this->getFormSchema())
            ->action(function (array $data) {
                // Ensure number exists
                if (! isset($data['number']) || empty($data['number'])) {
                    $data['number'] = $this->generateNumber();
                }

                // Always set booking_calendar_id from selected or default if missing or null
                if (! isset($data['booking_calendar_id']) || empty($data['booking_calendar_id'])) {
                    $calendarId = method_exists($this, 'getSelectedCalendarId') ? $this->getSelectedCalendarId() : null;
                    if (! $calendarId && method_exists($this, 'getDefaultCalendarId')) {
                        $calendarId = $this->getDefaultCalendarId();
                    }
                    $data['booking_calendar_id'] = $calendarId;
                }

                // Extract items before creating booking (items are not a fillable field)
                $items = $data['items'] ?? [];
                unset($data['items']);

                // Build proper starts_at and ends_at from service_date + times
                if (isset($data['service_date']) && isset($data['start_time'])) {
                    $startDateTime = Carbon::parse($data['service_date'].' '.$data['start_time']);
                    $data['starts_at'] = $startDateTime->toDateTimeString();
                }
                if (isset($data['service_date']) && isset($data['end_time'])) {
                    $endDateTime = Carbon::parse($data['service_date'].' '.$data['end_time']);
                    $data['ends_at'] = $endDateTime->toDateTimeString();
                }

                logger()->info('BookingCalendarWidget: BEFORE Booking::create()', [
                    'booking_calendar_id' => $data['booking_calendar_id'] ?? 'NOT SET',
                    'full_data' => $data,
                ]);
                $booking = Booking::create($data);
                logger()->info('BookingCalendarWidget: AFTER Booking::create()', [
                    'booking_id' => $booking->id,
                    'booking_calendar_id_in_db' => $booking->booking_calendar_id,
                ]);

                if (is_array($items)) {
                    foreach ($items as $item) {
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

                // The Observer will handle Google Calendar sync and WhatsApp notification automatically

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
            ->modalHeading(function (array $arguments): string {
                $data = $arguments['data'] ?? [];

                $startRaw = $data['start_time'] ?? data_get($data, 'start') ?? ($data['start_val'] ?? null);
                $endRaw = $data['end_time'] ?? data_get($data, 'end') ?? ($data['end_val'] ?? null);

                $fmt = function ($raw) {
                    if (! $raw) {
                        return null;
                    }
                    try {
                        return Carbon::parse($raw)->format('H:i');
                    } catch (Throwable $e) {
                        // Fallback: take first 5 characters if looks like HH:MM
                        return preg_match('/^(\d{2}:\d{2})/', (string) $raw, $m) ? $m[1] : (string) $raw;
                    }
                };

                $start = $fmt($startRaw);
                $end = $fmt($endRaw);

                $bookingUser = data_get($data, 'booking_user') ?: ($data['booking_user_name'] ?? data_get($data, 'extendedProps.booking_user'));

                $serviceUser = data_get($data, 'service_user') ?: ($data['service_user_name'] ?? data_get($data, 'extendedProps.service_user'));
                if (! $serviceUser && ! empty($data['service_user_id'])) {
                    try {
                        $svcUser = User::find($data['service_user_id']);
                        $serviceUser = $svcUser?->name ?: $serviceUser;
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                $prefix = $serviceUser ? "{$serviceUser}" : '';

                if ($start && $end) {
                    return $bookingUser ? "{$prefix} @ {$start}-{$end} # {$bookingUser}" : "{$prefix}{$start} - {$end}";
                }

                if ($start) {
                    return $bookingUser ? "{$prefix}{$start} — {$bookingUser}" : ($prefix ? mb_trim($prefix) : $start);
                }

                return 'Manage Update Booking';
            })
            ->modalContent(function (array $arguments) {
                $data = $arguments['data'] ?? [];

                $clientObj = $data['client'] ?? data_get($data, 'extendedProps.client') ?? null;
                $clientName = is_array($clientObj) ? ($clientObj['name'] ?? null) : ($data['client_name'] ?? data_get($data, 'extendedProps.client_name') ?? null);
                $phone = is_array($clientObj) ? ($clientObj['phone'] ?? '') : ($data['phone'] ?? data_get($data, 'extendedProps.phone') ?? '');
                $street = is_array($clientObj) ? ($clientObj['address'] ?? '') : ($data['client_address'] ?? $data['address'] ?? data_get($data, 'extendedProps.client_address') ?? '');
                $city = is_array($clientObj) ? ($clientObj['city'] ?? '') : ($data['client_city'] ?? $data['city'] ?? data_get($data, 'extendedProps.client_city') ?? '');

                // Build services list from payload items or fallbacks
                $items = $data['items'] ?? data_get($data, 'items', []);
                $services = [];
                if (is_array($items)) {
                    foreach ($items as $it) {
                        if (! is_array($it)) {
                            continue;
                        }
                        $name = $it['booking_service_name'] ?? $it['service_name'] ?? $it['name'] ?? null;
                        if (! $name && isset($it['booking_service_id'])) {
                            $svc = Service::find($it['booking_service_id']);
                            $name = $svc?->name;
                        }
                        $qty = isset($it['qty']) ? (int) $it['qty'] : (isset($it['quantity']) ? (int) $it['quantity'] : 1);
                        $unit = isset($it['unit_price']) ? (float) $it['unit_price'] : (isset($it['price']) ? (float) $it['price'] : 0.0);
                        $currency = $data['currency'] ?? ($data['currency_code'] ?? ($data['currency'] ?? 'SEK'));
                        if ($name) {
                            $total = $qty * $unit;
                            $formatted = $unit > 0 ? sprintf('%s x%d — %s %s', $name, $qty, number_format($total, 0, ',', ' '), $currency) : sprintf('%s x%d', $name, $qty);
                            $services[] = $formatted;
                        }
                    }
                }

                // Fallback: single service name on payload
                if (empty($services) && ! empty($data['service_name'])) {
                    $services[] = $data['service_name'];
                }

                $services = array_values(array_unique(array_filter(array_map('trim', $services))));

                return view('filament-booking.modal.booking-description', [
                    'client_name' => $clientName,
                    'phone' => $phone,
                    'street' => $street,
                    'city' => $city,
                    'services' => $services,
                ]);
            })
            ->modalWidth('sm')
            ->model(Booking::class)
            ->mountUsing(function (array $arguments) {
                $this->calendarData = $arguments['data'];
            })
            ->modalFooterActions([

                Action::make('confirm')
                    ->label('Confirm')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('confirmBooking', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),
                Action::make('edit')
                    ->label('Update')
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
                    ->label('Radera')
                    ->color('danger')
                    ->requiresConfirmation(true)
                    ->icon('heroicon-o-trash')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('delete', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
                    }),

            ]);
    }

    public function locationOptionsAction(): Action
    {
        return Action::make('locationOptions')
            ->label('Location options')
            ->icon('heroicon-o-map-pin')
            ->color('gray')
            ->modalHeading('Edit location')
            ->modalWidth('sm')
            ->model(DailyLocation::class)
            ->mountUsing(function (array $arguments) {
                $this->calendarData = $arguments['data'];
            })
            ->modalFooterActions([
                Action::make('edit')
                    ->label('Edit')
                    ->color('primary')
                    ->icon('heroicon-o-pencil-square')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('editDailyLocation', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $id = $data['id'] ?? null;
                        if ($id) {
                            DailyLocation::whereKey($id)->delete();
                            $this->refreshRecords();
                        }
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => 0]);
                    }),
                Action::make('close')
                    ->label('Close')
                    ->color('gray')
                    ->close(true)
                    ->icon('heroicon-o-x-circle'),
            ]);
    }

    public function periodOptionsAction(): Action
    {
        return Action::make('periodOptions')
            ->label('Period options')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->modalHeading('Edit period')
            ->modalWidth('sm')
            ->model(BookingServicePeriod::class)
            ->mountUsing(function (array $arguments) {
                $this->calendarData = $arguments['data'];
            })
            ->modalFooterActions([
                Action::make('edit')
                    ->label('Edit')
                    ->color('primary')
                    ->icon('heroicon-o-pencil-square')
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $this->replaceMountedAction('editServicePeriod', ['data' => $data]);
                        $newIndex = max(0, count($this->mountedActions) - 1);
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => $newIndex]);
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function () {
                        $data = $arguments['data'] ?? [];
                        $id = $data['id'] ?? null;
                        if ($id) {
                            BookingServicePeriod::whereKey($id)->delete();
                            $this->refreshRecords();
                        }
                        $this->dispatch('sync-action-modals', ['id' => $this->getId(), 'newActionNestingIndex' => 0]);
                    }),
                Action::make('close')
                    ->label('Close')
                    ->color('gray')
                    ->close(true)
                    ->icon('heroicon-o-x-circle'),
            ]);
    }

    public function onEventClick($event): void
    {
        logger()->info('zzz: onEventClick', ['events' => $event]);

        $title = $event['title'] ?? null;
        $start = $event['start'] ?? null;
        $end = $event['end'] ?? null;
        $view = $event['view'] ?? null;
        $resource = $event['resource'] ?? null;
        // logger()->info('zzz: onEventClick', ['events' => $start . ' ' . $end . ' ' . ($allDay ? 'allDay' : 'notAllDay')]);

        $allDay = (bool) ($event['allDay']);

        logger()->info('BookingCalendarWidget CALENDAR WAS CLICKED', [
            'title' => $title,
            'start' => $start,
            'end' => $end,
            'allDay' => $allDay,
            'view' => $view,
            'resource' => $resource,
        ]);

        $type = data_get($event, 'extendedProps.type', 'booking');

        logger()->info('BookingCalendarWidget: Event type detected', ['type' => $type]);

        switch ($type) {
            case 'blocking':
                $recId = $event['extendedProps']['booking_id'] ?? null;
                logger()->info('BookingCalendarWidget: Blocking period click', ['recId' => $recId]);

                if (! $recId) {
                    logger()->error('BookingCalendarWidget: No record ID found for blocking period');

                    return;
                }

                try {
                    $this->model = BookingServicePeriod::class;

                    // Directly query the record instead of using resolveRecord
                    $this->record = BookingServicePeriod::find($recId);

                    if (! $this->record) {
                        logger()->error('BookingCalendarWidget: Record not found', ['id' => $recId]);
                        Notification::make()
                            ->title('Period not found')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($this->record instanceof Model) {
                        $this->eventRecord = $this->record;
                        $this->recordId = $this->record->id;
                        $payload = $this->record->toArray();
                    } else {
                        logger()->error('BookingCalendarWidget: Record is not a valid Model instance', ['record' => $this->record]);
                        Notification::make()
                            ->title('Invalid record type')
                            ->danger()
                            ->send();

                        return;
                    }

                    $user = Auth::user();
                    if (! $user) {
                        logger()->error('BookingCalendarWidget: No authenticated user');
                        Notification::make()
                            ->title('Authentication required')
                            ->danger()
                            ->send();

                        return;
                    }

                    $userRole = $user->role;
                    if ($userRole instanceof UnitEnum) {
                        $roleValue = $userRole->value;
                    } else {
                        $roleValue = (string) $userRole;
                    }

                    $canEdit = in_array($roleValue, ['admin', 'super', 'super_admin'], true);
                    logger()->info('BookingCalendarWidget: Mounting editServicePeriod for blocking', [
                        'canEdit' => $canEdit,
                        'userRole' => $roleValue,
                        'recordId' => $this->record->id,
                    ]);

                    if ($canEdit) {
                        $this->mountAction('editServicePeriod', [
                            'data' => $payload,
                        ]);
                        $this->lastMountedData = $payload;
                    } else {
                        logger()->info('BookingCalendarWidget: User does not have permission to edit blocking period', [
                            'userRole' => $roleValue,
                        ]);
                        Notification::make()
                            ->title('Permission denied')
                            ->body('You do not have permission to edit this period')
                            ->warning()
                            ->send();
                    }
                } catch (Exception $e) {
                    logger()->error('BookingCalendarWidget: Exception in blocking case', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    Notification::make()
                        ->title('Error loading period')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
                break;

            case 'location':
                if ($allDay) {
                    $recId = $event['extendedProps']['daily_location_id'] ?? null;
                    try {
                        $this->model = DailyLocation::class;
                        $this->record = DailyLocation::find($recId);
                        if (! $this->record) {
                            throw new Exception("Location record not found: {$recId}");
                        }
                        if ($this->record instanceof Model) {
                            $this->eventRecord = $this->record;
                            $this->recordId = $this->record->id;
                            $payload = $this->record->toArray();
                        }
                        $user = Auth::user();
                        if (! $user) {
                            logger()->error('BookingCalendarWidget: No authenticated user for location');
                            Notification::make()
                                ->title('Authentication required')
                                ->danger()
                                ->send();

                            return;
                        }

                        $userRole = $user->role;
                        if ($userRole instanceof UnitEnum) {
                            $roleValue = $userRole->value;
                        } else {
                            $roleValue = (string) $userRole;
                        }

                        $canEdit = in_array($roleValue, ['admin', 'super', 'super_admin'], true);

                        // For bookings we mount the booking `options` action below
                        // based on the user's permissions; do not mount the
                        // service-period editor here (copy/paste leftover).

                        $userRole = $user->role;
                        if ($userRole instanceof UnitEnum) {
                            $roleValue = $userRole->value;
                        } else {
                            $roleValue = (string) $userRole;
                        }

                        $canEdit = in_array($roleValue, ['admin', 'super', 'super_admin'], true);
                        Log::info('BookingCalendarWidget: Location click', [
                            'canEdit' => $canEdit,
                            'userRole' => $roleValue,
                            'recordId' => $recId,
                        ]);
                        $action = $canEdit ? 'editDailyLocation' : '';
                        if ($action) {
                            $this->mountAction($action, [
                                'data' => $payload,
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('BookingCalendarWidget: Location error', [
                            'error' => $e->getMessage(),
                            'recId' => $recId,
                        ]);
                        Notification::make()
                            ->title('Error loading location')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }
                break;

            case 'booking':
            default:
                if (! $allDay) {
                    $recId = $event['id'] ?? null;
                    try {
                        $this->model = Booking::class;
                        $this->record = Booking::find($recId);
                        if (! $this->record) {
                            throw new Exception("Booking record not found: {$recId}");
                        }
                        if ($this->record instanceof Model) {
                            $this->eventRecord = $this->record;
                            $this->record->load(['items', 'client']);
                            $this->recordId = $this->record->id;
                            $payload = $this->record->toArray();

                            // Ensure client details are present for modalDescription
                            $payload['client_name'] = $this->record->client?->name ?? ($payload['client_name'] ?? null);
                            $payload['address'] = $this->record->client?->address ?? ($payload['address'] ?? null);
                            $payload['phone'] = $this->record->client?->phone ?? ($payload['phone'] ?? null);
                        }
                        $payload['service_date'] = $this->record->service_date?->format('Y-m-d') ?? ($payload['service_date'] ?? null);
                        // Ensure booking user name is available for the modal header
                        $payload['booking_user_name'] = $this->record->bookingUser?->name ?? ($payload['booking_user_name'] ?? null);
                        $booking = $this->record;
                        $user = Auth::user();

                        if (! $user) {
                            logger()->error('BookingCalendarWidget: No authenticated user for booking');
                            Notification::make()
                                ->title('Authentication required')
                                ->danger()
                                ->send();

                            return;
                        }

                        $userRole = $user->role;
                        if ($userRole instanceof UnitEnum) {
                            $roleValue = $userRole->value;
                        } else {
                            $roleValue = (string) $userRole;
                        }

                        // Intentionally do not mount the service-period editor here.
                        // Booking-specific actions are mounted further below
                        // (see the $action = $canEdit ? 'options' : '' logic).

                        $userRole = $user->role;
                        if ($userRole instanceof UnitEnum) {
                            $roleValue = $userRole->value;
                        } else {
                            $roleValue = (string) $userRole;
                        }

                        $canEdit = $user->id === $booking->booking_user_id || in_array($roleValue, ['admin', 'super', 'super_admin'], true);
                        Log::info('BookingCalendarWidget: Booking click', [
                            'canEdit' => $canEdit,
                            'isBookingOwner' => $user->id === $booking->booking_user_id,
                            'userRole' => $roleValue,
                            'recordId' => $recId,
                        ]);
                        $action = $canEdit ? 'options' : '';
                        if ($action) {
                            logger()->info('CLICK $payload:', $payload);
                            $this->mountAction($action, [
                                'data' => $payload,
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('BookingCalendarWidget: Booking error', [
                            'error' => $e->getMessage(),
                            'recId' => $recId,
                        ]);
                        Notification::make()
                            ->title('Error loading booking')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                } else {
                    // All-day click for creating location
                    $timezone = config('app.timezone');
                    $startDate = $allDay ? Carbon::parse($start) : Carbon::parse($start, $timezone);
                    $this->mountAction('createDailyLocation', [
                        'date' => $startDate->format('Y-m-d'),
                        'service_date' => $startDate->format('Y-m-d'),
                        'service_user_id' => $event['extendedProps']['service_user_id'] ?? null,
                        'created_by' => Auth::id(),
                    ]);
                }
                break;
        }
    }

    public function eventDropped(string $eventId, string $startStr, ?string $endStr = null, string $type = 'booking', bool $allDay = false): void
    {
        logger('BookingCalendarWidget: eventDropped called', [
            'eventId' => $eventId,
            'startStr' => $startStr,
            'endStr' => $endStr,
            'type' => $type,
            'allDay' => $allDay,
        ]);

        // Only allow admins to drag and drop
        if (! Auth::check()) {
            logger('BookingCalendarWidget: Not authenticated');
            $this->dispatch('notify', 'error', 'You must be authenticated to modify events.');

            return;
        }

        $user = Auth::user();
        logger('BookingCalendarWidget: User authenticated', ['user_id' => $user->id, 'user_class' => get_class($user)]);
        $userRole = $user->role;
        if ($userRole instanceof UnitEnum) {
            $roleValue = $userRole->value;
        } else {
            $roleValue = (string) $userRole;
        }

        if (! in_array($roleValue, ['admin', 'super', 'super_admin'], true)) {
            logger('BookingCalendarWidget: Insufficient permissions', ['role' => $roleValue]);
            $this->dispatch('notify', 'error', 'You do not have permission to modify events.');

            return;
        }

        $start = Carbon::parse($startStr, config('app.timezone'));
        $end = $endStr ? Carbon::parse($endStr, config('app.timezone')) : null;

        logger('BookingCalendarWidget: Dates parsed', [
            'start' => $start->toDateTimeString(),
            'end' => $end?->toDateTimeString(),
            'serviceDate' => $start->toDateString(),
        ]);

        $serviceDate = $start->toDateString();

        // Validate drag operations based on event type and target
        switch ($type) {
            case 'booking':
            case 'blocking':
                // Timed events cannot be dropped to all-day row
                if ($allDay) {
                    $this->dispatch('notify', 'error', 'Timed events cannot be moved to the all-day row.');

                    return;
                }
                $startTime = $start->format('H:i:s');
                $endTime = $end?->format('H:i:s');
                break;

            case 'location':
                // Location events are always all-day and should stay that way
                if (! $allDay) {
                    $this->dispatch('notify', 'error', 'Location events can only be moved within the all-day row.');

                    return;
                }
                $startTime = null;
                $endTime = null;
                break;

            default:
                $this->dispatch('notify', 'error', 'Unknown event type.');

                return;
        }

        switch ($type) {
            case 'booking':
                /** @var Booking|null $booking */
                $booking = Booking::find($eventId);
                if ($booking) {
                    logger('BookingCalendarWidget: Booking found for move', ['id' => $eventId]);
                    $booking->update([
                        'service_date' => $serviceDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'starts_at' => $start->toIso8601String(),
                        'ends_at' => $end?->toIso8601String(),
                    ]);
                    logger('BookingCalendarWidget: Booking moved', [
                        'booking_id' => $booking->id,
                        'service_date' => $serviceDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ]);
                    $this->dispatch('notify', 'success', 'Booking moved successfully.');

                    // Sync to Google Calendar and send WhatsApp
                    SyncBookingToGoogleCalendar::dispatch($booking, sendWhatsapp: true);
                } else {
                    logger('BookingCalendarWidget: Booking not found', ['id' => $eventId]);
                }
                break;

            case 'location':
                /** @var DailyLocation|null $location */
                $location = DailyLocation::find($eventId);
                if ($location) {
                    $location->update([
                        'date' => $serviceDate,
                    ]);
                    $this->dispatch('notify', 'success', 'Location moved successfully.');
                }
                break;

            case 'blocking':
                /** @var BookingServicePeriod|null $blocking */
                $blocking = BookingServicePeriod::find($eventId);
                if ($blocking) {
                    $blocking->update([
                        'service_date' => $serviceDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'starts_at' => $start->toIso8601String(),
                        'ends_at' => $end?->toIso8601String(),
                    ]);
                    $this->dispatch('notify', 'success', 'Blocking period moved successfully.');
                }
                break;
        }

        // Refresh the calendar
        $this->refreshRecords();
    }

    public function onEventDrop($event): void
    {
        $id = data_get($event, 'id');
        $start = data_get($event, 'startStr') ?? data_get($event, 'start');
        $end = data_get($event, 'endStr') ?? data_get($event, 'end');
        $type = data_get($event, 'extendedProps.type') ?? data_get($event, 'type') ?? 'booking';
        $allDay = data_get($event, 'allDay', false);

        if (! $id || ! $start) {
            $this->dispatch('notify', 'error', 'Unable to move event: missing event data.');

            return;
        }

        $this->eventDropped((string) $id, (string) $start, $end ? (string) $end : null, (string) $type, (bool) $allDay);
    }

    public function resolveDateSelectAction(bool $allDay, ?array $view): string
    {
        $user = Auth::user();
        $isAdmin = $user ? $this->isAdmin($user) : false;

        if ($isAdmin) {
            if ($allDay) {
                return 'createDailyLocation';
            }

            return 'admin';
        }

        return 'create';
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
            Group::make()
                ->schema([
                    Section::make()
                        ->schema(BookingForm::getDetailsComponents())
                        ->columns(2),

                    Section::make('Booking items')
                        ->afterHeader([
                            Action::make('reset-items')
                                ->label('Reset items')
                                ->color('danger')
                                ->modalHeading('Reset booking items')
                                ->modalDescription('All existing items will be removed from the booking.')
                                ->requiresConfirmation()
                                ->action(fn (Set $set) => $set('items', [])),
                        ])
                        ->schema([
                            $this->getCalendarItemsRepeater(),
                        ]),
                ])
                ->columnSpan(['lg' => 3]),
        ];
    }

    public function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        $filters = $this->pageFilters;
        $selectedCalendarId = $filters['booking_calendars_2'] ?? null;

        $serviceUserId = null;
        if ($selectedCalendarId) {
            $calendar = BookingCalendar::find($selectedCalendarId);
            $serviceUserId = $calendar?->owner_id;
        }

        $blockingPeriods = BookingServicePeriod::query()
            ->when($serviceUserId, fn ($query) => $query->where('service_user_id', $serviceUserId))
            ->where('period_type', '=', 'unavailable')
            ->get();

        $blockingEvents = $blockingPeriods->map(fn (BookingServicePeriod $blockingPeriod) => $blockingPeriod->toCalendarEvent())->toArray();

        $bookings = Booking::query()
            ->with(['client', 'service', 'serviceUser', 'bookingUser', 'location'])
            ->when($serviceUserId, fn ($query) => $query->where('service_user_id', $serviceUserId))
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
            ->when($serviceUserId, fn ($query) => $query->where('service_user_id', $serviceUserId))
            ->whereBetween('date', [$start, $end])
            ->with(['serviceUser'])
            ->get();

        $locationEvents = $dailyLocations->map(function (DailyLocation $loc) {
            $title = $loc->location ?: ($loc->serviceUser?->name ?? 'Location');

            return [
                'id' => $loc->id,
                'title' => $title,
                'eventsType' => 'location',
                'type' => 'location',
                'start' => $loc->date?->toDateString(),
                'number' => 0,
                'allDay' => true,
                'backgroundColor' => 'primary',
                'borderColor' => 'transparent',
                'textColor' => '#111827',
                'extendedProps' => [
                    'is_location' => true,
                    'type' => 'location',
                    'daily_location_id' => $loc->id,
                    'service_user_id' => $loc->service_user_id,
                    'location' => $loc->location,
                ],
            ];
        })->toArray();

        return collect(array_merge($bookingEvents, $locationEvents, $blockingEvents));
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

    public function refreshCalendar()
    {
        $this->selectedTechnician = isset($this->pageFilters['booking_calendars_2']) ? (int) $this->pageFilters['booking_calendars_2'] : null;
        $this->refreshRecords();
    }

    public function onEventResizeLegacy(array $event, array $oldEvent, array $relatedEvents, array $startDelta, array $endDelta): bool
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        // Handle the resize by updating the booking
        if ($this->record instanceof Booking) {
            $endDeltaMs = $endDelta['milliseconds'] ?? 0;
            $newEnd = Carbon::parse($oldEvent['end'])->addMilliseconds($endDeltaMs);

            $this->record->forceFill([
                'ends_at' => $newEnd,
            ])->save();

            $this->refreshRecords();

            return true;
        }

        return false;
    }

    public function mount(): void
    {
        $this->selectedTechnician = isset($this->pageFilters['booking_calendars_2']) ? (int) $this->pageFilters['booking_calendars_2'] : null;
        $this->eventClickEnabled = true;
        //    $this->dateClickEnabled = true;
        $this->eventDragEnabled = true;
        $this->eventResizeEnabled = true;
        $this->dateSelectEnabled = true;
    }

    public function getView(): string
    {
        return 'adultdate/filament-booking::calendar-widget';
    }

    protected function getEloquentQuery(): Builder
    {
        return $this->getModel()::query();
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

    protected function isAdmin(Model|Authenticatable $user): bool
    {
        // If it's an Admin model, always return true
        if ($user instanceof Admin) {
            return true;
        }

        // For User model, check the role attribute
        $userRole = $user->role;

        // Handle enum instances
        if ($userRole instanceof UnitEnum) {
            $roleValue = $userRole->value;
        } else {
            $roleValue = (string) $userRole;
        }

        return in_array($roleValue, ['admin', 'super', 'super_admin'], true);
    }

    protected function getCalendarItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->table([
                TableColumn::make('Service'),
                TableColumn::make('Quantity')
                    ->width(100),
                TableColumn::make('Unit Price')
                    ->width(110),
            ])
            ->schema([
                Select::make('booking_service_id')
                    ->label('Service')
                    ->options(Service::query()->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('unit_price', Service::find($state)?->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('unit_price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required(),
            ])
            ->orderColumn('sort')
            ->defaultItems(1)
            ->hiddenLabel();
    }

    protected function getDefaultFormData(array $seed = []): array
    {
        return array_replace([
            'number' => $this->generateNumber(),
            'booking_client_id' => null,
            'service_id' => null,
            'booking_user_id' => null,
            'booking_location_id' => null,
            'booking_calendar_id' => null,
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

    protected function getSelectedServiceUserId(): ?int
    {
        $filters = $this->pageFilters ?? [];
        $selectedCalendarId = $filters['booking_calendars_2'] ?? null;

        if ($selectedCalendarId) {
            $calendar = BookingCalendar::find($selectedCalendarId);

            return $calendar?->owner_id;
        }

        return null;
    }

    protected function getSelectedCalendarId(): ?int
    {
        $filters = $this->pageFilters ?? [];

        $value = $filters['booking_calendars_2'] ?? null;

        return $value ? (int) $value : null;
    }

    protected function getDefaultCalendarId(): ?int
    {
        $serviceUserId = $this->getSelectedServiceUserId();
        if (! $serviceUserId) {
            return null;
        }
        logger()->info('GOOGLE CALENDAR', ['service_user_id' => $serviceUserId]);
        // Find a calendar owned by the selected service user
        $calendar = BookingCalendar::where('owner_id', $serviceUserId)->first();

        return $calendar ? $calendar->id : null;
    }
}
