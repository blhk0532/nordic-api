<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MapPin;
use App\Models\RatsitPostort;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use Filament\Support\Colors\Color;
use Livewire\Attributes\On;

class GeoMapWidget extends MapWidget
{
    public bool $refresh = false;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Sweden Map';

    protected array $mapCenter = [60.1282, 18.6435];

    protected int $defaultZoom = 5;

    protected int $mapHeight = 500;

    public ?string $selectedKommun = null;

    protected string $view = 'filament.widgets.map-widget';

    #[On('show-postorter')]
    public function handleShowPostorter(string $kommun): void
    {
        $this->selectedKommun = $kommun;
        $this->heading = "Postnummer in {$kommun}";

        $this->refreshPins();
    }

    #[On('refresh-pins')]
    public function refreshPins(): void
    {
        // Force Livewire to re-render the view by toggling a public property
        $this->refresh = ! $this->refresh;

        // Reset the private marker cache in the base trait
        (fn () => $this->cachedLayerData = null)->call($this);

        // Debug log to confirm event received
        \Log::info('GeoMapWidget: refresh-pins/refresh-maps received. Pins count: '.MapPin::count());

        $this->refreshMap();
    }

    #[On('refresh-maps')]
    public function handleRefreshMaps(): void
    {
        $this->refreshPins();
    }

    #[On('clear-selection')]
    public function handleClearSelection(): void
    {
        $this->selectedKommun = null;
        $this->heading = 'Sweden Kommuner Map';
    }

    protected function getMarkers(): array
    {
        if ($this->selectedKommun) {
            return $this->getPostorterMarkers();
        }

        return $this->getKommunerMarkers();
    }

    protected function getKommunerMarkers(): array
    {
        $kommuner = MapPin::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $markers = [];
        foreach ($kommuner as $kommun) {
            $personerCount = is_numeric($kommun->name) ? (int) $kommun->name : 0;

            $markers[] = Marker::make($kommun->latitude, $kommun->longitude)
                ->id('pin-'.$kommun->id) // Use stable database ID instead of dynamic hash
                ->title($kommun->name ?? 'Unknown')
                ->popupContent(($kommun->name ?? 'Unknown'))
                ->color($this->getMarkerColor($personerCount));
        }

        return $markers;
    }

    protected function getPostorterMarkers(): array
    {
        $kommun = MapPin::whereRaw('LOWER(kommun) = ?', [strtolower($this->selectedKommun)])->first();

        if (! $kommun) {
            return [];
        }

        $postorter = RatsitPostort::whereRaw('LOWER(kommun) = ?', [strtolower($this->selectedKommun)])
            ->orWhereRaw('LOWER(personer_kommun) = ?', [strtolower($this->selectedKommun)])
            ->selectRaw('post_nummer, post_ort, SUM(personer) as personer_count, SUM(foretag) as foretag_count')
            ->groupBy('post_nummer', 'post_ort')
            ->get();

        $markers = [];
        $index = 0;
        $total = $postorter->count();

        foreach ($postorter as $postort) {
            $latOffset = (sin($index * 2 * M_PI / max($total, 1)) * 0.05);
            $lngOffset = (cos($index * 2 * M_PI / max($total, 1)) * 0.05);

            $markers[] = Marker::make(
                (float) $kommun->latitude + $latOffset,
                (float) $kommun->longitude + $lngOffset
            )
                ->title($postort->post_nummer.' - '.$postort->post_ort)
                ->popupContent($postort->post_nummer.' '.$postort->post_ort.'<br>Personer: '.number_format($postort->personer_count).'<br>Företag: '.number_format($postort->foretag_count))
                ->color(Color::Blue);
            $index++;
        }

        return $markers;
    }

    public function getHeading(): ?string
    {
        return 'Spararade Pins';
    }

    protected function getTotalKommuner(): int
    {
        return MapPin::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();
    }

    protected function getTotalPersoner(): int
    {
        return (int) MapPin::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->sum('id');
    }

    public function getMarkerColor(int $personerCount): array
    {
        if ($personerCount > 100000) {
            return Color::Red;
        }
        if ($personerCount > 50000) {
            return Color::Orange;
        }
        if ($personerCount > 20000) {
            return Color::Gray;
        }

        return Color::Blue;
    }
}
