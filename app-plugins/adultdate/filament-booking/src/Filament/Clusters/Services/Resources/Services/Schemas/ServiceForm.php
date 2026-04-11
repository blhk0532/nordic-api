<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\Schemas;

use Adultdate\FilamentBooking\Models\Booking\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('price')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),
                                TextInput::make('service_code')
                                    ->label('Service Code')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Service::class, 'service_code', ignoreRecord: true),
                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Service::class, 'slug', ignoreRecord: true),

                                RichEditor::make('description')
                                    ->columnSpan('full'),
                            ])
                            ->columns(2),

                        Section::make('Images')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('booking_media')
                                    ->collection('service-images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->acceptedFileTypes(['image/jpeg'])
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visibility')
                                    ->helperText('This service will be hidden from all sales channels.')
                                    ->default(true),

                                Toggle::make('is_available')
                                    ->label('Available')
                                    ->helperText('This service is available for booking.')
                                    ->default(true),

                                DatePicker::make('published_at')
                                    ->label('Publishing date')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Section::make('Details')
                            ->schema([
                                TextInput::make('time_duration')
                                    ->label('Duration (minutes)')
                                    ->numeric()
                                    ->default(60)
                                    ->required(),

                                TextInput::make('cost')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                            ]),

                        Section::make('Associations')
                            ->schema([
                                Select::make('booking_brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable(),

                                Select::make('booking_categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
