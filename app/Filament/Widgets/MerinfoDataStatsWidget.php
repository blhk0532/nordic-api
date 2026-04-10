<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MerinfoData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MerinfoDataStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $connection = (new MerinfoData)->getConnectionName() ?: config('database.default');
        $driver = config('database.connections.'.$connection.'.driver');

        $telefonScope = function (Builder $query) use ($driver): void {
            $query->whereNotNull('telefon');

            if ($driver === 'pgsql') {
                $query->whereRaw('telefon::text <> ?', ['[]'])
                    ->whereRaw('telefon::text <> ?', ['""'])
                    ->whereRaw('telefon::text <> ?', ['{}']);

                return;
            }

            $query->where('telefon', '!=', '[]')
                ->where('telefon', '!=', '""')
                ->where('telefon', '!=', '{}');
        };

        $houseScope = function (Builder $query): void {
            $query->whereNotNull('bostadstyp')
                ->orWhereNotNull('bostadspris');
        };

        $telefonCount = MerinfoData::where($telefonScope)->count();

        $houseCount = MerinfoData::where($houseScope)->count();

        $telAndHouseCount = MerinfoData::where($telefonScope)
            ->where($houseScope)
            ->count();

        $totalCount = MerinfoData::count();

        return [
            Stat::make('Telefon', number_format($telefonCount))
                ->description('Records with phone')
                ->color('success')
                ->icon('heroicon-o-phone'),

            Stat::make('House', number_format($houseCount))
                ->description('Records with house')
                ->color('primary')
                ->icon('heroicon-o-home'),

            Stat::make('Tel & Hus', number_format($telAndHouseCount))
                ->description('Records with both')
                ->color('warning')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Total', number_format($totalCount))
                ->description('Total records')
                ->color('gray')
                ->icon('heroicon-o-document-text'),
        ];
    }
}
