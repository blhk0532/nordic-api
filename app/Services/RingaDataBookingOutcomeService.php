<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Outcomes;
use App\Models\RingaData;
use App\Models\RingaDataOutcome;
use Illuminate\Support\Facades\DB;

class RingaDataBookingOutcomeService
{
    public function recordBooking(RingaData $record, int $bookingId, ?int $userId = null): void
    {
        DB::transaction(function () use ($record, $bookingId, $userId): void {
            $record->update([
                'booking_id' => $bookingId,
                'booked_at' => now(),
                'outcome' => Outcomes::Yes->value,
                'is_outcome' => true,
                'attempts' => ((int) $record->attempts) + 1,
            ]);

            RingaDataOutcome::query()->create([
                'ringa_data_id' => $record->id,
                'user_id' => $userId,
                'coutcome' => Outcomes::Yes->value,
            ]);
        });
    }
}
