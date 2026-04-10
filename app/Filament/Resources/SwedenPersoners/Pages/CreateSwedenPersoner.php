<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Pages;

use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSwedenPersoner extends CreateRecord
{
    protected static string $resource = SwedenPersonerResource::class;
}
