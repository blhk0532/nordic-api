<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenPersonerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Personuppgifter')
                            ->schema([
                                TextEntry::make('fornamn')->label('Förnamn')->placeholder('-'),
                                TextEntry::make('efternamn')->label('Efternamn')->placeholder('-'),
                                TextEntry::make('personnamn')->label('Personnamn')->placeholder('-'),
                                TextEntry::make('alder')->label('Ålder')->numeric()->placeholder('-'),
                                TextEntry::make('kon')->label('Kön')->placeholder('-'),
                                TextEntry::make('personnummer')->label('Personnummer')->placeholder('-'),
                                TextEntry::make('civilstand')->label('Civilstånd')->placeholder('-'),
                                TextEntry::make('telefon')->label('Telefon')->placeholder('-'),
                            ])
                            ->columns(4),

                        Section::make('Adress')
                            ->schema([
                                TextEntry::make('adress')->label('Adress')->placeholder('-'),
                                TextEntry::make('postnummer')->label('Postnummer')->placeholder('-'),
                                TextEntry::make('postort')->label('Postort')->placeholder('-'),
                                TextEntry::make('kommun')->label('Kommun')->placeholder('-'),
                                TextEntry::make('adressandring')->label('Adressändring')->placeholder('-'),
                            ])
                            ->columns(4),

                        Section::make('Bostad')
                            ->schema([
                                TextEntry::make('bostadstyp')->label('Bostadstyp')->placeholder('-'),
                                TextEntry::make('agandeform')->label('Ägandeform')->placeholder('-'),
                                TextEntry::make('boarea')->label('Boarea')->placeholder('-'),
                                TextEntry::make('byggar')->label('Byggår')->placeholder('-'),
                                TextEntry::make('personer')->label('Antal personer i hushåll')->numeric()->placeholder('-'),
                            ])
                            ->columns(4),

                        Section::make('Status')
                            ->schema([
                                IconEntry::make('is_hus')->label('Hus')->boolean(),
                                IconEntry::make('is_owner')->label('Ägare')->boolean(),
                                IconEntry::make('is_active')->label('Aktiv')->boolean(),
                                IconEntry::make('is_queue')->label('I kö')->boolean(),
                                IconEntry::make('is_done')->label('Klar')->boolean(),
                            ])
                            ->columns(5),

                        Section::make('Data')
                            ->schema([
                                TextEntry::make('ratsit_data')->label('Ratsit Data'),
                                TextEntry::make('hitta_data')->label('Hitta Data'),
                                TextEntry::make('merinfo_data')->label('MerInfo Data'),
                            ])
                            ->columns(5),

                        Section::make('Tidsstämplar')
                            ->schema([
                                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
