<?php

namespace App\Filament\Resources\Merinfos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MerinfoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type'),
                TextEntry::make('short_id'),
                TextEntry::make('name'),
                TextEntry::make('givenNameOrFirstName'),
                TextEntry::make('personalNumber'),
                KeyValueEntry::make('pnr'),
                KeyValueEntry::make('address'),
                TextEntry::make('gender'),
                IconEntry::make('is_celebrity')->boolean(),
                IconEntry::make('has_company_engagement')->boolean(),
                TextEntry::make('number_plus_count'),
                KeyValueEntry::make('phone_number'),
                TextEntry::make('url'),
                TextEntry::make('same_address_url'),
            ]);
    }
}
