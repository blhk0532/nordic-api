<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Adultdate\FilamentBooking\Models\Booking\Brand as BookingBrand;
use Adultdate\FilamentBooking\Models\Booking\Service as BookingService;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WallaceMartinss\FilamentEvolution\Models\WhatsappInstance;

class BookingCalendar extends Model
{
    protected $fillable = [
        'name',
        'google_calendar_id',
        'whatsapp_id',
        'creator_id',
        'owner_id',
        'brand_id',
        'service_ids',
        'notification_user_ids',
        'access',
        'is_active',
        'public_url',
        'embed_code',
        'public_address_ical',
        'secret_address_ical',
        'shareable_link',
        'whatsapp_numbers',
        'notify_emails',
    ];

    protected $casts = [
        'access' => 'array',
        'notification_user_ids' => 'array',
        'is_active' => 'boolean',
        'whatsapp_numbers' => 'array',
        'service_ids' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(WhatsappInstance::class, 'whatsapp_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BookingBrand::class, 'brand_id');
    }

    public function services()
    {
        return BookingService::whereIn('id', $this->service_ids ?? [])->get();
    }

    public function getReceivingWhatsappNumbers(): array
    {
        return WhatsappInstance::whereIn('id', $this->whatsapp_numbers ?? [])->pluck('number')->toArray();
    }
}
