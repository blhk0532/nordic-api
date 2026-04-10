<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'persons';

    /** @var array<int, string> */
    protected $fillable = [
        'name', 'street', 'zip', 'city', 'kommun', 'phone', 'merinfo_id', 'merinfo_phone', 'personal_number', 'gender', 'merinfo_is_house', 'team_id',
    ];

    public function merinfo(): BelongsTo
    {
        return $this->belongsTo(Merinfo::class);
    }
}
