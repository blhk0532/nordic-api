<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SwedenPostnummer;
use Cheesegrits\FilamentGoogleMaps\Actions\GoToAction;
use Cheesegrits\FilamentGoogleMaps\Filters\MapIsFilter;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class MapPickerWidget extends MapTableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        return SwedenPostnummer::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('postnummer')
                ->label('Postnummer')
                ->searchable()
                ->sortable(),
            TextColumn::make('postort')
                ->label('Postort')
                ->searchable()
                ->sortable(),
            TextColumn::make('kommun')
                ->label('Kommun')
                ->searchable()
                ->sortable(),
            TextColumn::make('lan')
                ->label('Län')
                ->searchable(),
            TextColumn::make('personer')
                ->label('Personer')
                ->numeric(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('postort')
                ->label('Postort')
                ->options(
                    SwedenPostnummer::query()
                        ->distinct()
                        ->pluck('postort', 'postort')
                        ->toArray()
                ),
            SelectFilter::make('kommun')
                ->label('Kommun')
                ->searchable()
                ->options(
                    SwedenPostnummer::query()
                        ->distinct()
                        ->pluck('kommun', 'kommun')
                        ->toArray()
                ),
            SelectFilter::make('lan')
                ->label('Län')
                ->options(
                    SwedenPostnummer::query()
                        ->distinct()
                        ->pluck('lan', 'lan')
                        ->toArray()
                ),
            MapIsFilter::make('map')
                ->label('Map Bounds'),
        ];
    }

    protected function getFilters(): ?array
    {
        return null;
    }

    public function getConfig(): array
    {
        $config = parent::getConfig();

        return array_merge($config, [
            'center' => [
                'latitude' => 62.5333,
                'longitude' => 16.6667,
            ],
            'zoom' => 8,
            'fit' => true,
        ]);
    }

    protected function getTableActions(): array
    {
        return [
            GoToAction::make()
                ->label('Map')
                ->alpineClickHandler(function (Model $record): HtmlString {
                    $latLngFields = $record::getLatLngAttributes();

                    return new HtmlString(sprintf(
                        "const section = document.getElementById('filament-google-maps-widget-on-table'); if (section) { section.classList.remove('is-collapsed'); section.classList.remove('fi-collapsed'); } \$dispatch('filament-google-maps::widget/setMapCenter', {latitude: %f, longitude: %f, zoom: %d});",
                        round((float) $record->{$latLngFields['latitude']}, 8),
                        round((float) $record->{$latLngFields['longitude']}, 8),
                        12,
                    ));
                })
                ->zoom(12),
        ];
    }

    public function isMapPicker(): bool
    {
        return true;
    }

    protected function getMapFields(): array
    {
        return [
            'latitude',
            'longitude',
        ];
    }

    protected function getMapLabel(): string
    {
        return 'sverige';
    }

    public function mount(): void
    {
        $this->form->fill([
            'address_search' => null,
            'street' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'location' => [
                'latitude' => 62.5333,
                'longitude' => 16.6667,
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        TextInput::make('address_search')
                            ->label('Address Search')
                            ->placeholder('Search by street, city, or postal code')
                            ->maxLength(255)
                            ->columnSpanFull(),

                    ]),

            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Here you can dispatch an event or emit the selected location
        $this->dispatch('location-selected', [
            'latitude' => $data['location']['latitude'],
            'longitude' => $data['location']['longitude'],
        ])->self();
    }
}
