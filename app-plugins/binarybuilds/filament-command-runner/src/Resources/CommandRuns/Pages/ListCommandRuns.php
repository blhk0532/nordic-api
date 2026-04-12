<?php

declare(strict_types=1);

namespace BinaryBuilds\CommandRunner\Resources\CommandRuns\Pages;

use BinaryBuilds\CommandRunner\Resources\CommandRuns\CommandRunResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListCommandRuns extends ListRecords
{
    protected static string $resource = CommandRunResource::class;

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

        public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
