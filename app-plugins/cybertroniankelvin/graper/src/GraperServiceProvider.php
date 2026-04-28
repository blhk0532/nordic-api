<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper;

use CybertronianKelvin\Graper\Commands\GraperCommand;
use CybertronianKelvin\Graper\Commands\GraperInstallCommand;
use CybertronianKelvin\Graper\Http\Controllers\GraperPageController;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GraperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('graper')
            ->hasConfigFile('graper')
            ->hasConfigFile('grapesjs')
            ->hasViews()
            ->hasMigration('create_graper_pages_table')
            ->hasCommand(GraperCommand::class)
            ->hasCommand(GraperInstallCommand::class);
    }

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'graper');

        $this->loadRoutes();
        $this->registerDisplayRoute();

        FilamentAsset::register([
            Css::make('graper-editor', 'https://unpkg.com/grapesjs/dist/css/grapes.min.css'),
            Js::make('graper-editor', asset('build/grapesjs/index.js')),
        ], 'graper');
    }

    protected function registerDisplayRoute(): void
    {
        $prefix = trim(config('graper.page_route_prefix', '/'), '/');
        $path = $prefix === '' ? '/{slug}' : '/'.$prefix.'/{slug}';

        Route::get($path, [GraperPageController::class, 'display'])
            ->middleware('web')
            ->name('graper.page.display');
    }

    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/graper.php');
    }
}
