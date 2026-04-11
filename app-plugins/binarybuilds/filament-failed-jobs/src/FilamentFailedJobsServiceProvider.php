<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentFailedJobs;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentFailedJobsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'failed-jobs';

    public static string $viewNamespace = 'failed-jobs';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name);
    }
}
