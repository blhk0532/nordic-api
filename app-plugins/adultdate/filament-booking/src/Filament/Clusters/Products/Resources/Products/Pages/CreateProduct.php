<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Products\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
