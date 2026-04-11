<?php

declare(strict_types=1);

namespace App\Filament\Panels\App\Resources\Searches\Pages;

use App\Filament\Panels\App\Resources\Searches\SearchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSearch extends CreateRecord
{
    protected static string $resource = SearchResource::class;
}
