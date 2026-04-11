<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Observers;

use Adultdate\FilamentBooking\Jobs\SyncBookingToGoogleCalendar;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Services\GoogleCalendarSyncService;
use App\Models\Admin;
use App\Models\User;
use App\Services\RawWhatsappService;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;
use WallaceMartinss\FilamentEvolution\Services\WhatsappService;

class BookingObserver
{
    public function __construct(
        private GoogleCalendarSyncService $syncService,
        private WhatsappService $whatsapp
    ) {}

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        logger('BookingObserver: created event triggered', [
            'booking_id' => $booking->id,
            'booking_user_id' => $booking->booking_user_id,
            'admin_id' => $booking->admin_id,
        ]);

        // Send database notification if configured
        if ($booking->bookingCalendar?->notification_user_ids) {
            $recipients = collect();
            $recipientIds = collect(); // Track IDs to avoid duplicates

            foreach ($booking->bookingCalendar->notification_user_ids as $id) {
                if (str_starts_with($id, 'user-')) {
                    $userId = str_replace('user-', '', $id);
                    $user = User::find($userId);
                    if ($user && ! $recipientIds->contains("user-{$user->id}")) {
                        $recipients->push($user);
                        $recipientIds->push("user-{$user->id}");
                    }
                } elseif (str_starts_with($id, 'admin-')) {
                    $adminId = str_replace('admin-', '', $id);
                    $admin = Admin::find($adminId);
                    if ($admin && ! $recipientIds->contains("admin-{$admin->id}")) {
                        $recipients->push($admin);
                        $recipientIds->push("admin-{$admin->id}");
                    }
                }
            }

            // Add the booking creator if not already in recipients
            if ($booking->booking_user_id) {
                $creator = User::find($booking->booking_user_id);
                if ($creator && ! $recipientIds->contains("user-{$creator->id}")) {
                    $recipients->push($creator);
                    $recipientIds->push("user-{$creator->id}");
                }
            }

            if ($recipients->isNotEmpty()) {
                foreach ($recipients as $recipient) {
                    Notification::make()
                        ->title('New Booking Created')
                        ->body("Booking #{$booking->number} for {$booking->service?->name} has been created.")
                        ->sendToDatabase($recipient);
                }
            }
        }

        // Dispatch async job for Google Calendar sync and WhatsApp notification
        SyncBookingToGoogleCalendar::dispatch($booking, sendWhatsapp: true);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Only sync if relevant fields have changed
        if ($this->shouldSync($booking)) {
            // Send WhatsApp for updates (move, resize, etc.)
            SyncBookingToGoogleCalendar::dispatch($booking, sendWhatsapp: true);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        if ($booking->google_event_id && $booking->bookingCalendar?->google_calendar_id) {
            try {
                $this->syncService->deleteGoogleEvent(
                    $booking,
                    $booking->bookingCalendar->google_calendar_id
                );
            } catch (Exception $e) {
                Log::error('Failed to delete Google Calendar event on booking deletion', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Sync booking to Google Calendar
     */
    private function syncToGoogleCalendar(Booking $booking): void
    {
        // Only sync if booking has a calendar with Google Calendar ID
        $calendarId = $booking->booking_calendar_id;
        $googleCalendarId = $booking->bookingCalendar?->google_calendar_id;
        logger('BookingObserver: syncToGoogleCalendar called', [
            'booking_id' => $booking->id,
            'booking_calendar_id' => $calendarId,
            'google_calendar_id' => $googleCalendarId,
        ]);
        if (! $googleCalendarId) {
            logger('BookingObserver: No google_calendar_id, skipping sync', [
                'booking_id' => $booking->id,
                'booking_calendar_id' => $calendarId,
            ]);
            // Still attempt WhatsApp notification if configured
            $this->maybeSendWhatsapp($booking);

            return;
        }

        try {
            $this->syncService->syncBooking(
                $booking,
                $googleCalendarId
            );
            logger('BookingObserver: Google sync triggered', [
                'booking_id' => $booking->id,
                'google_calendar_id' => $googleCalendarId,
            ]);

            // After syncing to Google, optionally send WhatsApp notification
            $this->maybeSendWhatsapp($booking);
        } catch (Exception $e) {
            logger('Failed to sync booking to Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send WhatsApp booking notification if calendar has whatsapp instance.
     */
    private function maybeSendWhatsapp(Booking $booking): void
    {
        try {
            $calendar = $booking->bookingCalendar;
            if (! $calendar || empty($calendar->whatsapp_id)) {
                return;
            }

            $instanceId = $calendar->whatsapp_id;

            // Determine destination number: prefer instance number, then client, then owner
            $to = $calendar->whatsappInstance?->number
                ?? $booking->client?->phone
                ?? $calendar->owner?->phone
                ?? null;
            if (! $to) {
                Log::warning('Whatsapp not sent: no destination number', [
                    'booking_id' => $booking->id,
                    'calendar_id' => $calendar->id,
                ]);

                return;
            }

            $serviceName = $booking->service?->name ?? 'Service';
            $clientName = $booking->client?->name ?? 'Client';
            $clientPhone = $booking->client?->phone ?? 'Unknown';
            $BookingNumber = $booking->number ?? 'N/A';
            $date = Carbon::parse($booking->service_date ?: $booking->starts_at ?: now())->format('Y-m-d');
            $start = $booking->start_time ?: Carbon::parse($booking->starts_at ?: now())->format('H:i');
            $end = $booking->end_time ?: Carbon::parse($booking->ends_at ?: now())->format('H:i');
            $addr = mb_trim(($booking->client?->address ?? '').' '.($booking->client?->city ?? ''));
            $datenow = now()->format('d-m-Y');
            $serviceUserName = $booking->serviceUser?->name ?? null;
            $lines = array_filter([
                "🗓️⌯⌲NDS⋆｡˚{$date}",
                $serviceUserName ? "👷🏼 {$serviceUserName} 🕓 " : null,
                $start ? "{$start}" : null,
                $end ? "{$end}" : null,
                $clientName ? "🙋🏻‍♂️ {$clientName}" : null,
                $clientPhone ? "📞 {$clientPhone}" : null,
                $addr ? "🏠 {$addr}" : null,
                $serviceName ? "📋 {$serviceName}" : null,
                $BookingNumber ? "# {$BookingNumber}" : null,
            ]);

            $message = implode("\n", $lines);

            // Send raw number directly to Evolution API (no formatting)
            app(RawWhatsappService::class)->sendTextRaw($instanceId, (string) $to, (string) $message);

            Log::info('Whatsapp booking notification sent', [
                'booking_id' => $booking->id,
                'instance_id' => $instanceId,
                'to' => $to,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed sending WhatsApp notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine if booking should be synced based on changed attributes
     */
    private function shouldSync(Booking $booking): bool
    {
        $relevantFields = [
            'service_id',
            'service_date',
            'start_time',
            'end_time',
            'starts_at',
            'ends_at',
            'booking_location_id',
            'booking_calendar_id',
            'status',
            'notes',
            'service_note',
        ];

        foreach ($relevantFields as $field) {
            if ($booking->wasChanged($field)) {
                return true;
            }
        }

        return false;
    }
}
