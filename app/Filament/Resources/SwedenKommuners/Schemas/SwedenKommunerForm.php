<?php

namespace App\Filament\Resources\SwedenKommuners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenKommunerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Kommun Information')
                            ->schema([

                                TextInput::make('kommun')
                                    ->required()
                                    ->label('Kommun Namn'),
                                TextInput::make('lan')
                                    ->required()
                                    ->label('Län'),
                                TextInput::make('personer')
                                    ->numeric()
                                    ->label('Antal Personer'),
                                TextInput::make('foretag')
                                    ->numeric(),
                                TextInput::make('latitude'),
                                TextInput::make('longitude')
                                    ->required()
                                    ->label('Longitude'),
                            ])
                            ->columns(2),
                    ]),

            ]);
    }
}
