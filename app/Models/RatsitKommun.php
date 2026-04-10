<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $kommun
 * @property int $personer_count
 * @property int $foretag_count
 * @property string|null $personer_link
 * @property int $personer_postorter
 * @property string|null $foretag_link
 * @property int $foretag_postorter
 * @property float|null $latitude
 * @property float|null $longitude
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereForetagCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereForetagLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereForetagPostorter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereKommun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun wherePersonerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun wherePersonerLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun wherePersonerPostorter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitKommun whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RatsitKommun extends Model
{
    use HasFactory;

    protected $table = 'sweden_kommuner';

    protected $guarded = [];

    protected $casts = [
        'personer' => 'integer',
        'personer_count' => 'integer',
        'foretag' => 'integer',
        'foretag_count' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function getPersonerCountAttribute(): int
    {
        return $this->personer ?? 0;
    }

    public function getForetagCountAttribute(): int
    {
        return $this->foretag ?? 0;
    }
}
