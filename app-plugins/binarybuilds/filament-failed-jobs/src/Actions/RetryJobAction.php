<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Actions;

use BinaryBuilds\FilamentFailedJobs\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class RetryJobAction extends Action
{
    use ManagesJobs;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('Retry'))
            ->icon(Heroicon::ArrowPath)
            ->modalHeading(__('Retry failed job?'))
            ->modalDescription(__('Are you sure you want to retry this job?'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('Job pushed to queue successfully!'))
            ->action(function (FailedJob $job) {
                $this->retryJobs(collect([$job]));
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'retry';
    }
}
