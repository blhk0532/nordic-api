<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Adultdate\FilamentBooking\Models\Address;
use Adultdate\FilamentBooking\Models\Comment;
use Database\Factories\Booking\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var string
     */
    protected $table = 'booking_customers';

    /**
     * @var string
     */
    protected $keyType = 'int';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'ulid',
        'name',
        'address',
        'phone',
        'email',
        'birthday',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'string',
        'birthday' => 'date',
        'address' => 'string',
        'phone' => 'string',
    ];

    /** @return MorphToMany<Address, $this> */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'booking_addressable');
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasManyThrough<Payment, Order, $this> */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Order::class, 'booking_customer_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }
}
