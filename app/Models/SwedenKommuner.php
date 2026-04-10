<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SwedenKommuner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sweden_kommuner';

    /** @var array<int, string> */
    protected $fillable = [
        'kommun', 'lan', 'personer', 'personer_count', 'foretag', 'latitude', 'longitude',
    ];

    public function swedenPostorter(): HasMany
    {
        return $this->hasMany(SwedenPostorter::class, 'kommun', 'kommun');
    }

    public function swedenPostnummer(): HasMany
    {
        return $this->hasMany(SwedenPostnummer::class, 'kommun', 'kommun');
    }

    public function swedenAdresser(): HasMany
    {
        return $this->hasMany(SwedenAdresser::class, 'kommun', 'kommun');
    }

    public function swedenGator(): HasMany
    {
        return $this->hasMany(SwedenGator::class, 'kommun', 'kommun');
    }
}
