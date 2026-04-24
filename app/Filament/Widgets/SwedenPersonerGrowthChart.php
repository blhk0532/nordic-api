<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SwedenPersoner;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class SwedenPersonerGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Sweden Personer Growth';

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

        $records = SwedenPersoner::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get()
            ->groupBy(fn (SwedenPersoner $record): string => $record->created_at?->format('Y-m-d') ?? '')
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
                    'label' => 'New Personer',
                    'data' => $data,
                    'fill' => 'start',
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
