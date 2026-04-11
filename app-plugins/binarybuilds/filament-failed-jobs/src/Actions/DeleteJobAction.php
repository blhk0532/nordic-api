<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Actions;

use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DeleteJobAction extends Action
{
    use ManagesJobs;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Delete'))
            ->color('danger')
            ->icon(Heroicon::Trash)
            ->modalHeading(__('Delete failed job?'))
            ->modalDescription(__('Are you sure you want to delete this job?'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('Job deleted!'))
            ->action(function (FailedJob $job) {
                $this->deleteJobs(collect([$job]));
            });
    }

    public static function getDefaultName(): ?string
    {
        return __('delete');
    }
}
