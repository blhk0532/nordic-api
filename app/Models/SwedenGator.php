<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwedenGator extends Model
{
    use HasFactory;

    protected $table = 'sweden_gator';

    /** @var array<int, string> */
    protected $fillable = [
        'gata', 'postnummer', 'postort', 'kommun', 'lan', 'latitude', 'longitude', 'personer', 'foretag',
    ];

    /**
     * @return array{latitude: string, longitude: string}
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'latitude' => 'latitude',
            'longitude' => 'longitude',
        ];
    }
}
