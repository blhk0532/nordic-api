<?php

declare(strict_types=1);

namespace App\Filament\Resources\HittaDatas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HittaDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Personuppgifter')
                ->description('Grundläggande information om personen')
                ->schema([
                    Grid::make(12)->schema([
                        TextInput::make('personnamn')
                            ->label('Personnamn')
                            ->maxLength(65535)
                            ->columnSpan(6),

                        TextInput::make('alder')
                            ->label('Ålder')
                            ->maxLength(255)
                            ->columnSpan(3),

                        Select::make('kon')
                            ->label('Kön')
                            ->options([
                                'Man' => 'Man',
                                'Kvinna' => 'Kvinna',
                            ])
                            ->columnSpan(3),
                    ]),
                ])
                ->columns(1),

            Section::make('Adressuppgifter')
                ->description('Adress och bostadsinformation')
                ->schema([
                    Grid::make(12)->schema([
                        TextInput::make('gatuadress')
                            ->label('Gatuadress')
                            ->maxLength(65535)
                            ->columnSpan(6),

                        TextInput::make('postnummer')
                            ->label('Postnummer')
                            ->maxLength(255)
                            ->columnSpan(3),

                        TextInput::make('postort')
                            ->label('Postort')
                            ->maxLength(255)
                            ->columnSpan(3),

                        TextInput::make('bostadstyp')
                            ->label('Bostadstyp')
                            ->maxLength(255)
                            ->columnSpan(6),

                        TextInput::make('bostadspris')
                            ->label('Bostadspris')
                            ->numeric()
                            ->prefix('kr')
                            ->maxLength(255)
                            ->columnSpan(6),
                    ]),
                ])
                ->columns(1),

            Section::make('Kontaktuppgifter')
                ->description('Telefon och länkar')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('telefon')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('karta')
                            ->label('Karta (URL)')
                            ->url()
                            ->maxLength(65535),

                        TextInput::make('link')
                            ->label('Länk')
                            ->url()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                ])
                ->columns(1),

            Section::make('Statusflaggor')
                ->description('Systemflaggor och statusmarkeringar')
                ->schema([
                    Grid::make(4)->schema([
                        Toggle::make('is_active')
                            ->label('Aktiv')
                            ->default(true),

                        Toggle::make('is_telefon')
                            ->label('Har Telefon')
                            ->default(false),

                        Toggle::make('is_ratsit')
                            ->label('I Ratsit')
                            ->default(false),

                        Toggle::make('is_hus')
                            ->label('Är Hus')
                            ->default(false),
                    ]),
                ])
                ->columns(1)
                ->collapsible(),
        ]);
    }
}
