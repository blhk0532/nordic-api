<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs\Pages;

use App\Filament\Resources\Jobs\JobResource;
use App\Filament\Resources\Jobs\Widgets\QueueMonitorWidget;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
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
