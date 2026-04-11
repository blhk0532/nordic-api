<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $queue
 * @property string|null $name
 * @property string|null $status
 * @property array<array-key, mixed> $payload
 * @property int $attempts
 * @property int|null $reserved_at
 * @property int $available_at
 * @property int $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereAvailableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereReservedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Job whereStatus($value)
 *
 * @mixin \Eloquent
 */
class Job extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'json',
        'attempts' => 'integer',
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer',
    ];

    public function getStatusAttribute(): string
    {
        if ($this->reserved_at !== null) {
            return 'reserved';
        }

        if ($this->available_at > time()) {
            return 'delayed';
        }

        return 'ready';
    }

    public function getNameAttribute(): ?string
    {
        $payload = $this->payload;

        if (is_array($payload) && isset($payload['displayName'])) {
            return $payload['displayName'];
        }

        if (is_string($payload) && json_decode($payload, true)) {
            $decoded = json_decode($payload, true);
            if (isset($decoded['displayName'])) {
                return $decoded['displayName'];
            }
        }

        return null;
    }
}
