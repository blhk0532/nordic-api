<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Adultdate\FilamentBooking\Enums\OrderStatus;
use Database\Factories\Booking\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'booking_orders';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'total_price',
        'status',
        'currency',
        'shipping_price',
        'shipping_method',
        'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    /** @return MorphOne<OrderAddress, $this> */
    public function address(): MorphOne
    {
        return $this->morphOne(OrderAddress::class, 'booking_addressable');
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'booking_customer_id');
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'booking_order_id');
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate and return the total price from order items
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
}
