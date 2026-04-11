<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Products\Resources\Categories\CategoryResource;
use Adultdate\FilamentBooking\Filament\Imports\Booking\CategoryImporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->importer(CategoryImporter::class),
            CreateAction::make(),
        ];
    }
}
