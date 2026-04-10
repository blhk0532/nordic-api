<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RatsitDataStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $connection = (new RatsitData)->getConnectionName() ?: config('database.default');
        $driver = config('database.connections.'.$connection.'.driver');
        // Count records with phone (check actual telefon field, not just is_telefon flag)
        $telefonCount = RatsitData::where(function ($query) use ($driver) {
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

        // Count records with house data (check bostadstyp, agandeform, or boarea)
        $houseCount = RatsitData::where(function ($query) {
            $query->whereNotNull('bostadstyp')
                ->orWhereNotNull('agandeform')
                ->orWhereNotNull('boarea');
        })->count();

        // Count records with both phone and house
        $telAndHouseCount = RatsitData::where(function ($query) use ($driver) {
            $query->where(function ($q) use ($driver) {
                if ($driver === 'pgsql') {
                    $q->whereNotNull('telefon')
                        ->whereRaw('telefon::text <> ?', [''])
                        ->whereRaw('telefon::text <> ?', ['[]'])
                        ->whereRaw('telefon::text <> ?', ['{}']);
                } else {
                    $q->whereNotNull('telefon')
                        ->where('telefon', '!=', '')
                        ->where('telefon', '!=', '[]');
                }
            })->orWhere(function ($q) use ($driver) {
                if ($driver === 'pgsql') {
                    $q->whereNotNull('telfonnummer')
                        ->whereRaw('telfonnummer::text <> ?', ['[]'])
                        ->whereRaw('telfonnummer::text <> ?', ['""'])
                        ->whereRaw('telfonnummer::text <> ?', ['{}']);
                } else {
                    $q->whereNotNull('telfonnummer')
                        ->where('telfonnummer', '!=', '[]')
                        ->where('telfonnummer', '!=', '""');
                }
            });
        })->where(function ($query) {
            $query->whereNotNull('bostadstyp')
                ->orWhereNotNull('agandeform')
                ->orWhereNotNull('boarea');
        })->count();

        $totalCount = RatsitData::count();

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
