<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerinfoDatas\Pages;

use App\Filament\Resources\MerinfoDatas\MerinfoDataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMerinfoData extends CreateRecord
{
    protected static string $resource = MerinfoDataResource::class;
}
