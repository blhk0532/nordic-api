<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = Booking::query()
            ->whereIn('status', ['booked', 'confirmed'])
            ->whereNotNull('starts_at');

        if ($request->has('id')) {
            $query->where('service_user_id', $request->input('id'));
        }

        if ($request->has('resourceId')) {
            $query->where('service_user_id', $request->input('resourceId'));
        }

        $events = $query->get()
            ->map(function (Booking $booking) {
                // Convert to Europe/Stockholm timezone
                $startsAt = $booking->starts_at->setTimezone('Europe/Stockholm');
                $endsAt = $booking->ends_at?->setTimezone('Europe/Stockholm');

                $event = [
                    'id' => $booking->id,
                    'title' => $booking->number,
                    'start' => $startsAt->toIso8601String(),
                ];

                if ($endsAt) {
                    $event['end'] = $endsAt->toIso8601String();
                }

                if ($booking->service_user_id) {
                    $event['resourceId'] = (string) $booking->service_user_id;
                }

                return $event;
            });

        return response()->json($events);
    }
}
