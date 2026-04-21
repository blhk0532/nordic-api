<?php

namespace App\Filament\Resources\Spreadsheets\Pages;

use App\Filament\Resources\Spreadsheets\SpreadsheetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpreadsheets extends ListRecords
{
    protected static string $resource = SpreadsheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
