<?php

namespace App\Filament\Resources\SwedenPostorters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenPostorterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plats')
                    ->schema([
                        TextInput::make('postort')
                            ->label('Postort')
                            ->required(),
                        TextInput::make('kommun')
                            ->label('Kommun'),
                        TextInput::make('lan')
                            ->label('Län'),
                        TextInput::make('latitude')
                            ->label('Latitud'),
                        TextInput::make('longitude')
                            ->label('Longitud'),
                    ])
                    ->columns(5),

                Section::make('Statistik')
                    ->schema([
                        TextInput::make('personer')
                            ->label('Personer')
                            ->numeric(),
                        TextInput::make('foretag')
                            ->label('Företag')
                            ->numeric(),
                        TextInput::make('postnummer')
                            ->label('Postnummer')
                            ->numeric(),
                        TextInput::make('gator')
                            ->label('Gator')
                            ->numeric(),
                        TextInput::make('adresser')
                            ->label('Adresser')
                            ->numeric(),
                    ])
                    ->columns(5),

                Section::make('Övrigt')
                    ->schema([
                        TextInput::make('ratsit_link')
                            ->label('Ratsit-länk')
                            ->url()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
