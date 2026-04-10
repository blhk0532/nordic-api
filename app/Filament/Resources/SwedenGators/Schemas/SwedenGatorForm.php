<?php

namespace App\Filament\Resources\SwedenGators\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SwedenGatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('gata'),
                TextInput::make('postnummer'),
                TextInput::make('postort'),
                TextInput::make('kommun'),
                TextInput::make('lan'),
                TextInput::make('personer')
                    ->numeric(),
                TextInput::make('företag')
                    ->numeric(),
                TextInput::make('adresser')
                    ->numeric(),
                TextInput::make('ratsit_link'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_queue')
                    ->required(),
                Toggle::make('is_done')
                    ->required(),
            ]);
    }
}
