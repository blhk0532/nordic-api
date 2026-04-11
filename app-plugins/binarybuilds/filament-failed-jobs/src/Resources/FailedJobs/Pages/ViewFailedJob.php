<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\Pages;

use BinaryBuilds\FilamentFailedJobs\Actions\DeleteJobAction;
use BinaryBuilds\FilamentFailedJobs\Actions\RetryJobAction;
use BinaryBuilds\FilamentFailedJobs\Resources\FailedJobs\FailedJobResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFailedJob extends ViewRecord
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RetryJobAction::make()->successRedirectUrl($this->getResourceUrl('index')),
            DeleteJobAction::make()->successRedirectUrl($this->getResourceUrl('index')),
        ];
    }
}
