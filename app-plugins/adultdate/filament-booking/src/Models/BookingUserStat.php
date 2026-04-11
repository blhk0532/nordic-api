<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingUserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stats_date',
        'total_calls',
        'answered_calls',
        'voicemail_calls',
        'no_answer_calls',
        'busy_calls',
        'failed_calls',
        'other_calls',
        'booked_meetings_count',
        'total_duration',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            'stats_date' => 'date',
            'total_calls' => 'integer',
            'answered_calls' => 'integer',
            'voicemail_calls' => 'integer',
            'no_answer_calls' => 'integer',
            'busy_calls' => 'integer',
            'failed_calls' => 'integer',
            'other_calls' => 'integer',
            'booked_meetings_count' => 'integer',
            'total_duration' => 'integer',
        ];
    }
}
