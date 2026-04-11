<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Actions;
use Adultdate\FilamentBooking\Concerns\CanRefreshCalendar;
use Adultdate\FilamentBooking\Concerns\HasHeaderActions;
use Adultdate\FilamentBooking\Concerns\HasSchema;
use Adultdate\FilamentBooking\Concerns\InteractsWithCalendar;
use Adultdate\FilamentBooking\Concerns\InteractsWithEventRecord;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\Models\CalendarEvent;
use Adultdate\FilamentBooking\Models\CalendarSettings;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CalendarWidget extends FullCalendarWidget implements HasCalendar
{
    use CanBeConfigured, CanRefreshCalendar, HasHeaderActions, HasSchema, InteractsWithCalendar, InteractsWithEventRecord, InteractsWithRawJS {
        InteractsWithCalendar::getOptions insteadof CanBeConfigured;
    }

    public Model|string|null $model = 'Adultdate\FilamentBooking\Models\CalendarEvent';

    protected static ?int $sort = 2;

    protected static bool $isDiscovered = true;

    protected static ?string $title = '';

    protected static string $viewIdentifier = 'adultdate/filament-booking::calendar-widget';

    protected int|string|array $columnSpan = 'full';

    //    public function getModel(): string
    //    {
    //        return $this->model;
    //    }

    public function config(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '07:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '21:00:00';

        $config = [
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'nowIndicator' => true,
            'views' => [
                'timeGridDay' => [
                    //    'slotMinTime' => $openingStart,
                    //    'slotMaxTime' => $openingEnd,
                    'slotMinTime' => '00:00:00',
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
                'timeGridWeek' => [
                    //    'slotMinTime' => $openingStart,
                    //    'slotMaxTime' => $openingEnd,
                    'slotMinTime' => '00:00:00',
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
            ],
        ];

        return $config;
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->label('Title'),

            Textarea::make('description')
                ->rows(3)
                ->label('Description')
                ->columnSpanFull(),

            DateTimePicker::make('start')
                ->required()
                ->native(false)
                ->seconds(false)
                ->label('Start Date & Time'),

            DateTimePicker::make('end')
                ->required()
                ->native(false)
                ->seconds(false)
                ->label('End Date & Time')
                ->rule('after:start'),

            Checkbox::make('all_day')
                ->label('All Day Event'),
        ];
    }

    public function getFormSchemaForModel(Schema $schema, ?string $model = null): Schema
    {
        return $schema->components($this->getFormSchema());
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->name('create')
                ->mountUsing(function (Action $action, Schema $schema, array $arguments): void {
                    // Accept either Form or Schema-like objects
                    $values = [
                        'start' => $arguments['start'] ?? null,
                        'end' => $arguments['end'] ?? null,
                        'all_day' => $arguments['allDay'] ?? false,
                    ];

                    // If the caller provided ISO start/end strings, also compute date/time convenience fields
                    if (! empty($arguments['start'])) {
                        try {
                            $s = Carbon::parse($arguments['start']);
                            $values['start_date'] = $s->format('Y-m-d');
                            $values['start_time'] = $s->format('H:i');
                            // Use a full datetime string that DateTimePicker can parse reliably
                            $values['start'] = $s->toDateTimeString(); // Y-m-d H:i:s
                        } catch (Throwable $e) {
                            // ignore parsing errors
                        }
                    }

                    if (! empty($arguments['end'])) {
                        try {
                            $e = Carbon::parse($arguments['end']);
                            $values['end_date'] = $e->format('Y-m-d');
                            $values['end_time'] = $e->format('H:i');
                            $values['end'] = $e->toDateTimeString();
                        } catch (Throwable $e) {
                            // ignore
                        }
                    }

                    $schema->fill($values);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    // Set user_id to current user
                    $data['user_id'] = Auth::user()?->id;

                    return $data;
                }),
        ];
    }

    /**
     * Handle date/time selection from FullCalendar and mount the Create action.
     * Provides normalized ISO strings and convenience start_date/start_time/end_date/end_time fields.
     */
    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        // The parent trait provides calculateTimezoneOffset() that normalizes timezone and day behavior
        if (method_exists($this, 'calculateTimezoneOffset')) {
            [$startCarbon, $endCarbon] = $this->calculateTimezoneOffset($start, $end, $allDay);
        } else {
            $timezone = config('app.timezone');
            $startCarbon = Carbon::parse($start, $timezone);
            $endCarbon = $end ? Carbon::parse($end, $timezone) : null;
        }

        $startIso = $startCarbon->toIsoString();
        $endIso = $endCarbon ? $endCarbon->toIsoString() : null;

        $startDate = $startCarbon->format('Y-m-d');
        $startTime = $startCarbon->format('H:i');
        $endDate = $endCarbon ? $endCarbon->format('Y-m-d') : null;
        $endTime = $endCarbon ? $endCarbon->format('H:i') : null;

        $this->mountAction('create', [
            'type' => 'select',
            'start' => $startIso,
            'end' => $endIso,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_date' => $endDate,
            'end_time' => $endTime,
            'allDay' => $allDay,
            'resource' => $resource,
            // Provide `data` payload so action entanglement paths exist for fields like starts_at/ends_at/description/users
            'data' => [
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_date' => $endDate,
                'end_time' => $endTime,
                'starts_at' => $startIso,
                'ends_at' => $endIso,
                'description' => null,
                'users' => [],                // Ensure entangled property exists for forms expecting a user id
                'user_id' => Auth::user()?->id,            ],
        ]);

        // Force the frontend to synchronize action modals so the modal opens immediately
        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        $userId = filament()->auth()->id() ?? Auth::id();

        if (! $userId) {
            $user = filament()->auth()->user();
            if ($user) {
                $userId = $user->id;
            }
        }

        // If no user is authenticated, return empty collection
        if (! $userId) {
            return collect();
        }

        return CalendarEvent::query()
            ->where('user_id', $userId)
            ->whereDate('end', '>=', $start)
            ->whereDate('start', '<=', $end)
            ->get();
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Ensure user_id is preserved
                    if (! isset($data['user_id'])) {
                        $data['user_id'] = Auth::user()?->id;
                    }

                    return $data;
                }),
        ];
    }
}
