<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DialerCampaignStatus;
use Database\Factories\DialerCampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DialerCampaign extends Model
{
    /** @use HasFactory<DialerCampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'status',
        'source_channel',
        'context',
        'caller_id',
        'max_concurrent_calls',
        'max_attempts',
        'retry_delay_seconds',
        'started_at',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DialerCampaignStatus::class,
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(DialerLead::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(DialerCallAttempt::class);
    }

    public function isRunning(): bool
    {
        return $this->status === DialerCampaignStatus::Running;
    }
}
