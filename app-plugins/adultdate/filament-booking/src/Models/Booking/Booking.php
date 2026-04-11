<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Adultdate\FilamentBooking\Enums\BookingState;
use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Models\BookingCalendar;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\ModelStates\HasStates;

class Booking extends Model
{
    use HasFactory;
    use HasStates;
    use SoftDeletes;

    protected $table = 'booking_bookings';

    protected $fillable = [
        'number',
        'google_event_id',
        'service_id',
        'service_user_id',
        'booking_user_id',
        'admin_id',
        'booking_client_id',
        'booking_location_id',
        'booking_calendar_id',
        'total_price',
        'currency',
        'status',
        'service_date',
        'start_time',
        'end_time',
        'starts_at',
        'ends_at',
        'notes',
        'service_note',
        'is_active',
        'notified_at',
        'confirmed_at',
        'completed_at',
        'schedulable_type',
        'schedulable_id',
        'personnummer',
        'fastighetsbeteckning',
        'title',
        'description',
        'location',
        'color',
        'phone',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'is_active' => 'boolean',
        'notified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'service_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'state' => BookingState::class,
        'title',
        'description',
        'location',
        'color',
        'phone',
    ];

    protected $attributes = [
        'currency' => 'SEK',
        'is_active' => true,
        'state' => 'Adultdate\FilamentBooking\Enums\Booked',
    ];

    /**
     * Get the admin who created the booking (if any).
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /** @return MorphOne<OrderAddress, $this> */
    public function address(): MorphOne
    {
        return $this->morphOne(OrderAddress::class, 'booking_addressable');
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'booking_client_id');
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /** @return BelongsTo<User, $this> */
    public function serviceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_user_id')
            ->where('role', 'service');
    }

    /** @return BelongsTo<User, $this> */
    public function bookingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booking_user_id');
    }

    /** @return BelongsTo<BookingLocation, $this> */
    public function bookingLocation(): BelongsTo
    {
        return $this->belongsTo(BookingLocation::class, 'booking_location_id');
    }

    /** Alias for bookingLocation to satisfy widgets expecting `location` */
    public function location(): BelongsTo
    {
        return $this->bookingLocation();
    }

    /** @return BelongsTo<BookingCalendar, $this> */
    public function bookingCalendar(): BelongsTo
    {
        return $this->belongsTo(BookingCalendar::class, 'booking_calendar_id');
    }

    /** @return HasMany<BookingItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class, 'booking_booking_id');
    }

    /**
     * Calculate and return the total price from booking items
     */
    public function calculateTotalPrice(): float
    {
        return $this->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });
    }

    /**
     * Update the total price based on current items
     */
    public function updateTotalPrice(): void
    {
        $this->load('items');
        $this->update(['total_price' => $this->calculateTotalPrice()]);
    }

    /**
     * Convert booking to calendar event object
     */
    public function toCalendarEvent(): array
    {
        $start = null;
        $end = null;

        if ($this->service_date && $this->start_time) {
            // Handle start_time as Carbon datetime (due to 'datetime' cast)
            $timeStr = is_object($this->start_time)
                ? $this->start_time->format('H:i:s')
                : str($this->start_time)->padRight(8, ':00');
            $start = $this->service_date->toDateString().'T'.$timeStr;
        } elseif ($this->starts_at) {
            $start = $this->starts_at->toIso8601String();
        }

        if ($this->service_date && $this->end_time) {
            // Handle end_time as Carbon datetime (due to 'datetime' cast)
            $timeStr = is_object($this->end_time)
                ? $this->end_time->format('H:i:s')
                : str($this->end_time)->padRight(8, ':00');
            $end = $this->service_date->toDateString().'T'.$timeStr;
        } elseif ($this->ends_at) {
            $end = $this->ends_at->toIso8601String();
        }
        $timeStamp = time();
        $dateStamp = date('m-d-Y', $timeStamp);
        $bookingNumber = 'BK-'.strrev((string) $timeStamp).'-NDS-'.$dateStamp.'-'.$timeStamp;
        $baseTitle = ($this->client?->address ?? '').'  '.($this->client?->city ?? '');

        return [
            'id' => $this->id,
            'title' => $baseTitle,
            'start' => $start,
            'end' => $end,
            'type' => 'booking',
            'backgroundColor' => $this->status?->getCalendarColor() ?? BookingStatus::Booked->getCalendarColor(),
            'borderColor' => $this->status?->getCalendarColor() ?? BookingStatus::Booked->getCalendarColor(),
            'textColor' => 'white',
            'extendedProps' => [
                'key' => $this->id,  // Required: Record ID for event resolution
                'booking_id' => $this->id,
                'type' => 'booking',
                'number' => $bookingNumber,
                'client_name' => $this->client?->name,
                'client_address' => $this->client?->address,
                'client_city' => $this->client?->city,
                'service_date' => $this->service_date?->format('Y-m-d'),
                'service_name' => $this->service?->name,
                'service_user' => $this->serviceUser?->name,
                'booking_user' => $this->bookingUser?->name,
                'booking_user_id' => $this->bookingUser?->id,
                'location' => $this->bookingLocation?->name ?? $this->location,
                'displayLocation' => $this->bookingLocation?->name ?? $this->location,
                // Model FQCN used by calendar to select custom event content
                'model' => self::class,
                'status' => $this->status?->value,
                'total_price' => $this->total_price,
                'currency' => $this->currency,
                'notes' => $this->notes,
            ],
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (Booking $booking): void {
            $user = Auth::user();
            if (! $booking->booking_user_id && $user && $user instanceof User) {
                $booking->booking_user_id = $user->id;
                logger('Booking::creating - Set booking_user_id from Auth::user()', [
                    'user_id' => $user->id,
                    'booking_id' => $booking->id ?? null,
                ]);
            } else {
                logger('Booking::creating - booking_user_id not set or user is not App\\Models\\User', [
                    'auth_user' => $user ? get_class($user) : null,
                    'booking_id' => $booking->id ?? null,
                ]);
            }
        });
    }

    protected static function newFactory()
    {
        // Factory will be resolved automatically by HasFactory trait
        return parent::newFactory();
    }

    protected function registerStates(): void
    {
        $this->addState('state', BookingState::class);
    }
}
