<?php

namespace App\Filament\Resources\Spreadsheets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Qalainau\UniverSheet\SpreadsheetEntry;

class SpreadsheetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('name')
                    ->label('Name'),
                SpreadsheetEntry::make('data')
                    ->label('Spreadsheet Data'),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }
}
