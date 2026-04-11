<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Database\Factories\Booking\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * @var string
     */
    protected $table = 'booking_categories';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'position',
        'is_visible',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'booking_category_product', 'booking_category_id', 'booking_product_id');
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_category_service', 'booking_category_id', 'booking_service_id')->withTimestamps();
    }
}
