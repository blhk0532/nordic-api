<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SwedenKommuner;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class KommunerMapWidgetDb extends MapWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = ' ';

    protected array $mapCenter = [62.5333, 16.6667];

    protected int $defaultZoom = 5;

    protected int $mapHeight = 655;

    protected string $view = 'filament.widgets.map-widget';

    public function getHeading(): ?string
    {
        return 'Kommuner DB';
    }

    #[On('show-postorter')]
    public function handleShowPostorter(string $kommun): void
    {
        $this->dispatch('refresh-map');
    }

    #[On('clear-selection')]
    public function handleClearSelection(): void
    {
        $this->dispatch('refresh-map');
    }

    protected function getMarkers(): array
    {
        return $this->getKommunerMarkers();
    }

    protected function getKommunerMarkers(): array
    {
        $personCounts = DB::table('sweden_personer')
            ->select('kommun')
            ->selectRaw('COUNT(id) as total')
            ->groupBy('kommun');

        $rows = DB::table('sweden_kommuner as sk')
            ->joinSub($personCounts, 'sp_counts', 'sk.kommun', '=', 'sp_counts.kommun')
            ->whereNotNull('sk.latitude')
            ->whereNotNull('sk.longitude')
            ->select('sk.kommun', 'sk.latitude', 'sk.longitude', 'sp_counts.total')
            ->get();

        $markers = [];
        foreach ($rows as $row) {
            $count = (int) $row->total;
            $kommunName = (string) $row->kommun;

            $markers[] = Marker::make((float) $row->latitude, (float) $row->longitude)
                ->title($kommunName.' - '.number_format($count).' personer')
                ->popupContent($kommunName.': '.number_format($count).' personer')
                ->onClick(function () use ($kommunName): void {
                    $this->dispatch('show-postorter', kommun: $kommunName);
                })
                ->color($this->getMarkerColor($count));
        }

        return $markers;
    }

    protected function getTotalKommuner(): int
    {
        return SwedenKommuner::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();
    }

    protected function getTotalPersoner(): int
    {
        return (int) DB::table('sweden_personer')->count();
    }

    protected function getMarkerColor(int $personerCount): array
    {
        if ($personerCount > 200000) {
            return Color::Red;
        }
        if ($personerCount > 100000) {
            return Color::Pink;
        }
        if ($personerCount > 80000) {
            return Color::Orange;
        }
        if ($personerCount > 60000) {
            return Color::Cyan;
        }
        if ($personerCount > 50000) {
            return Color::Pink;
        }
        if ($personerCount > 40000) {
            return Color::Violet;
        }
        if ($personerCount > 30000) {
            return Color::Blue;
        }
        if ($personerCount > 20000) {
            return Color::Indigo;
        }
        if ($personerCount > 10000) {
            return Color::Sky;
        }
        if ($personerCount > 8000) {
            return Color::Gray;
        }
        if ($personerCount > 3000) {
            return Color::Gray;
        }

        return Color::Gray;
    }
}
