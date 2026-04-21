<?php

namespace App\Filament\Resources\Spreadsheets\Pages;

use App\Filament\Resources\Spreadsheets\SpreadsheetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpreadsheet extends EditRecord
{
    protected static string $resource = SpreadsheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
