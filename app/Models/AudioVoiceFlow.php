<?php

namespace App\Models;

use Database\Factories\AudioVoiceFlowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudioVoiceFlow extends Model
{
    /** @use HasFactory<AudioVoiceFlowFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'audio_voice_flow';

    protected $fillable = [
        'user_id',
        'name',
        'filename',
        'description',
        'status',
        'priority',
        'tags',
        'duration',
        'play_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'duration' => 'integer',
            'play_count' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeOrderByQueue($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('created_at', 'desc');
    }
}
