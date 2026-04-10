<?php

namespace App\Filament\Resources\Merinfos\Pages;

use App\Filament\Resources\Merinfos\MerinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMerinfo extends EditRecord
{
    protected static string $resource = MerinfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
