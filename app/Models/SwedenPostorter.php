<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwedenPostorter extends Model
{
    use HasFactory;

    protected $table = 'sweden_postorter';

    /** @var array<int, string> */
    protected $fillable = [
        'postort', 'kommun', 'lan', 'latitude', 'longitude', 'personer', 'foretag',
    ];
}
