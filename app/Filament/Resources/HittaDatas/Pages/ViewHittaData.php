<?php

declare(strict_types=1);

namespace App\Filament\Resources\HittaDatas\Pages;

use App\Filament\Resources\HittaDatas\HittaDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHittaData extends ViewRecord
{
    protected static string $resource = HittaDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
