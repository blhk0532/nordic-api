<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

trait InteractsWithCalendar
{
    use CanRefreshCalendar;
    use CanUseFilamentTimezone;
    use EvaluatesClosures;
    use HandlesDateClick;
    use HandlesDateSelect;
    use HandlesDatesSet;
    use HandlesEventAllUpdated;
    use HandlesEventClick;
    use HandlesEventDragAndDrop;
    use HandlesEventResize;
    use HandlesNoEventsClick;
    use HandlesViewDidMount;
    use HasCalendarContextData;
    use HasCalendarView;
    use HasContextMenu;
    use HasDayMaxEvents;
    use HasDefaultActions;
    use HasEventContent;
    use HasEvents;
    use HasFirstDay;
    use HasFooterActions;
    use HasHeaderActions;
    use HasHeading;
    use HasLocale;
    use HasMoreLinkContent;
    use HasOptions;
    use HasResourceLabelContent;
    use HasResources;
    use HasSchema;
    use HasTheme;
    use InteractsWithActions {
        InteractsWithActions::mountAction as baseMountAction;
    }
    use InteractsWithEventRecord;
    use InteractsWithSchemas;

    public function mountAction(string $name, array $arguments = [], array $context = []): mixed
    {
        // If the action is being mounted from inside a nested schema component (e.g. a repeater "Add"
        // button), do not override its arguments with calendar context. Let Filament handle it so we
        // don't clear the form state while adding items.
        if (isset($context['schemaComponent'])) {
            return $this->baseMountAction($name, $arguments, $context);
        }

        $raw = $this->getRawCalendarContextData() ?? [];

        // Merge provided arguments with raw calendar context
        $merged = [
            ...$arguments,
            ...$raw,
        ];

        // Normalize and enrich the `data` payload with keys expected by forms (starts_at/ends_at, start_date/start_time, users, description, user_id)
        $incomingData = isset($merged['data']) && is_array($merged['data']) ? $merged['data'] : [];

        $enrich = [];

        // Prefer explicit start/end fields on the root merged payload
        if (isset($merged['start'])) {
            $enrich['starts_at'] = $merged['start'];
        }

        if (isset($merged['end'])) {
            $enrich['ends_at'] = $merged['end'];
        }

        // Support dateClick payloads which provide `date` inside data
        if (isset($incomingData['date'])) {
            $enrich['starts_at'] = $incomingData['date'];
        }

        // If a starts_at exists but not ends_at, provide a sensible default (1 hour) unless it's an all-day selection
        $tmpStartsAtForDefault = $enrich['starts_at'] ?? ($incomingData['starts_at'] ?? null);
        $tmpEndsAtForDefault = $enrich['ends_at'] ?? ($incomingData['ends_at'] ?? null);
        $isAllDay = $incomingData['allDay'] ?? $merged['allDay'] ?? $merged['all_day'] ?? false;

        if ($tmpStartsAtForDefault && ! $tmpEndsAtForDefault && ! $isAllDay) {
            try {
                $enrich['ends_at'] = Carbon::parse($tmpStartsAtForDefault)->addHour()->toIsoString();
            } catch (Throwable $_) {
                // ignore parsing errors
            }
        }

        // Also include convenience date/time fields when available on the root
        if (isset($merged['start_date'])) {
            $enrich['start_date'] = $merged['start_date'];
        }

        if (isset($merged['start_time'])) {
            $enrich['start_time'] = $merged['start_time'];
        }

        if (isset($merged['end_date'])) {
            $enrich['end_date'] = $merged['end_date'];
        }

        if (isset($merged['end_time'])) {
            $enrich['end_time'] = $merged['end_time'];
        }

        // Also derive convenience date/time fields from starts_at/ends_at if missing
        try {
            $tmpStartsAt = $enrich['starts_at'] ?? ($incomingData['starts_at'] ?? null);
            $tmpEndsAt = $enrich['ends_at'] ?? ($incomingData['ends_at'] ?? null);

            if (! isset($enrich['start_date']) && $tmpStartsAt) {
                $dt = Carbon::parse($tmpStartsAt);
                $enrich['start_date'] = $dt->format('Y-m-d');
                $enrich['start_time'] = $dt->format('H:i');
            }

            if (! isset($enrich['end_date']) && $tmpEndsAt) {
                $dt2 = Carbon::parse($tmpEndsAt);
                $enrich['end_date'] = $dt2->format('Y-m-d');
                $enrich['end_time'] = $dt2->format('H:i');
            }
        } catch (Throwable $_) {
            // ignore parsing errors
        }

        // Ensure placeholders exist for expected keys
        $enrich['description'] = $incomingData['description'] ?? null;
        $enrich['users'] = $incomingData['users'] ?? [];
        $enrich['user_id'] = $incomingData['user_id'] ?? Auth::user()?->id;

        // Ensure metadata exists for forms that expect it. Convert arrays to JSON string so
        // components like CodeEditor (which expect a string) don't receive an array and throw
        // during client-side initialization (split is not a function).
        $rawMetadata = $incomingData['metadata'] ?? [];
        if (is_array($rawMetadata)) {
            $rawMetadata = count($rawMetadata) ? json_encode($rawMetadata, JSON_PRETTY_PRINT) : null;
        } elseif (! is_string($rawMetadata)) {
            $rawMetadata = null;
        }

        $enrich['metadata'] = $rawMetadata;

        // Ensure background_color exists for CalendarEvent forms (ColorPicker expects a key)
        $enrich['background_color'] = $incomingData['background_color'] ?? null;

        // Ensure starts_at/ends_at keys exist (even if null) so Livewire entanglement doesn't fail
        $enrich['starts_at'] = $enrich['starts_at'] ?? ($incomingData['starts_at'] ?? null);
        $enrich['ends_at'] = $enrich['ends_at'] ?? ($incomingData['ends_at'] ?? null);

        // Merge back into merged['data'] (incoming values take precedence)
        $merged['data'] = [
            ...$enrich,
            ...$incomingData,
        ];

        // Ensure root start/end are set from starts_at/ends_at if present so 'start'/'end' convenience keys exist
        if (! isset($merged['start']) && isset($merged['data']['starts_at'])) {
            $merged['start'] = $merged['data']['starts_at'];
        }

        if (! isset($merged['end']) && isset($merged['data']['ends_at'])) {
            $merged['end'] = $merged['data']['ends_at'];
        }

        // Provide convenience keys that some forms and front-end code expect
        $merged['data']['start'] = $merged['start'] ?? ($incomingData['start'] ?? $merged['data']['starts_at'] ?? null);
        $merged['data']['end'] = $merged['end'] ?? ($incomingData['end'] ?? $merged['data']['ends_at'] ?? null);
        $merged['data']['allDay'] = $merged['allDay'] ?? ($incomingData['allDay'] ?? ($merged['all_day'] ?? ($incomingData['all_day'] ?? null)));
        $merged['data']['startStr'] = $incomingData['startStr'] ?? ($merged['startStr'] ?? null);
        $merged['data']['endStr'] = $incomingData['endStr'] ?? ($merged['endStr'] ?? null);
        $merged['data']['view'] = $incomingData['view'] ?? ($merged['view'] ?? null);

        Log::debug('InteractsWithCalendar::mountAction', [
            'name' => $name,
            'arguments' => $arguments,
            'context' => $context,
            'rawCalendarContext' => $raw,
            'merged' => $merged,
        ]);

        $result = $this->baseMountAction($name, $merged, $context);

        // Ensure the newly mounted action has a `data` entry so Livewire entanglement to
        // mountedActions.0.data.* does not fail due to missing nested keys.
        try {
            $idx = max(0, count($this->mountedActions ?? []) - 1);

            if (isset($this->mountedActions[$idx]) && is_array($this->mountedActions[$idx])) {
                // Ensure the 'data' key exists in the mounted action array
                if (! isset($this->mountedActions[$idx]['data'])) {
                    $this->mountedActions[$idx]['data'] = $merged['data'] ?? [];
                }

                // Log final mounted state for diagnostic purposes
                Log::debug('InteractsWithCalendar::mountAction::ensuredData', [
                    'idx' => $idx,
                    'hasData' => isset($this->mountedActions[$idx]['data']),
                ]);
            }
        } catch (Throwable $e) {
            Log::debug('InteractsWithCalendar::mountAction::ensureDataFailed', ['error' => $e->getMessage()]);
        }

        return $result;
    }
}
