<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $postort
 * @property string $postnummer
 * @property int $personer
 * @property int $foretag
 * @property string|null $ratsit_link
 * @property string|null $kommun
 * @property string|null $lan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $team_id
 * @property bool $is_active
 * @property bool $is_queue
 * @property bool $is_done
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereForetag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereKommun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort wherePersoner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereRatsitLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort wherePostnummer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort wherePostort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RatsitPostort whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RatsitPostort extends Model
{
    use HasFactory;

    protected $table = 'sweden_postorter';

    protected $guarded = [];

    protected $casts = [
        'personer' => 'integer',
        'foretag' => 'integer',
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
