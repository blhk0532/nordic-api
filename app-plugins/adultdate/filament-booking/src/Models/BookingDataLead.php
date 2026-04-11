<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingDataLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'luid',
        'name',
        'address',
        'street',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'email',
        'dob',
        'age',
        'sex',
        'status',
        'is_active',
        'assigned_to',
        'attempt_count',
        'last_contacted_at',
        'notes',
        'metadata',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function callStats(): HasMany
    {
        return $this->hasMany(BookingCallStat::class, 'lead_id');
    }

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'age' => 'integer',
            'is_active' => 'boolean',
            'attempt_count' => 'integer',
            'last_contacted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
