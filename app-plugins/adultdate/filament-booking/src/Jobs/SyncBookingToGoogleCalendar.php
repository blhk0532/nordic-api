<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Jobs;

use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Services\GoogleCalendarSyncService;
use App\Services\RawWhatsappService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncBookingToGoogleCalendar implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking,
        public bool $sendWhatsapp = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarSyncService $syncService, RawWhatsappService $whatsappService): void
    {
        // Sync to Google Calendar
        $this->syncToGoogleCalendar($syncService);

        // Send WhatsApp if requested
        if ($this->sendWhatsapp) {
            $this->sendWhatsappNotification($whatsappService);
        }
    }

    protected function syncToGoogleCalendar(GoogleCalendarSyncService $syncService): void
    {
        $booking = $this->booking;

        // Only sync if booking has a calendar with Google Calendar ID
        $calendarId = $booking->booking_calendar_id;
        $googleCalendarId = $booking->bookingCalendar?->google_calendar_id;

        Log::info('SyncBookingToGoogleCalendar Job: syncToGoogleCalendar called', [
            'booking_id' => $booking->id,
            'booking_calendar_id' => $calendarId,
            'google_calendar_id' => $googleCalendarId,
        ]);

        if (! $googleCalendarId) {
            Log::info('SyncBookingToGoogleCalendar Job: No google_calendar_id, skipping sync', [
                'booking_id' => $booking->id,
                'booking_calendar_id' => $calendarId,
            ]);

            return;
        }

        try {
            $syncService->syncBooking($booking, $googleCalendarId);

            Log::info('SyncBookingToGoogleCalendar Job: Google sync completed', [
                'booking_id' => $booking->id,
                'google_calendar_id' => $googleCalendarId,
            ]);
        } catch (Exception $e) {
            Log::error('SyncBookingToGoogleCalendar Job: Failed to sync booking to Google Calendar', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function sendWhatsappNotification(RawWhatsappService $whatsappService): void
    {
        $booking = $this->booking;

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
                Log::warning('SyncBookingToGoogleCalendar Job: Whatsapp not sent: no destination number', [
                    'booking_id' => $booking->id,
                    'calendar_id' => $calendar->id,
                ]);

                return;
            }

            $serviceName = $booking->service?->name ?? 'Service';
            $clientName = $booking->client?->name ?? 'Client';
            $clientPhone = $booking->client?->phone ?? 'Unknown';
            $bookingNumber = $booking->number ?? 'N/A';
            $date = Carbon::parse($booking->service_date ?: $booking->starts_at ?: now())->format('Y-m-d');
            $start = $booking->start_time ?: Carbon::parse($booking->starts_at ?: now())->format('H:i');
            $end = $booking->end_time ?: Carbon::parse($booking->ends_at ?: now())->format('H:i');
            $addr = mb_trim(($booking->client?->address ?? '').' '.($booking->client?->city ?? ''));
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
                $bookingNumber ? "# {$bookingNumber}" : null,
            ]);

            $message = implode("\n", $lines);

            // Send raw number directly to Evolution API (no formatting)
            $whatsappService->sendTextRaw($instanceId, (string) $to, (string) $message);

            Log::info('SyncBookingToGoogleCalendar Job: Whatsapp booking notification sent', [
                'booking_id' => $booking->id,
                'instance_id' => $instanceId,
                'to' => $to,
            ]);
        } catch (Throwable $e) {
            Log::error('SyncBookingToGoogleCalendar Job: Failed sending WhatsApp notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
