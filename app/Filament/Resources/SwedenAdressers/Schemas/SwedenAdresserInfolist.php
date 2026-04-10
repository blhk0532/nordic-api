<?php

namespace App\Filament\Resources\SwedenAdressers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SwedenAdresserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('gata')
                    ->placeholder('-'),
                TextEntry::make('postnummer')
                    ->placeholder('-'),
                TextEntry::make('postort')
                    ->placeholder('-'),
                TextEntry::make('kommun')
                    ->placeholder('-'),
                TextEntry::make('lan')
                    ->placeholder('-'),
                TextEntry::make('personer')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('företag')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('adresser')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ratsit_link')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_queue')
                    ->boolean(),
                IconEntry::make('is_done')
                    ->boolean(),
            ]);
    }
}
