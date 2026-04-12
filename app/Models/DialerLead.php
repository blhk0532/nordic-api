<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DialerLeadStatus;
use Database\Factories\DialerLeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DialerLead extends Model
{
    /** @use HasFactory<DialerLeadFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'dialer_campaign_id',
        'phone_number',
        'name',
        'status',
        'priority',
        'attempts_count',
        'last_attempted_at',
        'last_disposition',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => DialerLeadStatus::class,
            'meta' => 'array',
            'last_attempted_at' => 'datetime',
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

    public function attempts(): HasMany
    {
        return $this->hasMany(DialerCallAttempt::class);
    }
}
