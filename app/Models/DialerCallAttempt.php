<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DialerAttemptStatus;
use Database\Factories\DialerCallAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DialerCallAttempt extends Model
{
    /** @use HasFactory<DialerCallAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'dialer_campaign_id',
        'dialer_lead_id',
        'status',
        'ami_action_id',
        'ami_unique_id',
        'ami_linked_id',
        'channel',
        'destination',
        'disposition',
        'hangup_cause',
        'raw_event',
        'sent_at',
        'answered_at',
        'ended_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'status' => DialerAttemptStatus::class,
            'raw_event' => 'array',
            'sent_at' => 'datetime',
            'answered_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DialerCampaign::class, 'dialer_campaign_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(DialerLead::class, 'dialer_lead_id');
    }
}
