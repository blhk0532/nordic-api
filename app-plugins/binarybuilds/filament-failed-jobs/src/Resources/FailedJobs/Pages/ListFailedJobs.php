<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Pages;

use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

        public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return null;
    }
}
