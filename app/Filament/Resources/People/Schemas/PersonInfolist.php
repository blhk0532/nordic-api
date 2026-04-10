<?php

namespace App\Filament\Resources\People\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PersonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('gender'),
                TextEntry::make('person'),
                TextEntry::make('street'),
                TextEntry::make('zip'),
                TextEntry::make('city'),
                TextEntry::make('kommun')
                    ->placeholder('-'),
                TextEntry::make('merinfo_id'),
                TextEntry::make('merinfo_phone'),
            ]);
    }
}
