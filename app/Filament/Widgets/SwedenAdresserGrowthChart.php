<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SwedenAdresser;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class SwedenAdresserGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Sweden Adresser Growth';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = filled($this->pageFilters['startDate'] ?? null)
            ? Carbon::parse($this->pageFilters['startDate'])->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();
        $endDate = filled($this->pageFilters['endDate'] ?? null)
            ? Carbon::parse($this->pageFilters['endDate'])->endOfDay()
            : now();

        $days = collect();
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $days->push($cursor->copy());
            $cursor->addDay();
        }

        $records = SwedenAdresser::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get()
            ->groupBy(fn (SwedenAdresser $record): string => $record->created_at?->format('Y-m-d') ?? '')
            ->map(fn ($group) => $group->count());

        $labels = [];
        $data = [];

        foreach ($days as $day) {
            $labels[] = $day->format('d M');
            $data[] = $records->get($day->format('Y-m-d'), 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Adresser',
                    'data' => $data,
                    'fill' => 'start',
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
