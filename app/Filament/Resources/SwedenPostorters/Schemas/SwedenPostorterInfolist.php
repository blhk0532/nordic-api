<?php

namespace App\Filament\Resources\SwedenPostorters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenPostorterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plats')
                    ->schema([
                        TextEntry::make('postort')
                            ->label('Postort'),
                        TextEntry::make('kommun')
                            ->label('Kommun')
                            ->placeholder('-'),
                        TextEntry::make('lan')
                            ->label('Län')
                            ->placeholder('-'),
                        TextEntry::make('latitude')
                            ->label('Latitud')
                            ->placeholder('-'),
                        TextEntry::make('longitude')
                            ->label('Longitud')
                            ->placeholder('-'),
                    ])
                    ->columns(5),

                Section::make('Statistik')
                    ->schema([
                        TextEntry::make('personer')
                            ->label('Personer')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('foretag')
                            ->label('Företag')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('postnummer')
                            ->label('Postnummer')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('gator')
                            ->label('Gator')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('adresser')
                            ->label('Adresser')
                            ->numeric()
                            ->placeholder('-'),
                    ])
                    ->columns(5),

                Section::make('Övrigt')
                    ->schema([
                        TextEntry::make('ratsit_link')
                            ->label('Ratsit-länk')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Skapad')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Uppdaterad')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
