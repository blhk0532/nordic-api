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
                    ->label('Name')
                    ->hidden(),
                SpreadsheetEntry::make('data')
                    ->label(fn ($record) => $record['name'] ?? 'Spreadsheet Database')
                    ->extraAttributes(['class' => 'w-full', 'style' => 'min-height: 800px;'])
                    ->columnSpanFull(),

            ]);
    }
}
