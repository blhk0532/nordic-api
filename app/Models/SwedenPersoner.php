<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwedenPersoner extends Model
{
    protected $table = 'sweden_personer';

    /** @var array<int, string> */
    protected $fillable = [
        'adress',
        'postnummer',
        'postort',
        'fornamn',
        'efternamn',
        'personnamn',
        'alder',
        'kommun',
        'lan',
        'latitude',
        'longitude',
        'personnummer',
        'kon',
        'telefon',
        'telefonnummer',
        'civilstand',
        'adressandring',
        'bostadstyp',
        'agandeform',
        'boarea',
        'byggar',
        'personer',
        'ratsit_link',
        'ratsit_data',
        'hitta_link',
        'hitta_data',
        'merinfo_link',
        'merinfo_data',
        'eniro_link',
        'eniro_data',
        'upplysning_link',
        'upplysning_data',
        'mrkoll_link',
        'mrkoll_data',
        'is_hus',
        'is_owner',
        'is_active',
        'is_queue',
        'is_done',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'telefonnummer' => 'array',
        'ratsit_data' => 'array',
        'hitta_data' => 'array',
        'merinfo_data' => 'array',
        'eniro_data' => 'array',
        'upplysning_data' => 'array',
        'mrkoll_data' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_hus' => 'boolean',
        'is_owner' => 'boolean',
        'is_active' => 'boolean',
        'is_queue' => 'boolean',
        'is_done' => 'boolean',
        'postnummer' => 'string',
    ];

    public function swedenKommun(): BelongsTo
    {
        return $this->belongsTo(SwedenKommuner::class, 'kommun', 'kommun');
    }
}
