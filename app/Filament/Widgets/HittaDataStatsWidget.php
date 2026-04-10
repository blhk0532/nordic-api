<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\HittaData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HittaDataStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        // Count records with phone (check actual telefon field, not just is_telefon flag)
        $connection = (new HittaData)->getConnectionName() ?: config('database.default');
        $driver = config('database.connections.'.$connection.'.driver');
        $telefonCount = HittaData::where(function ($query) use ($driver) {
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
        })->count();

        // Count records with house data (check bostadstyp or bostadspris)
        $houseCount = HittaData::where(function ($query) {
            $query->whereNotNull('bostadstyp')
                ->orWhereNotNull('bostadspris');
        })->count();

        // Count records with both phone and house
        $telAndHouseCount = HittaData::where(function ($query) {
            $query->whereNotNull('telefon')
                ->where('telefon', '!=', '')
                ->where('telefon', '!=', '[]');
        })->where(function ($query) {
            $query->whereNotNull('bostadstyp')
                ->orWhereNotNull('bostadspris');
        })->count();

        $totalCount = HittaData::count();

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
