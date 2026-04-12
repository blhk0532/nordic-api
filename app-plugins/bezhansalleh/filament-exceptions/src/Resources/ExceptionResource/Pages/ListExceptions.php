<?php

declare(strict_types=1);

namespace BezhanSalleh\FilamentExceptions\Resources\ExceptionResource\Pages;

use BezhanSalleh\FilamentExceptions\Resources\ExceptionResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;


class ListExceptions extends ListRecords
{
    protected static string $resource = ExceptionResource::class;

    protected static bool $isScopedToTenant = false;

    protected function getTableEmptyStateIcon(): ?string
    {
        return static::$resource::getNavigationIcon();
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('filament-exceptions::filament-exceptions.empty_list');
    }

        public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
