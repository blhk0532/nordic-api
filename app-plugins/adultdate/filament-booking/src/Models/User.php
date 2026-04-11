<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BookingCalendar;
use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Zap\Models\Concerns\HasSchedules;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasSchedules, Notifiable, TwoFactorAuthenticatable;

    /**
     * @method array getBookableSlots(string $date, int $slotDuration = 60, ?int $bufferMinutes = null)
     * @method array getAvailableSlots(string $date, string $dayStart = '07:00', string $dayEnd = '20:00', int $slotDuration = 60, ?int $bufferMinutes = null)
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public function calendars()
    {
        return $this->hasMany(BookingCalendar::class, 'owner_id');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
