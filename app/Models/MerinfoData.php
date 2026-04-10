<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $personnamn
 * @property string|null $alder
 * @property string|null $kon
 * @property string|null $gatuadress
 * @property string|null $postnummer
 * @property string|null $postort
 * @property string|null $telefon
 * @property array<array-key, mixed>|null $telefonnummer
 * @property array<array-key, mixed>|null $telefoner
 * @property string|null $karta
 * @property string|null $link
 * @property string|null $bostadstyp
 * @property string|null $bostadspris
 * @property bool $is_active
 * @property bool $is_telefon
 * @property bool $is_ratsit
 * @property bool $is_hus
 * @property int|null $merinfo_personer_total
 * @property int|null $merinfo_foretag_total
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property int|null $merinfo_personer_count
 * @property int|null $merinfo_personer_queue
 * @property-read string $telefon_preview
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereAlder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereBostadspris($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereBostadstyp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereGatuadress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereIsHus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereIsRatsit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereIsTelefon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereKarta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereKon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereMerinfoForetagTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereMerinfoPersonerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereMerinfoPersonerQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereMerinfoPersonerTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData wherePersonnamn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData wherePostnummer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData wherePostort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereTelefon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereTelefoner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereTelefonnummer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MerinfoData whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MerinfoData extends Model
{
    // Use default connection; previously forced 'sqlite' which broke API tests under mysql.

    protected $table = 'merinfo_data';

    protected $fillable = [
        'personnamn',
        'givenNameOrFirstName',
        'personalNumber',
        'alder',
        'kon',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'telefonnummer',
        'telefoner',
        'karta',
        'link',
        'bostadstyp',
        'bostadspris',
        'is_active',
        'is_telefon',
        'is_ratsit',
        'is_hus',
        'merinfo_personer_total',
        'merinfo_foretag_total',
        'merinfo_personer_saved',
        'merinfo_foretag_saved',
        'merinfo_personer_phone_total',
        'merinfo_foretag_phone_total',
        'merinfo_personer_phone_saved',
        'merinfo_foretag_phone_saved',
        'merinfo_personer_house_saved',
        'merinfo_foretag_house_saved',
        'merinfo_personer_count',
        'merinfo_personer_queue',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
        'is_hus' => 'boolean',
        'postnummer' => 'string',
        // telefon: no cast - accepts any data type (string, array, etc.)
        'telefonnummer' => 'string',
        'telefoner' => 'array',
        'merinfo_personer_total' => 'integer',
        'merinfo_foretag_total' => 'integer',
        'merinfo_personer_saved' => 'integer',
        'merinfo_foretag_saved' => 'integer',
        'merinfo_personer_phone_total' => 'integer',
        'merinfo_foretag_phone_saved' => 'integer',
        'merinfo_personer_house_saved' => 'integer',
        'merinfo_foretag_house_saved' => 'integer',
        'merinfo_personer_count' => 'integer',
        'merinfo_personer_queue' => 'integer',
    ];

    /**
     * Truncated preview of the telefon field for table display.
     * Returns an em dash when empty or placeholder.
     * Handles any data type: string, array, etc.
     */
    public function getTelefonPreviewAttribute(): string
    {
        $telefon = $this->telefon;

        // Handle different data types
        if (is_array($telefon)) {
            // Flatten nested arrays
            $phones = [];
            array_walk_recursive($telefon, function ($item) use (&$phones) {
                if (is_string($item) || is_numeric($item)) {
                    $phones[] = (string) $item;
                }
            });
            $phoneStr = implode(' | ', $phones);
        } else {
            $phoneStr = mb_trim(preg_replace('/\s+/', ' ', (string) $telefon));
        }

        if ($phoneStr === '' || $phoneStr === 'Lägg till telefonnummer') {
            return '—';
        }

        return mb_strlen($phoneStr) > 13 ? mb_substr($phoneStr, 0, 13).'…' : $phoneStr;
    }
}
