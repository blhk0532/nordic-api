<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Pages;

use CybertronianKelvin\Graper\Models\GraperPage;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Route;

class GrapesJsPage extends Page
{
    protected static string $view = 'graper::pages.grapes-js-page';

    public ?GraperPage $page = null;

    public static function routes(Panel $panel): void
    {
        Route::get('/graper/edit/{page}', static::class)
            ->name('graper.edit')
            ->middleware(config('filament.auth.middleware'));
    }

    public function mount(GraperPage $page)
    {
        $this->page = $page;
    }

    protected function getViewData(): array
    {
        return [
            'page' => $this->page,
            'pageId' => $this->page->id,
            'height' => config('grapesjs.default_height', '500px'),
            'cssExternal' => config('grapesjs.css.external', []),
            'cssInternal' => config('grapesjs.css.internal'),
        ];
    }
}
