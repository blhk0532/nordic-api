<?php

namespace App\Filament\Resources\SwedenAdressers\Pages;

use App\Filament\Resources\SwedenAdressers\SwedenAdresserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSwedenAdresser extends EditRecord
{
    protected static string $resource = SwedenAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
