<?php

namespace App\Filament\Resources\SwedenPostnummers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SwedenPostnummerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('csv_id')
                    ->numeric(),
                TextInput::make('postnummer')
                    ->required(),
                TextInput::make('postort'),
                TextInput::make('lan'),
                TextInput::make('kommun'),
                TextInput::make('country'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('personer')
                    ->numeric(),
                TextInput::make('foretag')
                    ->numeric(),
                DateTimePicker::make('personer_saved'),
                DateTimePicker::make('foretag_saved'),
            ]);
    }
}
