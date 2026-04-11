<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class LocationMapPickerWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'filament.queue.widgets.location-map-picker-widget';

    protected int|string|array $columnSpan = '1/2';

    protected static ?int $sort = 1;

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
            ->extraAttributes(['class' => 'pb-0 mb-0'])
            ->schema([
                Grid::make()

                    ->schema([
                        Map::make(' ')
                            ->mapControls([
                                'mapTypeControl' => true,
                                'scaleControl' => true,
                                'streetViewControl' => true,
                                'rotateControl' => true,
                                'fullscreenControl' => true,
                                'searchBoxControl' => true,
                                'zoomControl' => true,
                            ])
                            ->height('450px')
                            ->defaultZoom(8)
                            ->autocomplete('address_search')
                            ->autocompleteReverse(true)
                            ->reverseGeocode([
                                'street' => '%S %n',
                                'city' => '%L',
                                'state' => '%A1',
                                'zip' => '%z',
                                'country' => '%c',
                                //    'latitude' => 'latitude',
                                //    'longitude' => 'longitude',
                            ])
                            ->debug(true)
                            ->defaultLocation([60.1282, 18.6435])
                            ->draggable(true)
                            ->clickable(true)
                            ->geolocate(true)
                            ->geolocateLabel('Get My Location')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('street')
                            ->label('...')
                            ->hidden()
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->readOnly(),
                        TextInput::make('zip')
                            ->label('...')
                            ->columnSpan(1)
                            ->maxLength(50)
                            ->readOnly(),
                        TextInput::make('city')
                            ->label('...')
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->readOnly(),
                        TextInput::make('state')
                            ->label('...')
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->readOnly(),
                        //    TextInput::make('latitude')
                        //        ->label('...')
                        //        ->maxLength(255)
                        //        ->columnSpan(1)
                        //        ->readOnly(),
                        //    TextInput::make('longitude')
                        //        ->label('...')
                        //        ->maxLength(255)
                        //        ->columnSpan(1)
                        //        ->readOnly(),
                        TextInput::make('country')
                            ->label('...')
                            ->maxLength(255)
                            ->columnSpan(1)
                            ->readOnly(),
                        TextInput::make('address_search')
                            ->label('Sök adress')
                            ->extraAttributes(['class' => 'sok-adress'])
                            ->placeholder('Search by street, city, or postal code')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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
