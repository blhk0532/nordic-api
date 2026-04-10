<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Adultdate\FilamentBooking\Models\Booking\BookingLocation;
use Adultdate\FilamentBooking\Models\Booking\Category;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\Service;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCalendar;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarDataController extends Controller
{
    public function clients(Request $request): JsonResponse
    {
        $clients = Client::query()
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'city' => $client->city,
                ];
            });

        return response()->json($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $client = Client::create($validated);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'city' => $client->city,
        ], 201);
    }

    public function services(Request $request): JsonResponse
    {
        $services = Service::query()
            ->where('is_available', true)
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'duration' => $service->time_duration,
                    'price' => $service->price,
                ];
            });

        return response()->json($services);
    }

    public function locations(Request $request): JsonResponse
    {
        $locations = BookingLocation::query()
            ->where('is_active', true)
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'address' => $location->address,
                    'city' => $location->city,
                ];
            });

        return response()->json($locations);
    }

    public function serviceUsers(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('role', 'service')
            ->where('status', 1)
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->map(function ($user) {
                // Get calendar access for this user
                $activeCalendars = BookingCalendar::query()
                    ->where('is_active', true)
                    ->where(function ($query) use ($user) {
                        $query->where('owner_id', $user->id)
                            ->orWhereJsonContains('access', $user->id);
                    })
                    ->pluck('id')
                    ->toArray();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'calendar_ids' => $activeCalendars,
                ];
            });

        return response()->json($users);
    }

    public function calendars(Request $request): JsonResponse
    {
        $calendars = BookingCalendar::query()
            ->where('is_active', true)
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get()
            ->map(function ($calendar) {
                return [
                    'id' => $calendar->id,
                    'name' => $calendar->name,
                    'owner_id' => $calendar->owner_id,
                    'color' => $calendar->color ?? '#3b82f6',
                ];
            });

        return response()->json($calendars);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_visible', true)
            ->orderBy('position')
            ->get(['id', 'slug', 'name'])
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'value' => $category->slug,
                    'label' => $category->name,
                ];
            });

        return response()->json($categories);
    }

    public function bookingStats(Request $request): JsonResponse
    {
        $stats = [
            'total_bookings' => Booking::where('is_active', true)->count(),
            'booked' => Booking::where('status', 'booked')->where('is_active', true)->count(),
            'confirmed' => Booking::where('status', 'confirmed')->where('is_active', true)->count(),
            'cancelled' => Booking::where('status', 'cancelled')->where('is_active', true)->count(),
            'completed' => Booking::where('status', 'completed')->where('is_active', true)->count(),
            'today_bookings' => Booking::whereDate('service_date', today())->where('is_active', true)->count(),
            'week_bookings' => Booking::whereBetween('service_date', [now()->startOfWeek(), now()->endOfWeek()])->where('is_active', true)->count(),
        ];

        return response()->json($stats);
    }
}
