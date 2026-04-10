<?php

declare(strict_types=1);

namespace App\Filament\Resources\MerinfoDatas\Pages;

use App\Filament\Resources\MerinfoDatas\MerinfoDataResource;
use App\Filament\Widgets\MerinfoDataStatsWidget;
use App\Jobs\BackupMerinfoData;
use App\Jobs\ImportMerinfoData;
use App\Models\User;
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

class ListMerinfoDatas extends ListRecords
{
    protected static string $resource = MerinfoDataResource::class;

    // Hide the default header title for this resource (removes the <h1 class="fi-header-heading">)
    protected ?string $heading = '';

    protected function getHeaderWidgets(): array
    {
        return [
            MerinfoDataStatsWidget::class,
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
