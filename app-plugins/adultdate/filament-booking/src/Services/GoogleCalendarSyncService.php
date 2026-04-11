<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Services;

use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\BookingCalendar;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\GoogleCalendar\Event;
use Spatie\GoogleCalendar\GoogleCalendarFactory;

class GoogleCalendarSyncService
{
    /**
     * Sync a booking to Google Calendar
     */
    public function syncBooking(Booking $booking, ?string $googleCalendarId = null): ?Event
    {
        try {
            // Get the google calendar ID from the booking calendar if not provided
            if (! $googleCalendarId) {
                $bookingCalendar = BookingCalendar::find($booking->booking_calendar_id ?? null);

                if (! $bookingCalendar || ! $bookingCalendar->google_calendar_id) {
                    Log::info('No Google Calendar ID found for booking', ['booking_id' => $booking->id]);

                    return null;
                }

                $googleCalendarId = $bookingCalendar->google_calendar_id;
            }

            // Check if booking already has a google event
            if ($booking->google_event_id) {
                return $this->updateGoogleEvent($booking, $googleCalendarId);
            }

            return $this->createGoogleEvent($booking, $googleCalendarId);
        } catch (Exception $e) {
            Log::error('Failed to sync booking to Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a Google Calendar event
     */
    public function deleteGoogleEvent(Booking $booking, ?string $googleCalendarId = null): bool
    {
        try {
            if (! $booking->google_event_id) {
                return false;
            }

            if (! $googleCalendarId) {
                $bookingCalendar = BookingCalendar::find($booking->booking_calendar_id ?? null);

                if (! $bookingCalendar || ! $bookingCalendar->google_calendar_id) {
                    return false;
                }

                $googleCalendarId = $bookingCalendar->google_calendar_id;
            }

            $event = $this->fetchGoogleEvent($booking->google_event_id, $googleCalendarId);

            // If the API returned a collection, use the first item
            if ($event instanceof Collection) {
                $event = $event->first();
            }

            if (! $event instanceof Event) {
                Log::warning('Google event not found when attempting delete', [
                    'booking_id' => $booking->id,
                    'google_event_id' => $booking->google_event_id,
                ]);

                return false;
            }

            $event->delete();

            $booking->update(['google_event_id' => null]);

            Log::info('Deleted Google Calendar event for booking', [
                'booking_id' => $booking->id,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete Google Calendar event', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create a new Google Calendar event
     */
    private function createGoogleEvent(Booking $booking, string $googleCalendarId): Event
    {
        $properties = [
            'name' => $this->getEventTitle($booking),
            'description' => $this->getEventDescription($booking),
            'startDateTime' => $this->getStartDateTime($booking),
            'endDateTime' => $this->getEndDateTime($booking),
        ];

        // Add location if available
        if ($booking->bookingLocation) {
            $properties['location'] = $this->getLocationString($booking);
        }

        // Create & save the event to the specified Google Calendar
        $event = Event::create($properties, $googleCalendarId);

        // Store the Google event ID in the booking
        $booking->update(['google_event_id' => $event->id]);

        Log::info('Created Google Calendar event for booking', [
            'booking_id' => $booking->id,
            'google_event_id' => $event->id,
        ]);

        return $event;
    }

    /**
     * Update an existing Google Calendar event
     */
    private function updateGoogleEvent(Booking $booking, string $googleCalendarId): Event
    {
        $event = $this->fetchGoogleEvent($booking->google_event_id, $googleCalendarId);

        // If the API returned a collection, use the first item
        if ($event instanceof Collection) {
            $event = $event->first();
        }

        if (! $event instanceof Event) {
            throw new RuntimeException('Google event not found for id: '.$booking->google_event_id);
        }

        $properties = [
            'name' => $this->getEventTitle($booking),
            'description' => $this->getEventDescription($booking),
            'startDateTime' => $this->getStartDateTime($booking),
            'endDateTime' => $this->getEndDateTime($booking),
        ];

        if ($booking->bookingLocation) {
            $properties['location'] = $this->getLocationString($booking);
        }

        $event = $event->update($properties);

        Log::info('Updated Google Calendar event for booking', [
            'booking_id' => $booking->id,
            'google_event_id' => $event->id,
        ]);

        return $event;
    }

    /**
     * Fetch a Google Calendar event (wrapper so tests can override the fetch behavior)
     */
    private function fetchGoogleEvent(string $eventId, string $googleCalendarId): mixed
    {
        $googleCalendar = GoogleCalendarFactory::createForCalendarId($googleCalendarId);
        $googleEvent = $googleCalendar->getEvent($eventId);

        return Event::createFromGoogleCalendarEvent($googleEvent, $googleCalendar->getCalendarId());
    }

    /**
     * Get the event title
     */
    private function getEventTitle(Booking $booking): string
    {
        $title = 'Booking #'.$booking->number;

        if ($booking->service) {
            $title .= ' - '.$booking->service->name;
        }

        if ($booking->bookingUser) {
            $title .= ' with '.$booking->bookingUser->name;
        }

        return $title;
    }

    /**
     * Get the event description
     */
    private function getEventDescription(Booking $booking): string
    {
        $description = [];

        $description[] = 'Booking Number: '.$booking->number;
        $description[] = 'Status: '.($booking->status?->value ?? 'N/A');

        if ($booking->service) {
            $description[] = 'Service: '.$booking->service->name;
        }

        if ($booking->bookingUser) {
            $description[] = 'Client: '.$booking->bookingUser->name;
        }

        $adminId = $booking->getAttributes()['admin_id'] ?? null;
        if ($adminId) {
            $adminName = $booking->admin?->name;
            if ($adminName) {
                $description[] = 'Created by Admin: '.$adminName;
            }
        }

        if ($booking->client) {
            $description[] = 'Client Info: '.$booking->client->name;
        }

        if ($booking->notes) {
            $description[] = '';
            $description[] = 'Notes:';
            $description[] = $booking->notes;
        }

        if ($booking->service_note) {
            $description[] = '';
            $description[] = 'Service Notes:';
            $description[] = $booking->service_note;
        }

        return implode("\n", $description);
    }

    /**
     * Get the start datetime
     */
    private function getStartDateTime(Booking $booking): Carbon
    {
        $dateTime = $booking->starts_at ?? Carbon::parse($booking->service_date.' '.$booking->start_time);

        return $dateTime instanceof CarbonImmutable ? $dateTime->toMutable() : $dateTime;
    }

    /**
     * Get the end datetime
     */
    private function getEndDateTime(Booking $booking): Carbon
    {
        $dateTime = $booking->ends_at ?? Carbon::parse($booking->service_date.' '.$booking->end_time);

        return $dateTime instanceof CarbonImmutable ? $dateTime->toMutable() : $dateTime;
    }

    /**
     * Get the location string
     */
    private function getLocationString(Booking $booking): string
    {
        if (! $booking->bookingLocation) {
            return '';
        }

        $location = $booking->bookingLocation;
        $parts = [];

        if ($location->name) {
            $parts[] = $location->name;
        }

        if ($location->address) {
            $parts[] = $location->address;
        }

        if ($location->city) {
            $parts[] = $location->city;
        }

        if ($location->country) {
            $parts[] = $location->country;
        }

        return implode(', ', $parts);
    }
}
