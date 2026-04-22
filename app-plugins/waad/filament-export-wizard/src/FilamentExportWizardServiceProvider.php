<?php

namespace Waad\FilamentExportWizard;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Waad\FilamentExportWizard\Livewire\ExportWizard;

class FilamentExportWizardServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-export-wizard';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('filament-export-wizard', ExportWizard::class);
    }
}
