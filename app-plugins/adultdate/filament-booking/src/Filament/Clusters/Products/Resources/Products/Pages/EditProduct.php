<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Products\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
