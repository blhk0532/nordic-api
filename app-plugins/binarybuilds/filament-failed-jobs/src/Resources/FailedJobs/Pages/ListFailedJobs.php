<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Pages;

use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\FailedJobResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): Htmlable|string|null
    {
        return null;
    }
}
