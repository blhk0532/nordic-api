<?php

namespace App\Filament\Resources\SwedenAdressers\Pages;

use App\Filament\Resources\SwedenAdressers\SwedenAdresserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSwedenAdresser extends ViewRecord
{
    protected static string $resource = SwedenAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
