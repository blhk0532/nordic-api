<?php

declare(strict_types=1);

namespace App\Models;

use Filament\AdvancedExport\Contracts\Exportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwedenPersoner extends Model implements Exportable
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

    public function getHushallMedlemmarAttribute(): array
    {
        $medlemmar = [];

        if (! empty($this->ratsit_data['hushåll'])) {
            foreach ($this->ratsit_data['hushåll'] as $person) {
                if (($person['namn'] ?? '') !== $this->personnamn) {
                    $medlemmar[] = [
                        'namn' => $person['namn'] ?? '',
                        'alder' => $person['ålder'] ?? $person['alder'] ?? '',
                    ];
                }
            }
        }

        return $medlemmar;
    }

    public static function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'fornamn' => 'Förnamn',
            'efternamn' => 'Efternamn',
            'personnamn' => 'Fullständigt namn',
            'personnummer' => 'Personnummer',
            'alder' => 'Ålder',
            'kon' => 'Kön',
            'civilstand' => 'Civilstånd',
            'telefon' => 'Telefon',
            'adress' => 'Adress',
            'postnummer' => 'Postnummer',
            'postort' => 'Postort',
            'kommun' => 'Kommun',
            'lan' => 'Län',
            'bostadstyp' => 'Bostadstyp',
            'agandeform' => 'Ägandeform',
            'boarea' => 'Boarea',
            'byggar' => 'Byggår',
            'personer' => 'Antal boende',
            'is_hus' => 'Är hus',
            'is_owner' => 'Är ägare',
            'ratsit_link' => 'Ratsit Länk',
            'hitta_link' => 'Hitta Länk',
            'merinfo_link' => 'Merinfo Länk',
            'eniro_link' => 'Eniro Länk',
            'upplysning_link' => 'Upplysning Länk',
            'mrkoll_link' => 'Mrkoll Länk',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'created_at' => 'Skapad',
            'updated_at' => 'Uppdaterad',
        ];
    }

    public static function getDefaultExportColumns(): array
    {
        return [
            ['field' => 'fornamn', 'title' => 'Förnamn'],
            ['field' => 'efternamn', 'title' => 'Efternamn'],
            ['field' => 'personnummer', 'title' => 'Personnummer'],
            ['field' => 'kon', 'title' => 'Kön'],
            ['field' => 'adress', 'title' => 'Adress'],
            ['field' => 'telefon', 'title' => 'Telefon'],
            ['field' => 'postort', 'title' => 'Postort'],
            ['field' => 'postnummer', 'title' => 'Postnummer'],
        ];
    }
}
