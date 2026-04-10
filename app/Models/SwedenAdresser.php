<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwedenAdresser extends Model
{
    use HasFactory;

    protected $table = 'sweden_adresser';

    /** @var array<int, string> */
    protected $fillable = [
        'adress', 'postnummer', 'postort', 'kommun', 'lan', 'latitude', 'longitude', 'personer', 'företag', 'adresser', 'ratsit_link',
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
