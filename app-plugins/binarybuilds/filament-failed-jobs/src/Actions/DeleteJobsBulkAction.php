<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Actions;

use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class DeleteJobsBulkAction extends BulkAction
{
    use ManagesJobs;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Delete Jobs'))
            ->color('danger')
            ->requiresConfirmation()
            ->accessSelectedRecords()
            ->icon(Heroicon::Trash)
            ->modalHeading(__('Delete failed jobs?'))
            ->modalDescription(__('Are you sure you want to delete these jobs?'))
            ->successNotificationTitle(__('Jobs deleted!'))
            ->action(function (Collection $jobs) {
                $this->deleteJobs($jobs);
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'delete';
    }
}
