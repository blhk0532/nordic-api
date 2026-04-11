<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingServicePeriod extends Model
{
    use HasFactory;

    protected $table = 'booking_service_periods';

    // Prefer explicit fillable to avoid accidental mass-assignment
    protected $fillable = [
        'service_date',
        'service_user_id',
        'service_location',
        'start_time',
        'end_time',
        'starts_at',
        'ends_at',
        'period_type',
        'created_by',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'service_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        // keep casts explicit
    ];

    /**
     * Safe accessor for `status` attribute.
     * Return raw attribute if present, otherwise null to avoid MissingAttributeException.
     */
    public function getStatusAttribute()
    {
        return $this->attributes['status'] ?? null;
    }

    /**
     * The user this service period belongs to.
     */
    public function serviceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_user_id')
            ->where('role', 'service');
    }

    /**
     * The user who created this period.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The bookings for this period.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Booking\Booking::class, 'service_user_id', 'service_user_id')
            ->where('service_date', $this->service_date);
    }

    public function toCalendarEvent(): array
    {
        $start = null;
        $end = null;

        if ($this->service_date && $this->start_time) {
            $start = $this->service_date->toDateString().'T'.
                str($this->start_time)->padRight(8, ':00');
        } elseif ($this->starts_at) {
            $start = $this->starts_at->toIso8601String();
        }

        if ($this->service_date && $this->end_time) {
            $end = $this->service_date->toDateString().'T'.
                str($this->end_time)->padRight(8, ':00');
        } elseif ($this->ends_at) {
            $end = $this->ends_at->toIso8601String();
        }

        $attrs = $this->getAttributes();

        $clientName = method_exists($this, 'client') ? $this->client?->name : null;
        $serviceName = method_exists($this, 'service') ? $this->service?->name : null;
        $bookingUserName = method_exists($this, 'bookingUser') ? $this->bookingUser?->name : null;
        $serviceUserName = method_exists($this, 'serviceUser') ? $this->serviceUser?->name : null;

        return [
            'id' => $this->id,
            'title' => $clientName ?? 'ⓘ upptagen',
            'start' => $start,
            'end' => $end,
            'type' => 'blocking',
            'backgroundColor' => match ($this->period_type) {
                'unavailable' => '#c33a3a', // light purple
                'available' => '#222ece', // light cyan
                'blocked' => '#222ece', // light cyan
                default => '#c33a3a', // light gray
            },
            'borderColor' => match ($this->period_type) {
                'unavailable' => '#ddd6fe',
                'available' => '#cffafe',
                'blocked' => '#cffafe',
                default => '#f3f4f6',
            },
            'textColor' => '#1f2937',
            'extendedProps' => [
                'key' => $this->id,  // Required: Record ID for event resolution
                'booking_id' => $this->id,
                'number' => $attrs['number'] ?? null,
                'client_name' => $clientName,
                'service_date' => $this->service_date?->format('Y-m-d'),
                'service_name' => $serviceName,
                'service_user' => $serviceUserName,
                'booking_user' => $bookingUserName,
                'location' => method_exists($this, 'location') ? $this->location?->name : null,
                'displayLocation' => method_exists($this, 'location') ? $this->location?->name : null,
                // Model FQCN used by calendar to select custom event content
                'model' => self::class,
                'status' => $this->status?->value,
                'total_price' => $attrs['total_price'] ?? null,
                'currency' => $attrs['currency'] ?? null,
                'notes' => $attrs['notes'] ?? null,
                'type' => 'blocking',
            ],
        ];
    }
}
