<?php

namespace App\Filament\Resources\SwedenGators\Pages;

use App\Filament\Resources\SwedenGators\SwedenGatorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSwedenGator extends EditRecord
{
    protected static string $resource = SwedenGatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
