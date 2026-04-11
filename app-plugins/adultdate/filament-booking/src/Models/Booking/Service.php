<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Adultdate\FilamentBooking\Enums\ServiceStatus;
use Adultdate\FilamentBooking\Models\BookingComment;
use Database\Factories\Booking\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Service extends Model implements HasMedia
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * @var string
     */
    protected $table = 'booking_services';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'service_code',
        'description',
        'price',
        'cost',
        'is_available',
        'time_duration',
        'status',
        'featured',
        'is_visible',
        'published_at',
        'seo_title',
        'seo_description',
        'booking_brand_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'featured' => 'boolean',
        'is_visible' => 'boolean',
        'is_available' => 'boolean',
        'status' => ServiceStatus::class,
        'published_at' => 'date',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'booking_brand_id');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'booking_category_service', 'booking_service_id', 'booking_category_id')->withTimestamps();
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(BookingComment::class, 'commentable');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('service-images')
            ->useDisk('service-images')
            ->acceptsMimeTypes(['image/jpeg'])
            ->registerMediaConversions(function (Media $media): void {
                $this
                    ->addMediaConversion('thumb')
                    ->width(40)
                    ->height(40)
                    ->performOnCollections('service-images');
            });
    }
}
