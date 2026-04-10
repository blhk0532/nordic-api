<?php

namespace App\Filament\Resources\SwedenPostnummers\Pages;

use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSwedenPostnummer extends ViewRecord
{
    protected static string $resource = SwedenPostnummerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
