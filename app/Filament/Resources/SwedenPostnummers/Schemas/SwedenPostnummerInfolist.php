<?php

namespace App\Filament\Resources\SwedenPostnummers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SwedenPostnummerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('csv_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('postnummer'),
                TextEntry::make('postort')
                    ->placeholder('-'),
                TextEntry::make('lan')
                    ->placeholder('-'),
                TextEntry::make('kommun')
                    ->placeholder('-'),
                TextEntry::make('country')
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('personer')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('foretag')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('personer_saved')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('foretag_saved')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
