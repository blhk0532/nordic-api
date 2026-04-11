<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs\Actions;

use BinaryBuilds\FilamentFailedJobs\FilamentFailedJobsPlugin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

trait ManagesJobs
{
    public function retryJobs(Collection $jobs): void
    {
        Artisan::call('queue:retry '.$jobs->pluck('uuid')->implode(' '));
    }

    public function deleteJobs(Collection $jobs): void
    {
        foreach ($jobs as $job) {
            if (FilamentFailedJobsPlugin::get()->isUsingHorizon()) {
                Artisan::call('horizon:forget '.$job->uuid);
            } else {
                Artisan::call('queue:forget '.$job->uuid);
            }
        }
    }
}
