<?php

namespace App\Filament\Resources\Spreadsheets\Pages;

use App\Filament\Resources\Spreadsheets\SpreadsheetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSpreadsheet extends ViewRecord
{
    protected static string $resource = SpreadsheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
