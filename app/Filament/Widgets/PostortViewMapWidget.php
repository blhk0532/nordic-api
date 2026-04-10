<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RatsitPostort;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use Filament\Support\Colors\Color;

class PostortViewMapWidget extends MapWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Sweden Postorter Map';

    protected int $defaultZoom = 8;

    protected int $mapHeight = 420;

    protected int|string|array $columnSpan = 'full';

    public ?string $postortName = null;

    public ?string $kommunName = null;

    public int|float|string|null $postortLatitude = null;

    public int|float|string|null $postortLongitude = null;

    protected string $view = 'filament.widgets.map-widget';

    public function getHeading(): ?string
    {
        if ($this->postortName !== null && $this->postortName !== '') {
            return "{$this->postortName} Postort";
        }

        return 'Postort karta';
    }

    protected function getMarkers(): array
    {
        $postortMarker = $this->getCurrentPostortMarker();
        $relatedPostorterMarkers = $this->getRelatedPostorterMarkers();

        return array_values(array_filter([
            $postortMarker,
            ...$relatedPostorterMarkers,
        ]));
    }

    protected function getMapCenter(): array
    {
        $latitude = $this->parseCoordinate($this->postortLatitude);
        $longitude = $this->parseCoordinate($this->postortLongitude);

        if ($latitude === null || $longitude === null) {
            return parent::getMapCenter();
        }

        return [$latitude, $longitude];
    }

    protected function getCurrentPostortMarker(): ?Marker
    {
        $latitude = $this->parseCoordinate($this->postortLatitude);
        $longitude = $this->parseCoordinate($this->postortLongitude);

        if ($latitude === null || $longitude === null) {
            return null;
        }

        $postortName = $this->postortName !== null && $this->postortName !== ''
            ? $this->postortName
            : 'Postort';

        $kommunName = $this->kommunName !== null && $this->kommunName !== ''
            ? $this->kommunName
            : 'Okänd kommun';

        return Marker::make($latitude, $longitude)
            ->title("Postort: {$postortName}")
            ->popupContent("{$postortName}<br>Kommun: {$kommunName}")
            ->color(Color::Red);
    }

    protected function getRelatedPostorterMarkers(): array
    {
        $kommunName = trim((string) $this->kommunName);

        if ($kommunName === '') {
            return [];
        }

        $normalizedKommun = mb_strtolower($kommunName);
        $currentPostortName = mb_strtolower(trim((string) $this->postortName));
        $like = '%'.$normalizedKommun.'%';

        $postorter = RatsitPostort::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($query) use ($like): void {
                $query->whereRaw('LOWER(kommun) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(personer_kommun) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(foretag_kommun) LIKE ?', [$like]);
            })
            ->selectRaw('postnummer, postort, latitude, longitude, SUM(personer) as personer_count, SUM(foretag) as foretag_count')
            ->groupBy('postnummer', 'postort', 'latitude', 'longitude')
            ->get();

        $markers = [];

        foreach ($postorter as $postort) {
            if ($currentPostortName !== '' && mb_strtolower((string) $postort->postort) === $currentPostortName) {
                continue;
            }

            $markers[] = Marker::make((float) $postort->latitude, (float) $postort->longitude)
                ->title($postort->postnummer.' - '.$postort->postort)
                ->popupContent($postort->postnummer.' '.$postort->postort.'<br>Personer: '.number_format((int) $postort->personer_count).'<br>Företag: '.number_format((int) $postort->foretag_count))
                ->color(Color::Blue);
        }

        return $markers;
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
