<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merinfo extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'merinfos';

    /** @var array<int, string> */
    protected $fillable = [
        'type', 'short_uuid', 'name', 'givenNameOrFirstName', 'personalNumber', 'pnr', 'address', 'gender', 'is_celebrity', 'has_company_engagement', 'is_house', 'number_plus_count', 'phone_number', 'url', 'same_address_url',
    ];

    protected function casts(): array
    {
        return [
            'pnr' => 'array',
            'address' => 'array',
            'phone_number' => 'array',
            'is_celebrity' => 'boolean',
            'has_company_engagement' => 'boolean',
            'is_house' => 'boolean',
        ];
    }
}
