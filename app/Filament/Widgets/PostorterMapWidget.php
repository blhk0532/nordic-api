<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitKommun;
use App\Models\RatsitPostort;
use App\Models\SwedenKommuner;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use Filament\Support\Colors\Color;
use Livewire\Attributes\On;

class PostorterMapWidget extends MapWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Sweden Postorter Map';

    protected array $mapCenter = [62.5333, 16.6667];

    protected int $defaultZoom = 5;

    protected int $mapHeight = 690;

    public ?string $selectedKommun = 'Gävle';

    protected string $view = 'filament.widgets.map-widget';

    public function getHeading(): ?string
    {
        if ($this->selectedKommun !== null && $this->selectedKommun !== '') {
            return "Postorter {$this->selectedKommun}";
        }

        return 'Välj en kommun för att visa postorter';
    }

    #[On('show-postorter')]
    public function handleShowPostorter(string $kommun): void
    {
        $this->selectedKommun = $kommun;
        $this->heading = "Postnummer i {$kommun}";
        $this->dispatch('refresh-map');
    }

    #[On('clear-selection')]
    public function handleClearSelection(): void
    {
        $this->selectedKommun = 'Gävle';
        $this->heading = 'Postorter i Gävle';
        $this->dispatch('refresh-map');
    }

    protected function getMarkers(): array
    {
        if (! $this->selectedKommun) {
            return [];
        }

        return $this->getPostorterMarkersForKommun();
    }

    protected function getPostorterMarkersForKommun(): array
    {
        $selectedKommun = (string) $this->selectedKommun;
        $normalizedKommun = strtolower(trim($selectedKommun));
        $baseKommun = strtolower(trim(strtok($selectedKommun, '-')));
        $baseKommun = rtrim($baseKommun, 's');

        $kommun = SwedenKommuner::query()
            ->whereRaw('LOWER(kommun) = ?', [$normalizedKommun])
            ->orWhereRaw('LOWER(kommun) = ?', [$baseKommun])
            ->orWhereRaw('LOWER(kommun) LIKE ?', ['%'.$baseKommun.'%'])
            ->first();

        $kommunCenter = RatsitKommun::query()
            ->whereRaw('LOWER(kommun) = ?', [$normalizedKommun])
            ->orWhereRaw('LOWER(kommun) LIKE ?', ['%'.$normalizedKommun.'%'])
            ->orWhereRaw('LOWER(kommun) LIKE ?', ['%'.$baseKommun.'%'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        $centerLat = $kommun?->latitude ?? $kommunCenter?->latitude;
        $centerLng = $kommun?->longitude ?? $kommunCenter?->longitude;

        if ($centerLat === null || $centerLng === null) {
            return [];
        }

        $like = '%'.$normalizedKommun.'%';
        $baseLike = '%'.$baseKommun.'%';

        $postorter = RatsitPostort::whereRaw('LOWER(personer_kommun) LIKE ? OR LOWER(foretag_kommun) LIKE ? OR LOWER(kommun) LIKE ?', [$like, $like, $like])
            ->orWhereRaw('LOWER(personer_kommun) LIKE ? OR LOWER(foretag_kommun) LIKE ? OR LOWER(kommun) LIKE ?', [$baseLike, $baseLike, $baseLike])
            ->where('personer', '>', 0)
            ->selectRaw('postnummer, postort, SUM(personer) as personer_count, SUM(foretag) as foretag_count')
            ->groupBy('postnummer', 'postort')
            ->get();

        $markers = [];
        $index = 0;
        $total = $postorter->count();

        foreach ($postorter as $postort) {
            $latOffset = sin($index * 2 * M_PI / max($total, 1)) * 0.1;
            $lngOffset = cos($index * 2 * M_PI / max($total, 1)) * 0.1;

            $markers[] = Marker::make(
                (float) $centerLat + $latOffset,
                (float) $centerLng + $lngOffset
            )
                ->title($postort->postnummer.' - '.$postort->postort)
                ->popupContent($postort->postnummer.' '.$postort->postort.'<br>Personer: '.number_format($postort->personer_count).'<br>Företag: '.number_format($postort->foretag_count))
                ->color(Color::Blue);
            $index++;
        }

        return $markers;
    }
}
