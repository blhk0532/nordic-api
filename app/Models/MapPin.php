<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapPin extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
