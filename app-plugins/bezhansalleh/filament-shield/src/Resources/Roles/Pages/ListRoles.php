<?php

declare(strict_types=1);

namespace BezhanSalleh\FilamentShield\Resources\Roles\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            //   CreateAction::make(),
        ];
    }

    public function getHeading(): Htmlable|string|null
    {
        return null;
    }
}
