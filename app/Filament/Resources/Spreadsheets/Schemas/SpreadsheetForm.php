<?php

namespace App\Filament\Resources\Spreadsheets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Qalainau\UniverSheet\SpreadsheetField;

class SpreadsheetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columns(1)
                            ->maxLength(255),
                        TextInput::make('google_sheet_id')
                            ->label('Google Sheet ID')
                            ->hint('Optional Google Sheets ID to sync with')
                            ->placeholder('e.g., 1AbCdEfGhIjKlMnOpQrStUvWxYz1234567890')
                            ->columns(1),
                        SpreadsheetField::make('data')
                            ->columns(2)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
