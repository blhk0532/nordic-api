<?php

namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifier_notifications';

    protected $fillable = [
        'notification_template_id',
        'user_id',
        'channel',
        'subject',
        'content',
        'data',
        'scheduled_at',
        'sent_at',
        'status',
        'error',
        'opened_at',
        'clicked_at',
        'opens_count',
        'clicks_count',
    ];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
