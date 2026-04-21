<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs\Pages;

use App\Filament\Resources\Jobs\JobResource;
use App\Filament\Resources\Jobs\Widgets\QueueMonitorWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): Htmlable|string|null
    {
        return null;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            QueueMonitorWidget::class,
        ];
    }
}
