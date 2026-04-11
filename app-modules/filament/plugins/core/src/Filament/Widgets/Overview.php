<?php

namespace Cachet\Filament\Widgets;

use App\Models\User;
use Cachet\Models\Component;
use Cachet\Models\Incident;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class Overview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('total_incidents', Incident::count())
                ->label(__('Scheman'))
                ->description(__(''))
                ->chart(DB::table('incidents')->selectRaw('count(*) as total')->groupByRaw('date(created_at)')->pluck('total')->toArray())
                ->icon('cachet-incident')
                ->chartColor('info')
                ->color('gray'),

            Stat::make('total_users', User::count())
                ->label(__('Users TEAM'))
                ->description(__(''))
                ->chart(DB::table('users')->selectRaw('count(*) as total')->groupByRaw('date(created_at)')->pluck('total')->toArray())
                ->icon('cachet-metrics')
                ->chartColor('info')
                ->color('gray'),

            Stat::make('components', Component::count())
                ->label(__('Tekniker'))
                ->description(__(''))
                ->chart(DB::table('components')->selectRaw('count(*) as total')->groupByRaw('date(created_at)')->pluck('total')->toArray())
                ->icon('heroicon-o-user-circle')
                ->chartColor('info')
                ->color('gray'),
        ];
    }
}
