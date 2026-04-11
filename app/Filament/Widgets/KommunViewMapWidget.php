<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitPostort;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class KommunViewMapWidget extends MapWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Sweden Kommuner Map';

    protected int $defaultZoom = 7;

    protected int $mapHeight = 660;

    protected int|string|array $columnSpan = '1/2';

    public ?string $kommunName = null;

    public int|float|string|null $kommunLatitude = null;

    public int|float|string|null $kommunLongitude = null;

    public int|float|string|null $kommunPersoner = null;

    protected string $view = 'filament.widgets.map-widget';

    public function getHeading(): ?string
    {
        if ($this->kommunName !== null && $this->kommunName !== '') {
            return "{$this->kommunName} Kommun";
        }

        return 'Kommun karta';
    }

    protected function getMarkers(): array
    {
        $kommunMarker = $this->getCurrentKommunMarker();
        $postorterMarkers = $this->getPostorterMarkersForCurrentKommun();

        return array_values(array_filter([
            $kommunMarker,
            ...$postorterMarkers,
        ]));
    }

    protected function getMapCenter(): array
    {
        $latitude = $this->parseCoordinate($this->kommunLatitude);
        $longitude = $this->parseCoordinate($this->kommunLongitude);

        if ($latitude !== null && $longitude !== null) {
            return [$latitude, $longitude];
        }

        $firstPostort = $this->getRelatedPostorterQuery()
            ->select('latitude', 'longitude')
            ->first();

        if ($firstPostort !== null) {
            return [(float) $firstPostort->latitude, (float) $firstPostort->longitude];
        }

        return parent::getMapCenter();
    }

    protected function getCurrentKommunMarker(): ?Marker
    {
        $latitude = $this->parseCoordinate($this->kommunLatitude);
        $longitude = $this->parseCoordinate($this->kommunLongitude);

        if ($latitude === null || $longitude === null) {
            return null;
        }

        $kommunName = $this->kommunName !== null && $this->kommunName !== ''
            ? $this->kommunName
            : 'Kommun';

        return Marker::make($latitude, $longitude)
            ->title("Kommun: {$kommunName}")
            ->tooltipContent('Personer: '.number_format((int) ($this->kommunPersoner ?? 0)))
            ->popupContent("{$kommunName}")
            ->color(Color::Red);
    }

    protected function getPostorterMarkersForCurrentKommun(): array
    {
        $postorter = $this->getRelatedPostorterQuery()
            ->selectRaw('postnummer, postort, latitude, longitude, SUM(personer) as personer_count, SUM(foretag) as foretag_count')
            ->groupBy('postnummer', 'postort', 'latitude', 'longitude')
            ->get();

        $markers = [];

        foreach ($postorter as $postort) {
            $markers[] = Marker::make((float) $postort->latitude, (float) $postort->longitude)
                ->title($postort->postnummer.' - '.$postort->postort)
                ->tooltipContent('Personer: '.number_format((int) $postort->personer_count))
                ->popupContent($postort->postnummer.' '.$postort->postort.'<br>Personer: '.number_format((int) $postort->personer_count).'<br>Företag: '.number_format((int) $postort->foretag_count))
                ->color(Color::Blue);
        }

        return $markers;
    }

    protected function getRelatedPostorterQuery(): Builder
    {
        $searchTerms = $this->getKommunSearchTerms();

        return RatsitPostort::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($searchTerms !== [], function ($query) use ($searchTerms): void {
                $query->where(function ($query) use ($searchTerms): void {
                    foreach ($searchTerms as $term) {
                        $like = '%'.$term.'%';

                        $query->orWhereRaw('LOWER(kommun) LIKE ?', [$like]);
                    }
                });
            });
    }

    protected function getKommunSearchTerms(): array
    {
        $normalizedKommun = mb_strtolower(trim((string) $this->kommunName));

        if ($normalizedKommun === '') {
            return [];
        }

        $baseKommun = preg_replace('/\s+kommun$/u', '', $normalizedKommun) ?? $normalizedKommun;
        $baseKommun = trim($baseKommun);

        return array_values(array_unique(array_filter([
            $normalizedKommun,
            $baseKommun,
        ])));
    }

    protected function parseCoordinate(int|float|string|null $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalizedValue = is_string($value)
            ? str_replace(',', '.', trim($value))
            : $value;

        if (! is_numeric($normalizedValue)) {
            return null;
        }

        return (float) $normalizedValue;
    }
}
