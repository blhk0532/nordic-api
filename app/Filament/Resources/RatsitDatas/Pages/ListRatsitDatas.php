<?php

declare(strict_types=1);

namespace App\Filament\Resources\RatsitDatas\Pages;

use App\Filament\Resources\RatsitDatas\RatsitDataResource;
use App\Filament\Widgets\RatsitDataStatsWidget;
use App\Jobs\BackupRatsitData;
use App\Jobs\ImportRatsitData;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Contracts\Support\Htmlable;

class ListRatsitDatas extends ListRecords
{
    protected static string $resource = RatsitDataResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            RatsitDataStatsWidget::class,
        ];
    }

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
