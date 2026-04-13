<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwedenPostnummer extends Model
{
    use HasFactory;

    protected $table = 'sweden_postnummer';

    /** @var array<int, string> */
    protected $fillable = [
        'postnummer', 'postort', 'kommun', 'lan', 'latitude', 'longitude', 'personer', 'foretag',
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

    public function scopeWithLiveCounts(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->addSelect("{$table}.*")
            ->selectSub(
                RatsitData::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('postnummer', "{$table}.postnummer"),
                'live_ratsit_count'
            )
            ->selectSub(
                HittaData::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('postnummer', "{$table}.postnummer"),
                'live_hitta_count'
            )
            ->selectSub(
                MerinfoData::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('postnummer', "{$table}.postnummer"),
                'live_merinfo_count'
            )
            ->selectSub(
                SwedenPersoner::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('postnummer', "{$table}.postnummer"),
                'live_personer_count'
            );
    }
}
