<?php

namespace App\Filament\Resources\Merinfos\Schemas;

use App\Forms\Components\JsonKeyValue;
use Filament\Forms;
use Filament\Schemas\Schema;

class MerinfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('short_uuid')
                    ->required()
                    ->disabled()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->disabled()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('givenNameOrFirstName')
                    ->required(),
                Forms\Components\TextInput::make('personalNumber'),
                JsonKeyValue::make('pnr')
                    ->required()
                    ->label('PNR'),
                JsonKeyValue::make('address')
                    ->required(),
                Forms\Components\TextInput::make('gender'),
                Forms\Components\Toggle::make('is_celebrity')
                    ->required(),
                Forms\Components\Toggle::make('has_company_engagement')
                    ->required(),
                Forms\Components\Toggle::make('is_house')
                    ->required(),
                Forms\Components\TextInput::make('number_plus_count')->numeric()
                    ->required(),
                JsonKeyValue::make('phone_number')
                    ->required()
                    ->label('Phone Number'),
                Forms\Components\TextInput::make('url'),
                Forms\Components\TextInput::make('same_address_url'),
            ]);
    }
}
