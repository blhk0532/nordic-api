<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenPersonerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Personuppgifter')
                            ->schema([
                                TextInput::make('fornamn')
                                    ->label('Förnamn'),
                                TextInput::make('efternamn')
                                    ->label('Efternamn'),
                                TextInput::make('personnamn')
                                    ->label('Personnamn'),
                                TextInput::make('alder')
                                    ->numeric()
                                    ->label('Ålder'),
                                TextInput::make('kon')
                                    ->label('Kön'),
                                TextInput::make('personnummer')
                                    ->label('Personnummer'),
                                TextInput::make('civilstand')
                                    ->label('Civilstånd'),
                                TextInput::make('telefon')
                                    ->tel()
                                    ->label('Telefon'),
                            ])
                            ->columns(2),

                        Section::make('Adress')
                            ->schema([
                                TextInput::make('adress')
                                    ->label('Adress'),
                                TextInput::make('postnummer')
                                    ->label('Postnummer'),
                                TextInput::make('postort')
                                    ->label('Postort'),
                                TextInput::make('kommun')
                                    ->label('Kommun'),
                                TextInput::make('lan')
                                    ->label('Län'),
                                TextInput::make('adressandring')
                                    ->label('Adressändring'),
                            ])
                            ->columns(2),

                        Section::make('Bostad')
                            ->schema([
                                TextInput::make('bostadstyp')
                                    ->label('Bostadstyp'),
                                TextInput::make('agandeform')
                                    ->label('Ägandeform'),
                                TextInput::make('boarea')
                                    ->label('Boarea'),
                                TextInput::make('byggar')
                                    ->label('Byggår'),
                                TextInput::make('personer')
                                    ->numeric()
                                    ->label('Antal personer i hushåll'),
                            ])
                            ->columns(2),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_hus')->label('Hus'),
                                Toggle::make('is_owner')->label('Ägare'),
                                Toggle::make('is_active')->label('Aktiv'),
                                Toggle::make('is_queue')->label('I kö'),
                                Toggle::make('is_done')->label('Klar'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
