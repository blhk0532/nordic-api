<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Actions;

use Adultdate\FilamentBooking\Concerns\CalendarAction;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\FilamentBookingPlugin;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\Booking\Service;
use Carbon\Carbon;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CreateAction extends \Filament\Actions\CreateAction
{
    use CalendarAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->form(
                fn (Schema $schema, CreateAction $action, HasCalendar $livewire) => $livewire
                    ->getFormSchemaForModel($schema, $action->getModel())
            )
            ->mutateDataUsing(function (array $data): array {
                $model = $this->getModel();
                if ($model && is_a($model, Booking::class, true)) {
                    if (! isset($data['schedulable_type']) || ! isset($data['schedulable_id'])) {
                        $user = Auth::user();
                        if ($user) {
                            $data['schedulable_type'] = $user::class;
                            $data['schedulable_id'] = $user->id;
                        }
                    }
                }

                return $data;
            })
            // Ensure forms are prefilled when the action is mounted programmatically
            ->mountUsing(function ($formOrSchema, array $arguments) {
                // Reset form state to avoid leftover values from previous mounts
                if ($formOrSchema instanceof Schema) {
                    $formOrSchema->fill([]);
                } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                    $formOrSchema->fill([]);
                }

                // Normalize nested `data` payload into top-level keys so callers that pass dates under `data` (calendar context) are handled.
                if (isset($arguments['data']) && is_array($arguments['data'])) {
                    foreach ($arguments['data'] as $k => $v) {
                        if (! array_key_exists($k, $arguments) || is_null($arguments[$k])) {
                            $arguments[$k] = $v;
                        }
                    }
                }

                // Normalize service_date/date into start_date so calendar callers that pass 'service_date'
                // (or 'date') will still prefill the form.
                if (isset($arguments['service_date']) && ! isset($arguments['start_date'])) {
                    $arguments['start_date'] = $arguments['service_date'];
                }

                if (isset($arguments['date']) && ! isset($arguments['start_date'])) {
                    $arguments['start_date'] = $arguments['date'];
                }

                // If no date arguments provided, do nothing
                if (! isset($arguments['start']) && ! isset($arguments['start_date'])) {
                    return;
                }

                $timezone = FilamentBookingPlugin::make()->getTimezone();

                // Model-aware mapping: if the action creates Meetings or Sprints, set starts_at/ends_at datetimes,
                // otherwise provide start_date/start_time style values for Schedule forms.
                $model = $this->getModel();

                $isEventModel = false;
                $isDailyLocationModel = false;

                // Avoid hard dependency by checking class names
                if ($model) {
                    $eventModelClasses = [
                        Booking::class,
                        Service::class,
                    ];
                    foreach ($eventModelClasses as $eventModelClass) {
                        if (is_a($model, $eventModelClass, true)) {
                            $isEventModel = true;
                            break;
                        }
                    }

                    // Check for DailyLocation model
                    if (is_a($model, DailyLocation::class, true)) {
                        $isDailyLocationModel = true;
                    }
                }

                if ($isEventModel) {
                    // Prefer explicit date/time arguments when present
                    if (isset($arguments['start_date']) || isset($arguments['start_time'])) {
                        $startDate = $arguments['start_date'] ?? null;
                        $startTime = $arguments['start_time'] ?? '00:00';
                        $endDate = $arguments['end_date'] ?? null;
                        $endTime = $arguments['end_time'] ?? null;

                        $startsAt = null;
                        $endsAt = null;

                        if ($startDate) {
                            $startDate = Carbon::parse($startDate, $timezone)->format('Y-m-d');
                            $startsAt = Carbon::createFromFormat('Y-m-d H:i', $startDate.' '.$startTime, $timezone)->toDateTimeString();
                        }

                        if ($endDate) {
                            $endDate = Carbon::parse($endDate, $timezone)->format('Y-m-d');
                            $et = $endTime ?? ($startTime ?? '00:00');
                            $endsAt = Carbon::createFromFormat('Y-m-d H:i', $endDate.' '.$et, $timezone)->toDateTimeString();
                        } elseif (isset($arguments['end'])) {
                            $endsAt = Carbon::parse($arguments['end'], $timezone)->toDateTimeString();
                        }

                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                            'metadata' => $meta,
                        ];
                    } else {
                        // Use ISO start/end values
                        $start = isset($arguments['start']) ? Carbon::parse($arguments['start'], $timezone) : null;
                        $end = isset($arguments['end']) ? Carbon::parse($arguments['end'], $timezone) : null;

                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'starts_at' => $start ? $start->toDateTimeString() : null,
                            'ends_at' => $end ? $end->toDateTimeString() : null,
                            'metadata' => $meta,
                        ];
                    }
                } else {
                    // Prefer explicit date/time arguments when present
                    if (isset($arguments['start_date']) || isset($arguments['start_time'])) {
                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'service_date' => $arguments['service_date'] ?? $arguments['start_date'] ?? null,
                            'start_date' => $arguments['start_date'] ?? $arguments['service_date'] ?? null,
                            'start_time' => $arguments['start_time'] ?? null,
                            'end_date' => $arguments['end_date'] ?? null,
                            'end_time' => $arguments['end_time'] ?? null,
                            // Ensure metadata key exists (as JSON string for CodeEditor)
                            'metadata' => $meta,
                        ];

                        foreach ($arguments as $key => $value) {
                            if (! array_key_exists($key, $values)) {
                                $values[$key] = $value;
                            }
                        }

                        $user = Auth::user();
                        if ($user) {
                            $values['schedulable_type'] = $user::class;
                            $values['schedulable_id'] = $user->id;
                        }
                    } else {
                        $start = null;

                        if (isset($arguments['start'])) {
                            $start = Carbon::parse($arguments['start'], $timezone);
                        } elseif (isset($arguments['date'])) {
                            $start = Carbon::parse($arguments['date'], $timezone);
                        }

                        if ($start) {
                            if ($isDailyLocationModel) {
                                // DailyLocation uses 'date' field instead of 'start_date'
                                $values = [
                                    'date' => $start->format('Y-m-d'),
                                    'metadata' => $arguments['metadata'] ?? [],
                                ];
                            } else {
                                $values = [
                                    'service_date' => $start->format('Y-m-d'),
                                    'start_date' => $start->format('Y-m-d'),
                                    'end_date' => isset($arguments['end']) ? Carbon::parse($arguments['end'], $timezone)->format('Y-m-d') : null,
                                    'metadata' => $arguments['metadata'] ?? [],
                                ];
                            }
                        } else {
                            $values = [];
                        }

                        $user = Auth::user();
                        if ($user) {
                            $values['schedulable_type'] = $user::class;
                            $values['schedulable_id'] = $user->id;
                        }

                        if ($start && $start->format('H:i:s') !== '00:00:00') {
                            $values['start_time'] = $start->format('H:i');
                        }

                        if (isset($arguments['end'])) {
                            $end = Carbon::parse($arguments['end'], $timezone);
                            if ($end->format('H:i:s') !== '00:00:00') {
                                $values['end_time'] = $end->format('H:i');
                            }
                        }

                        foreach ($arguments as $key => $value) {
                            if ($key !== 'data' && ! array_key_exists($key, $values)) {
                                $values[$key] = $value;
                            }
                        }
                    }
                }

                // Prefer Schema instances
                if ($formOrSchema instanceof Schema) {
                    $formOrSchema->fillPartially($values, array_keys($values));

                    return;
                }

                if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                    $formOrSchema->fill($values);

                    return;
                }
            })
            ->after(fn (HasCalendar $livewire) => $livewire->refreshRecords())
            ->cancelParentActions();
    }
}
