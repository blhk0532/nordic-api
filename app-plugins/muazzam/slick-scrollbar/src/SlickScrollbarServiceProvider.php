<?php

namespace Muazzam\SlickScrollbar;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

class SlickScrollbarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER, // <-- not STYLES
            function (): string {
                $css = @file_get_contents(__DIR__.'/../resources/dist/slick-scrollbar.css') ?: '';

                return $css ? "<style id=\"slick-scrollbar-css\">{$css}</style>" : '';
            }
        );
    }
}
