<?php

namespace App\Filament\Resources\SwedenGators\Pages;

use App\Filament\Resources\SwedenGators\SwedenGatorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSwedenGator extends ViewRecord
{
    protected static string $resource = SwedenGatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
