<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getBreadcrumbs(): array
    {
        return [

        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
