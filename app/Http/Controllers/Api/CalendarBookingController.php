<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Requests\Api\UpdateBookingRequest;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CalendarBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->buildBookingEvents($request));
    }

    public function publicIndex(Request $request): JsonResponse
    {
        return response()->json($this->buildBookingEvents($request));
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $bookingData = array_merge([
                'number' => $this->generateBookingNumber(),
                'booking_user_id' => Auth::id(),
                'status' => $validated['status'] ?? 'booked',
                'is_active' => true,
                'currency' => 'SEK',
            ], $validated);

            // Create starts_at and ends_at datetime fields
            if (isset($bookingData['service_date']) && isset($bookingData['start_time'])) {
                $bookingData['starts_at'] = Carbon::parse($bookingData['service_date'].' '.$bookingData['start_time']);
            }
            if (isset($bookingData['service_date']) && isset($bookingData['end_time'])) {
                $bookingData['ends_at'] = Carbon::parse($bookingData['service_date'].' '.$bookingData['end_time']);
            }

            $booking = Booking::create($bookingData);

            // Load relationships for response
            $booking->load(['client', 'service', 'serviceUser', 'location']);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $this->formatBookingForCalendar($booking),
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if (! $this->canEditBooking($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this booking',
            ], 403);
        }

        $validated = $request->validated();

        try {
            // Update starts_at and ends_at if date/time changed
            if (isset($validated['service_date']) || isset($validated['start_time'])) {
                $serviceDate = $validated['service_date'] ?? $booking->service_date;
                $startTime = $validated['start_time'] ?? $booking->start_time;
                $validated['starts_at'] = Carbon::parse($serviceDate.' '.$startTime);
            }

            if (isset($validated['service_date']) || isset($validated['end_time'])) {
                $serviceDate = $validated['service_date'] ?? $booking->service_date;
                $endTime = $validated['end_time'] ?? $booking->end_time;
                $validated['ends_at'] = Carbon::parse($serviceDate.' '.$endTime);
            }

            $booking->update($validated);

            // Load relationships for response
            $booking->load(['client', 'service', 'serviceUser', 'location']);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $this->formatBookingForCalendar($booking),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Booking $booking): JsonResponse
    {
        // Check authorization
        if (! $this->canEditBooking($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this booking',
            ], 403);
        }

        try {
            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete booking: '.$e->getMessage(),
            ], 500);
        }
    }

    public function move(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if (! $this->canEditBooking($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to move this booking',
            ], 403);
        }

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'nullable|date',
            'resource_id' => 'nullable|integer|exists:users,id',
        ]);

        try {
            $start = Carbon::parse($validated['start']);
            $end = $validated['end'] ? Carbon::parse($validated['end']) : null;

            $updateData = [
                'service_date' => $start->toDateString(),
                'start_time' => $start->toTimeString(),
                'starts_at' => $start,
            ];

            if ($end) {
                $updateData['end_time'] = $end->toTimeString();
                $updateData['ends_at'] = $end;
            }

            if (isset($validated['resource_id'])) {
                $updateData['service_user_id'] = $validated['resource_id'];
            }

            $booking->update($updateData);

            // Load relationships for response
            $booking->load(['client', 'service', 'serviceUser', 'location']);

            return response()->json([
                'success' => true,
                'message' => 'Booking moved successfully',
                'data' => $this->formatBookingForCalendar($booking),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move booking: '.$e->getMessage(),
            ], 500);
        }
    }

    public function resize(Request $request, Booking $booking): JsonResponse
    {
        // Check authorization
        if (! $this->canEditBooking($booking)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to resize this booking',
            ], 403);
        }

        $validated = $request->validate([
            'end' => 'required|date',
        ]);

        try {
            $end = Carbon::parse($validated['end']);

            $updateData = [
                'end_time' => $end->toTimeString(),
                'ends_at' => $end,
            ];

            $booking->update($updateData);

            // Load relationships for response
            $booking->load(['client', 'service', 'serviceUser', 'location']);

            return response()->json([
                'success' => true,
                'message' => 'Booking duration updated successfully',
                'data' => $this->formatBookingForCalendar($booking),
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resize booking: '.$e->getMessage(),
            ], 500);
        }
    }

    private function buildBookingEvents(Request $request): array
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'resource_id' => 'nullable|integer',
        ]);

        $start = Carbon::parse($request->input('start'))->startOfDay();
        $end = Carbon::parse($request->input('end'))->endOfDay();

        $query = Booking::query()
            ->with(['client', 'service', 'serviceUser', 'location', 'bookingCalendar'])
            ->where('is_active', true)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
                    ->when(
                        Schema::hasColumn('booking_bookings', 'starts_at'),
                        fn ($q) => $q->orWhereBetween('starts_at', [$start, $end]),
                    );
            });

        if ($request->has('resource_id')) {
            $query->where('service_user_id', $request->input('resource_id'));
        }

        if ($request->has('service_user_id')) {
            $query->where('service_user_id', $request->input('service_user_id'));
        }

        $bookings = $query->get();

        // Get DailyLocation events as all-day events
        $dailyLocationsQuery = DailyLocation::query()
            ->with(['serviceUser'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        // Filter daily locations by service_user_id if provided
        if ($request->has('resource_id') || $request->has('service_user_id')) {
            $userId = $request->input('resource_id') ?? $request->input('service_user_id');
            $dailyLocationsQuery->where('service_user_id', $userId);
        }

        $dailyLocations = $dailyLocationsQuery->get();

        $bookingEvents = $bookings->map(function (Booking $booking) {
            $event = [
                'id' => $booking->id,
                'title' => $booking->number,
                'start' => $booking->starts_at?->toIso8601String() ?? $booking->service_date?->toDateString(),
                'color' => $booking->color ?: $this->getEventColor($booking->status),
                'backgroundColor' => $booking->color ?: $this->getEventColor($booking->status),
                'borderColor' => $this->getEventBorderColor($booking->status),
                'extendedProps' => [
                    'type' => 'booking',
                    'status' => $booking->status,
                    'client_name' => optional($booking->client)->name,
                    'service_name' => optional($booking->service)->name,
                    'service_user_name' => optional($booking->serviceUser)->name,
                    'location_name' => optional($booking->location)->name,
                    'total_price' => $booking->total_price,
                    'notes' => $booking->notes,
                    'service_user_id' => $booking->service_user_id,
                    'booking_calendar_id' => $booking->booking_calendar_id,
                    'booking_calendar_name' => $booking->bookingCalendar?->name,
                    'booking_client_id' => $booking->booking_client_id,
                    'service_id' => $booking->service_id,
                    'google_event_id' => $booking->google_event_id,
                ],
            ];

            if ($booking->ends_at) {
                $event['end'] = $booking->ends_at->toIso8601String();
            }

            if ($booking->service_user_id) {
                $event['resourceId'] = (string) $booking->service_user_id;
            }

            return $event;
        })->toArray();

        $locationEvents = $dailyLocations->map(function (DailyLocation $location) {
            $event = [
                'id' => 'location-'.$location->id,
                'title' => $location->location ?: ($location->serviceUser?->name ?? 'Location'),
                'start' => $location->date?->toDateString(),
                'allDay' => true,
                'color' => '#ffffff',
                'backgroundColor' => '#ffffff',
                'borderColor' => '#e5e7eb',
                'textColor' => '#111827',
                'extendedProps' => [
                    'type' => 'location',
                    'eventsType' => 'location',
                    'daily_location_id' => $location->id,
                    'service_user_id' => $location->service_user_id,
                    'service_user_name' => $location->serviceUser?->name,
                    'location' => $location->location,
                ],
            ];

            if ($location->service_user_id) {
                $event['resourceId'] = (string) $location->service_user_id;
            }

            return $event;
        })->toArray();

        // Get service periods
        $servicePeriods = BookingServicePeriod::query()
            ->with(['serviceUser'])
            ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        if ($request->has('resource_id') || $request->has('service_user_id')) {
            $userId = $request->input('resource_id') ?? $request->input('service_user_id');
            $servicePeriods = $servicePeriods->where('service_user_id', $userId);
        }

        $servicePeriodsEvents = $servicePeriods->map(function (BookingServicePeriod $period) {
            $startDateTime = Carbon::parse($period->service_date->toDateString().' '.($period->start_time ?? '00:00'));
            $endDateTime = Carbon::parse($period->service_date->toDateString().' '.($period->end_time ?? '23:59'));

            $event = [
                'id' => 'service-period-'.$period->id,
                'title' => $period->period_type ? ucfirst($period->period_type) : 'Service Period',
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'color' => $period->color ?: $this->getServicePeriodColor($period->period_type),
                'backgroundColor' => $period->color ?: $this->getServicePeriodColor($period->period_type),
                'borderColor' => $period->color ? $this->adjustColorBrightness($period->color, -20) : $this->getServicePeriodBorderColor($period->period_type),
                'textColor' => $period->color ? '#ffffff' : '#1f2937',
                'extendedProps' => [
                    'type' => 'service_period',
                    'period_type' => $period->period_type,
                    'service_user_id' => $period->service_user_id,
                    'service_user_name' => $period->serviceUser?->name,
                    'service_location' => $period->service_location,
                    'service_date' => $period->service_date?->toDateString(),
                    'start_time' => $period->start_time,
                    'end_time' => $period->end_time,
                    'booking_service_period_id' => $period->id,
                    'color' => $period->color,
                ],
            ];

            if ($period->service_user_id) {
                $event['resourceId'] = (string) $period->service_user_id;
            }

            return $event;
        })->toArray();

        return array_merge($bookingEvents, $locationEvents, $servicePeriodsEvents);
    }

    private function canEditBooking(Booking $booking): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Admin, super, super_admin, or superadmin can edit any booking
        if (in_array($user->role, ['admin', 'super', 'super_admin', 'superadmin'], true)) {
            return true;
        }

        // Manager role can edit any booking
        if ($user->role === 'manager') {
            return true;
        }

        // Event creator (booking_user_id) can edit their own booking
        if ($booking->booking_user_id === $user->id) {
            return true;
        }

        return false;
    }

    private function generateBookingNumber(): string
    {
        return 'BK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    private function formatBookingForCalendar(Booking $booking): array
    {
        $title = $booking->title ?: $booking->number;
        $start = $booking->starts_at ?? ($booking->service_date ? $booking->service_date->startOfDay() : null);
        $end = $booking->ends_at;

        return [
            'id' => $booking->id,
            'number' => $booking->number,
            'title' => $title,
            'description' => $booking->description,
            'category' => $booking->category,
            'location' => $booking->location,
            'color' => $booking->color ?? '#3b82f6',
            'start' => $start?->toIso8601String(),
            'end' => $end?->toIso8601String(),
            'backgroundColor' => $this->getEventColor($booking->status),
            'borderColor' => $this->getEventBorderColor($booking->status),
            'resourceId' => $booking->service_user_id ? (string) $booking->service_user_id : null,
            'createdAt' => $booking->created_at?->toIso8601String(),
            'updatedAt' => $booking->updated_at?->toIso8601String(),
            'extendedProps' => [
                'type' => 'booking',
                'status' => $booking->status,
                'client_name' => optional($booking->client)->name,
                'service_name' => optional($booking->service)->name,
                'service_user_name' => optional($booking->serviceUser)->name,
                'location_name' => optional($booking->location)->name,
                'total_price' => $booking->total_price,
                'notes' => $booking->notes,
                'service_user_id' => $booking->service_user_id,
                'booking_calendar_id' => $booking->booking_calendar_id,
                'booking_calendar_name' => $booking->bookingCalendar?->name,
                'booking_client_id' => $booking->booking_client_id,
                'service_id' => $booking->service_id,
                'google_event_id' => $booking->google_event_id,
                'service_date' => $booking->service_date?->toDateString(),
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'title' => $booking->title,
                'category' => $booking->category,
                'location' => $booking->location,
                'color' => $booking->color,
            ],
        ];
    }

    private function getEventColor(BookingStatus|string|null $status): string
    {
        $value = $status instanceof BookingStatus ? $status->value : $status;

        return match ($value) {
            'confirmed' => '#10b981', // green
            'booked' => '#3b82f6', // blue
            'cancelled' => '#ef4444', // red
            'completed' => '#6b7280', // gray
            default => '#3b82f6',
        };
    }

    private function getEventBorderColor(BookingStatus|string|null $status): string
    {
        $value = $status instanceof BookingStatus ? $status->value : $status;

        return match ($value) {
            'confirmed' => '#059669',
            'booked' => '#1d4ed8',
            'cancelled' => '#dc2626',
            'completed' => '#4b5563',
            default => '#1d4ed8',
        };
    }

    private function getServicePeriodColor(?string $periodType): string
    {
        return match ($periodType) {
            'unavailable' => '#c33a3a', // light purple
            'available' => '#222ece', // light cyan
            'blocked' => '#c33a3a', // light cyan
            default => '#c33a3a', // light gray
        };
    }

    private function getServicePeriodBorderColor(?string $periodType): string
    {
        return match ($periodType) {
            'unavailable' => '#ddd6fe', // light purple border
            'available' => '#cffafe', // light cyan border
            'blocked' => '#cffafe', // light cyan border
            default => '#f3f4f6', // light gray border
        };
    }

    private function adjustColorBrightness(string $hex, int $steps): string
    {
        // Remove # if present
        $hex = mb_ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(mb_substr($hex, 0, 2));
        $g = hexdec(mb_substr($hex, 2, 2));
        $b = hexdec(mb_substr($hex, 4, 2));

        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
