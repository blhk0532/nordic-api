<?php

namespace App\Filament\Resources\People\Schemas;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('gender'),
                TextInput::make('person'),
                TextInput::make('street')
                    ->required(),
                TextInput::make('zip')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('kommun'),
                Forms\Components\Select::make('merinfo_id')
                    ->relationship('merinfo', 'id'),
                TextInput::make('merinfo_phone'),
            ]);
    }
}
