<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking;

use Adultdate\FilamentBooking\Commands\NumpadFieldCommand;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NumpadFieldServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-numpad-field';

    public static string $viewNamespace = 'filament-numpad-field';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name);

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(self::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        //        FilamentAsset::register(
        //            $this->getAssets(),
        //            $this->getAssetPackageName()
        //        );

        //        FilamentAsset::registerScriptData(
        //            $this->getScriptData(),
        //            $this->getAssetPackageName()
        //        );
    }

    protected function getAssetPackageName(): ?string
    {
        return 'dashed/filament-numpad-field';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-numpad-field', __DIR__ . '/../resources/dist/components/filament-numpad-field.js'),
            Css::make('filament-numpad-field-styles', __DIR__.'/../resources/dist/filament-numpad-field.css')->loadedOnRequest(),
            Js::make('filament-numpad-field-scripts', __DIR__.'/../resources/dist/filament-numpad-field.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            NumpadFieldCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-numpad-field_table',
        ];
    }
}
