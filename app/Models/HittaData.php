<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property string|null $telefonnummer
 * @property string|null $karta
 * @property string|null $link
 * @property string|null $bostadstyp
 * @property string|null $bostadspris
 * @property bool $is_active
 * @property bool $is_telefon
 * @property bool $is_ratsit
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property bool $is_hus
 * @property array<array-key, mixed>|null $telefonnumer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereAlder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereBostadspris($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereBostadstyp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereGatuadress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereIsHus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereIsRatsit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereIsTelefon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereKarta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereKon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData wherePersonnamn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData wherePostnummer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData wherePostort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereTelefon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereTelefonnumer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereTelefonnummer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HittaData whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class HittaData extends Model
{
    use HasFactory;

    protected $table = 'hitta_data';

    protected $fillable = [
        'personnamn',
        'alder',
        'kon',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'telefonnummer',
        // legacy/DB column name is 'telefonnumer' (typo) - accept both and map via accessors
        'telefonnumer',
        'karta',
        'link',
        'bostadstyp',
        'bostadspris',
        'is_active',
        'is_telefon',
        'is_ratsit',
        'is_hus',
    ];

    /**
     * Virtual accessor to expose telefonnummer while the DB column is named telefonnumer.
     */
    public function getTelefonnummerAttribute()
    {
        return $this->getAttribute('telefonnumer');
    }

    /**
     * Virtual mutator so assigning ->telefonnummer will write to telefonnumer column.
     */
    public function setTelefonnummerAttribute($value)
    {
        $this->setAttribute('telefonnumer', $value);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_telefon' => 'boolean',
            'is_ratsit' => 'boolean',
            'is_hus' => 'boolean',
            // DB column is 'telefonnumer' (note spelling) — cast that column to array
            'telefonnumer' => 'array',
        ];
    }
}
