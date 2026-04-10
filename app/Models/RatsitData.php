<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SwedishDateCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $gatuadress
 * @property string|null $postnummer
 * @property string|null $postort
 * @property string|null $forsamling
 * @property string|null $kommun
 * @property string|null $lan
 * @property string|null $adressandring
 * @property array $telfonnummer
 * @property string|null $stjarntacken
 * @property \Carbon\Carbon|null $fodelsedag
 * @property string|null $personnummer
 * @property string|null $alder
 * @property string|null $kon
 * @property string|null $civilstand
 * @property string|null $fornamn
 * @property string|null $efternamn
 * @property string|null $personnamn
 * @property string|null $telefon
 * @property array<array-key, mixed>|null $epost_adress
 * @property string|null $agandeform
 * @property string|null $bostadstyp
 * @property string|null $boarea
 * @property string|null $byggar
 * @property string|null $fastighet
 * @property array<array-key, mixed>|null $personer
 * @property array<array-key, mixed>|null $foretag
 * @property array<array-key, mixed>|null $grannar
 * @property array<array-key, mixed>|null $fordon
 * @property array<array-key, mixed>|null $hundar
 * @property array<array-key, mixed>|null $bolagsengagemang
 * @property numeric|null $longitude
 * @property numeric|null $latitud
 * @property string|null $google_maps
 * @property string|null $google_streetview
 * @property string|null $ratsit_se
 * @property bool $is_active
 * @property bool $is_hus
 * @property bool $is_telefon
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string|null $kommun_ratsit
 * @property bool $is_queued
 *
 * @method static Builder<static>|RatsitData active()
 * @method static Builder<static>|RatsitData newModelQuery()
 * @method static Builder<static>|RatsitData newQuery()
 * @method static Builder<static>|RatsitData query()
 * @method static Builder<static>|RatsitData whereAdressandring($value)
 * @method static Builder<static>|RatsitData whereAgandeform($value)
 * @method static Builder<static>|RatsitData whereAlder($value)
 * @method static Builder<static>|RatsitData whereBoarea($value)
 * @method static Builder<static>|RatsitData whereBolagsengagemang($value)
 * @method static Builder<static>|RatsitData whereBostadstyp($value)
 * @method static Builder<static>|RatsitData whereByggar($value)
 * @method static Builder<static>|RatsitData whereCivilstand($value)
 * @method static Builder<static>|RatsitData whereCreatedAt($value)
 * @method static Builder<static>|RatsitData whereEfternamn($value)
 * @method static Builder<static>|RatsitData whereEpostAdress($value)
 * @method static Builder<static>|RatsitData whereFastighet($value)
 * @method static Builder<static>|RatsitData whereFodelsedag($value)
 * @method static Builder<static>|RatsitData whereFordon($value)
 * @method static Builder<static>|RatsitData whereForetag($value)
 * @method static Builder<static>|RatsitData whereFornamn($value)
 * @method static Builder<static>|RatsitData whereForsamling($value)
 * @method static Builder<static>|RatsitData whereGatuadress($value)
 * @method static Builder<static>|RatsitData whereGoogleMaps($value)
 * @method static Builder<static>|RatsitData whereGoogleStreetview($value)
 * @method static Builder<static>|RatsitData whereGrannar($value)
 * @method static Builder<static>|RatsitData whereHundar($value)
 * @method static Builder<static>|RatsitData whereId($value)
 * @method static Builder<static>|RatsitData whereIsActive($value)
 * @method static Builder<static>|RatsitData whereIsHus($value)
 * @method static Builder<static>|RatsitData whereIsQueued($value)
 * @method static Builder<static>|RatsitData whereIsTelefon($value)
 * @method static Builder<static>|RatsitData whereKommun($value)
 * @method static Builder<static>|RatsitData whereKommunRatsit($value)
 * @method static Builder<static>|RatsitData whereKon($value)
 * @method static Builder<static>|RatsitData whereLan($value)
 * @method static Builder<static>|RatsitData whereLatitud($value)
 * @method static Builder<static>|RatsitData whereLongitude($value)
 * @method static Builder<static>|RatsitData wherePersoner($value)
 * @method static Builder<static>|RatsitData wherePersonnamn($value)
 * @method static Builder<static>|RatsitData wherePersonnummer($value)
 * @method static Builder<static>|RatsitData wherePostnummer($value)
 * @method static Builder<static>|RatsitData wherePostort($value)
 * @method static Builder<static>|RatsitData whereRatsitSe($value)
 * @method static Builder<static>|RatsitData whereStjarntacken($value)
 * @method static Builder<static>|RatsitData whereTelefon($value)
 * @method static Builder<static>|RatsitData whereTelfonnummer($value)
 * @method static Builder<static>|RatsitData whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RatsitData extends Model
{
    /** @use HasFactory<\Database\Factories\RatsitDataFactory> */
    use HasFactory;

    protected $table = 'ratsit_data';

    protected static bool $isScopedToTenant = false;

    protected $guarded = [];

    protected $casts = [
        'fodelsedag' => SwedishDateCast::class,
        'telefon' => 'string',
        'telfonnummer' => 'array',
        'epost_adress' => 'array',
        'bolagsengagemang' => 'array',
        'personer' => 'array',
        'foretag' => 'array',
        'grannar' => 'array',
        'fordon' => 'array',
        'hundar' => 'array',
        'is_active' => 'boolean',
        'is_hus' => 'boolean',
        'is_telefon' => 'boolean',
        'is_queued' => 'boolean',
        'longitude' => 'decimal:7',
        'latitud' => 'decimal:7',
    ];

    protected $fillable = [
        'gatuadress',
        'postnummer',
        'postort',
        'forsamling',
        'kommun',
        'kommun_ratsit',
        'lan',
        'adressandring', // Date of address change from Ratsit
        'fodelsedag',
        'personnummer',
        'stjarntacken', // Zodiac sign
        'alder',
        'kon',
        'civilstand',
        'fornamn',
        'efternamn',
        'personnamn',
        'telefon',
        'telfonnummer',
        'epost_adress',
        'bolagsengagemang',
        'agandeform',
        'bostadstyp',
        'boarea',
        'byggar',
        'fastighet',
        'personer',
        'foretag',
        'grannar',
        'fordon',
        'hundar',
        'longitude',
        'latitud',
        'google_maps', // Google Maps navigation URL
        'google_streetview', // Google Street View URL
        'ratsit_se', // Source profile URL
        'is_active',
        'is_telefon',
        'is_hus',
        'is_queued',
    ];

    /** @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Ensure telfonnummer is always returned as an array.
     * Accepts stored JSON arrays or pipe-delimited strings and normalizes to array.
     *
     * @param  mixed  $value
     */
    public function getTelfonnummerAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Fallback: pipe-delimited values -> convert to array
            $parts = array_filter(array_map('trim', explode('|', $value)));

            return array_values($parts);
        }

        // Any other type, cast to array
        return (array) $value;
    }

    /**
     * Normalize and store telfonnummer as JSON array.
     * Accepts array, JSON string, or pipe-delimited string.
     *
     * @param  mixed  $value
     */
    public function setTelfonnummerAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['telfonnummer'] = json_encode(array_values($value));

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->attributes['telfonnummer'] = json_encode(array_values($decoded));

                return;
            }

            $parts = array_filter(array_map('trim', explode('|', $value)));
            $this->attributes['telfonnummer'] = json_encode(array_values($parts));

            return;
        }

        // Fallback: castable values
        $this->attributes['telfonnummer'] = json_encode(array_values((array) $value));
    }
}
