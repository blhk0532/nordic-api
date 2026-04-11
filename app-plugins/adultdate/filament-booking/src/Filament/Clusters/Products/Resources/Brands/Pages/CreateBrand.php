<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;
}
