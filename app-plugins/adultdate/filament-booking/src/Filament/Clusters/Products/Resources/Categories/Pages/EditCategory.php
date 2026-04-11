<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
