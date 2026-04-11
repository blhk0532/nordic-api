<?php

declare(strict_types=1);

namespace BinaryBuilds\FilamentCacheManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentCacheManagerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-cache-manager';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name)
            ->hasViews();
    }
}
