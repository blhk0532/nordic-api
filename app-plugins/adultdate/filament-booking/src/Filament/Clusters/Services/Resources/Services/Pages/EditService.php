<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
